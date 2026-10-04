<?php
// backend/api/formations/index.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$pdo = $db->getConnection();

try {
    // Récupérer les paramètres
    $recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
    $est_actif = isset($_GET['est_actif']) ? (int)$_GET['est_actif'] : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    // Construire la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM formation WHERE 1=1";
    $params = [];
    
    // Filtre par statut
    if ($est_actif !== null) {
        $sql .= " AND est_actif = ?";
        $params[] = $est_actif;
    } else if (!isAdmin()) {  // ← CORRIGEZ ICI AUSSI
        // Les non-admins voient seulement les actives
        $sql .= " AND est_actif = 1";
    }
    
    // Filtre par recherche
    if (!empty($recherche)) {
        $sql .= " AND (titre LIKE ? OR description LIKE ?)";
        $searchParam = "%$recherche%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Ordre et pagination
    $sql .= " ORDER BY titre LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Exécuter la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer le nombre total
    $stmt = $pdo->query("SELECT FOUND_ROWS() as total");
    $total = $stmt->fetch()['total'];
    $pages = ceil($total / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $formations,
        'count' => count($formations),
        'total' => $total,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'pages' => $pages,
            'total' => $total
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des formations',
        'error' => $e->getMessage()
    ]);
}
?>