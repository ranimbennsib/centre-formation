<?php
// backend/api/sessions/index.php - VERSION SIMPLIFIÉE ET CORRIGÉE
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Activer le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new Database();
$pdo = $db->getConnection();

try {
    // DÉBUT - Récupération des headers manuellement
    $headers = getallheaders();
    $userId = isset($headers['X-User-Id']) ? intval($headers['X-User-Id']) : 0;
    $userRole = isset($headers['X-User-Role']) ? strtoupper($headers['X-User-Role']) : '';
    
    // Log pour débogage
    error_log("=== SESSIONS/INDEX.PHP ===");
    error_log("User ID: " . $userId);
    error_log("User Role: " . $userRole);
    error_log("Headers: " . print_r($headers, true));
    
    // Si pas d'utilisateur authentifié, retourner erreur
    if ($userId === 0) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non authentifié'
        ]);
        exit;
    }
    
    // Requête de base - SIMPLIFIÉE
    $sql = "SELECT s.*,
                   f.titre as formation_titre,
                   COALESCE(u_formateur.nom, 'Non assigné') as formateur_nom,
                   COALESCE(u_formateur.prenom, '') as formateur_prenom
            FROM session s
            JOIN formation f ON s.id_formation = f.id
            LEFT JOIN formateur form ON s.id_formateur = form.id
            LEFT JOIN utilisateur u_formateur ON form.id_utilisateur = u_formateur.id
            WHERE s.deleted_at IS NULL 
            AND s.statut_formateur != 'ANNULEE'";
    
    $params = [];
    $conditions = [];
    
    // SIMPLIFICATION : Pour l'instant, tout le monde voit toutes les sessions
    // Vous pourrez ajouter des filtres par rôle plus tard
     if ($userRole === 'FORMATEUR') {
         $conditions[] = "s.id_formateur = ?";
         // Récupérer l'ID formateur correspondant à l'utilisateur
         $stmt = $pdo->prepare("SELECT id FROM formateur WHERE id_utilisateur = ?");
         $stmt->execute([$userId]);
         $formateur = $stmt->fetch();
         if ($formateur) {
             $params[] = $formateur['id'];
         }
     }
    
    // 1. FILTRE PAR RECHERCHE TEXTE
    if (isset($_GET['recherche']) && !empty(trim($_GET['recherche']))) {
        $recherche = trim($_GET['recherche']);
        $conditions[] = "(f.titre LIKE ? OR s.salle LIKE ? OR u_formateur.nom LIKE ? OR u_formateur.prenom LIKE ?)";
        $searchTerm = "%" . $recherche . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // 2. FILTRE PAR STATUT
    if (isset($_GET['statut']) && !empty($_GET['statut'])) {
        $conditions[] = "s.statut_formateur = ?";
        $params[] = $_GET['statut'];
    }
    
    // 3. FILTRE PAR FORMATION
    if (isset($_GET['formation_id']) && is_numeric($_GET['formation_id'])) {
        $conditions[] = "s.id_formation = ?";
        $params[] = (int)$_GET['formation_id'];
    }
    
    // 4. FILTRE PAR FORMATEUR
    if (isset($_GET['formateur_id']) && is_numeric($_GET['formateur_id'])) {
        $conditions[] = "s.id_formateur = ?";
        $params[] = (int)$_GET['formateur_id'];
    }
    
    // 5. FILTRE PAR PLAGE DE DATES
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $conditions[] = "DATE(s.date_debut) >= ?";
        $params[] = $_GET['date_from'];
    }
    
    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $conditions[] = "DATE(s.date_fin) <= ?";
        $params[] = $_GET['date_to'];
    }
    
    // Ajouter toutes les conditions
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    // Trier par date de début
    $sql .= " ORDER BY s.date_debut DESC";
    
    // PAGINATION - SIMPLIFIÉE
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    // Ajouter la pagination à la requête
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Exécuter la requête principale
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pour le comptage total, faire une requête séparée
    $sqlCount = "SELECT COUNT(*) as total FROM session s
                 JOIN formation f ON s.id_formation = f.id
                 LEFT JOIN formateur form ON s.id_formateur = form.id
                 LEFT JOIN utilisateur u_formateur ON form.id_utilisateur = u_formateur.id
                 WHERE s.deleted_at IS NULL";
    
    // Ajouter les mêmes conditions au comptage
    if (!empty($conditions)) {
        $sqlCount .= " AND " . implode(" AND ", $conditions);
    }
    
    // Retirer les paramètres de pagination pour le comptage
    $countParams = [];
    foreach ($params as $key => $value) {
        if ($key < count($params) - 2) { // Exclure les 2 derniers paramètres (LIMIT et OFFSET)
            $countParams[] = $value;
        }
    }
    
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($countParams);
    $totalResult = $stmtCount->fetch(PDO::FETCH_ASSOC);
    $totalRows = $totalResult['total'];
    
    // Log pour débogage
    error_log("Sessions trouvées: " . count($sessions));
    error_log("Total rows: " . $totalRows);
    
    // Retourner la réponse
    echo json_encode([
        'success' => true,
        'data' => $sessions,
        'count' => count($sessions),
        'pagination' => [
            'total' => (int)$totalRows,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($totalRows / $limit)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("❌ Erreur PDO: " . $e->getMessage());
    error_log("❌ Fichier: " . $e->getFile());
    error_log("❌ Ligne: " . $e->getLine());
    error_log("❌ Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des sessions',
        'error' => $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} catch (Exception $e) {
    error_log("❌ Erreur générale: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur système',
        'error' => $e->getMessage()
    ]);
}
?>