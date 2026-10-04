<?php
// backend/api/formateurs/index.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vider le cache PHP si possible
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// -------------------- CORS --------------------
header("Access-Control-Allow-Origin: http://localhost:8081"); // frontend
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role");
header("Access-Control-Allow-Credentials: false");

// Répondre aux préflight requests OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -------------------- Auth et DB --------------------
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $userRole = getCurrentUserRole();
    
    // DEBUG: Log pour voir les appels
    error_log("=== API Formateurs appelée ===");
    error_log("URL: " . $_SERVER['REQUEST_URI']);
    error_log("Méthode: " . $_SERVER['REQUEST_METHOD']);
    
    // -------------------- Requête principale --------------------
    $sql = "
SELECT 
    f.id as formateur_id,
    f.cin,
    u.id as utilisateur_id,
    u.nom,
    u.prenom,
    u.email,
    u.date_inscription,
    f.specialite,
    COUNT(DISTINCT s.id) as sessions_total,
    COUNT(DISTINCT CASE WHEN s.statut_formateur = 'ACCEPTEE' THEN s.id END) as sessions_acceptees,
    COUNT(DISTINCT CASE WHEN s.statut_formateur = 'EN_ATTENTE' THEN s.id END) as sessions_attente,
    COUNT(DISTINCT CASE WHEN s.statut_formateur = 'REFUSEE' THEN s.id END) as sessions_refusees,
    COUNT(DISTINCT i.id) as inscriptions_totales,
    COUNT(DISTINCT CASE WHEN i.statut = 'VALIDEE' THEN i.id END) as inscriptions_validees,
    AVG(s.places_max) as places_moyennes_par_session,
    COALESCE(SUM(s.places_max), 0) as capacite_totale
FROM formateur f
INNER JOIN utilisateur u ON f.id_utilisateur = u.id 
    AND u.role = 'FORMATEUR' 
    AND u.deleted_at IS NULL
    AND f.deleted_at IS NULL
LEFT JOIN session s ON f.id = s.id_formateur AND s.deleted_at IS NULL
LEFT JOIN inscription i ON s.id = i.id_session
WHERE 1=1";

    $params = [];

    // Filtres
    if (isset($_GET['specialite']) && !empty($_GET['specialite'])) {
        $sql .= " AND f.specialite LIKE ?";
        $params[] = "%" . $_GET['specialite'] . "%";
    }

    if (isset($_GET['recherche']) && !empty($_GET['recherche'])) {
        $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR f.specialite LIKE ? OR f.cin LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%" . $_GET['recherche'] . "%";
        for ($i = 0; $i < 5; $i++) {
            $params[] = $searchTerm;
        }
    }

    if (isset($_GET['actif']) && $_GET['actif'] === 'true') {
        $sql .= " AND EXISTS (
            SELECT 1 FROM session s2 
            WHERE s2.id_formateur = f.id 
            AND s2.statut_formateur = 'ACCEPTEE'
            AND s2.date_debut > NOW()
            AND s2.deleted_at IS NULL
        )";
    }

    $sql .= " GROUP BY f.id, u.id, f.cin, u.nom, u.prenom, u.email, u.date_inscription, f.specialite";
    $sql .= " ORDER BY u.nom, u.prenom";

    // Pagination
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    // Exécution
    $stmt = $pdo->prepare($sql);
    foreach ($params as $i => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($i + 1, $value, $type);
    }

    $stmt->execute();
    $formateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total
    $countSql = "SELECT COUNT(DISTINCT f.id) as total
                 FROM formateur f
                 INNER JOIN utilisateur u ON f.id_utilisateur = u.id 
                 AND u.role = 'FORMATEUR' 
                 AND u.deleted_at IS NULL
                 AND f.deleted_at IS NULL
                 WHERE 1=1";

    $countParams = [];
    if (isset($_GET['specialite']) && !empty($_GET['specialite'])) {
        $countSql .= " AND f.specialite LIKE ?";
        $countParams[] = "%" . $_GET['specialite'] . "%";
    }
    if (isset($_GET['recherche']) && !empty($_GET['recherche'])) {
        $countSql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR f.specialite LIKE ? OR f.cin LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%" . $_GET['recherche'] . "%";
        for ($i = 0; $i < 5; $i++) {
            $countParams[] = $searchTerm;
        }
    }

    $stmtCount = $pdo->prepare($countSql);
    foreach ($countParams as $i => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmtCount->bindValue($i + 1, $value, $type);
    }
    $stmtCount->execute();
    $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'data' => $formateurs,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>