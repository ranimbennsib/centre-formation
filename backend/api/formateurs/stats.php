<?php
// backend/api/formateurs/stats.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Formateur uniquement
$user = getCurrentUser();
if (!$user || strtoupper($user['role']) !== 'FORMATEUR') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux formateurs']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    $userId = $user['id'];
    
    // Statistiques des sessions
    $sql = "SELECT 
                COUNT(*) as total_sessions,
                COUNT(CASE WHEN statut_formateur = 'ACCEPTEE' THEN 1 END) as accepted_sessions,
                COUNT(CASE WHEN statut_formateur = 'EN_ATTENTE' THEN 1 END) as pending_sessions,
                COUNT(CASE WHEN statut_formateur = 'REFUSEE' THEN 1 END) as refused_sessions
            FROM session 
            WHERE id_formateur = ? 
            AND deleted_at IS NULL";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Sessions récentes (pour l'activité)
    $sqlRecent = "SELECT 
                    s.id,
                    s.date_debut,
                    s.date_fin,
                    s.statut_formateur,
                    s.created_at,
                    f.titre as formation_titre
                FROM session s
                INNER JOIN formation f ON s.id_formation = f.id
                WHERE s.id_formateur = ?
                AND s.deleted_at IS NULL
                ORDER BY s.created_at DESC
                LIMIT 5";
    
    $stmtRecent = $pdo->prepare($sqlRecent);
    $stmtRecent->execute([$userId]);
    $recentSessions = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer le taux d'acceptation
    $acceptanceRate = 0;
    if ($stats['total_sessions'] > 0) {
        $acceptanceRate = round(($stats['accepted_sessions'] / $stats['total_sessions']) * 100, 2);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => $stats,
            'acceptance_rate' => $acceptanceRate,
            'recent_sessions' => $recentSessions
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur stats: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données'
    ]);
}
?>