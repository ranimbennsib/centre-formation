<?php
// backend/api/formations/detail.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

$db = new Database();
$pdo = $db->getConnection();

// Récupérer l'ID depuis l'URL : detail.php?id=5
$formationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($formationId === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID formation requis']);
    exit;
}

try {
    // Préparer la requête
    $sql = "SELECT * FROM formation WHERE id = ?";
    
    // Si l'utilisateur n'est pas admin, on ne montre que les formations actives
    if ($_SESSION['role'] !== 'ADMIN') {
        $sql .= " AND est_actif = 1";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$formationId]);
    $formation = $stmt->fetch();
    
    if ($formation) {
        // Ajouter des statistiques si admin
        if ($_SESSION['role'] === 'ADMIN') {
            // Compter les sessions de cette formation
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_sessions,
                       SUM(places_max) as total_places,
                       SUM(places_restantes) as places_restantes
                FROM session 
                WHERE id_formation = ?
            ");
            $stmt->execute([$formationId]);
            $stats = $stmt->fetch();
            
            $formation['statistiques'] = $stats;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $formation
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Formation non trouvée'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur',
        'error' => $e->getMessage()
    ]);
}
?>