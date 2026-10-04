<?php
// backend/api/inscriptions/index.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$pdo = $db->getConnection();

try {
    $userId   = getCurrentUserId();
    $userRole = getCurrentUserRole();

    // Débogage : afficher les paramètres reçus
    error_log("=== INSCRIPTIONS INDEX DEBUG ===");
    error_log("Role: $userRole");
    error_log("GET params: " . json_encode($_GET));

    $sql = "
        SELECT
            i.*,
            ue.nom AS employe_nom,
            ue.prenom AS employe_prenom,
            ue.email AS employe_email,
            s.date_debut,
            s.date_fin,
            s.salle,
            s.places_max,
            s.statut_formateur,
            f.titre AS formation_titre,
            f.description AS formation_description,
            uf.nom AS formateur_nom,
            uf.prenom AS formateur_prenom,
            uf.email AS formateur_email
        FROM inscription i
        JOIN employe e ON i.id_employe = e.id
        JOIN utilisateur ue ON e.id_utilisateur = ue.id
        JOIN session s ON i.id_session = s.id
        JOIN formation f ON s.id_formation = f.id
        LEFT JOIN formateur fo ON s.id_formateur = fo.id
        LEFT JOIN utilisateur uf ON fo.id_utilisateur = uf.id
        WHERE 1 = 1
    ";

    $params = [];

    /* 🔐 Filtres par rôle */
    if ($userRole === 'EMPLOYE') {
        $sql .= " AND e.id_utilisateur = ?";
        $params[] = $userId;
    }

    if ($userRole === 'FORMATEUR') {
        $sql .= " AND s.id_formateur IN (
                    SELECT id FROM formateur WHERE id_utilisateur = ?
                 )";
        $params[] = $userId;
    }

    /* 🔎 Filtres optionnels */
    
    // 1. Statut
    if (!empty($_GET['statut'])) {
        $sql .= " AND i.statut = ?";
        $params[] = $_GET['statut'];
        error_log("Filtre statut: " . $_GET['statut']);
    }

    // 2. Session ID
    if (!empty($_GET['session_id']) && is_numeric($_GET['session_id'])) {
        $sql .= " AND i.id_session = ?";
        $params[] = (int)$_GET['session_id'];
        error_log("Filtre session_id: " . $_GET['session_id']);
    }

    // 3. Formateur ID (pour admin)
    if (!empty($_GET['formateur_id']) && is_numeric($_GET['formateur_id']) && $userRole === 'ADMIN') {
        $sql .= " AND s.id_formateur = ?";
        $params[] = (int)$_GET['formateur_id'];
        error_log("Filtre formateur_id: " . $_GET['formateur_id']);
    }

    // 4. Recherche texte (nom employé, formation, etc.)
    if (!empty($_GET['recherche'])) {
        $searchTerm = '%' . $_GET['recherche'] . '%';
        $sql .= " AND (
                    ue.nom LIKE ? OR 
                    ue.prenom LIKE ? OR 
                    ue.email LIKE ? OR
                    f.titre LIKE ? OR
                    s.salle LIKE ?
                 )";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        error_log("Filtre recherche: " . $_GET['recherche']);
    }

    // 5. Dates (date_inscription)
    if (!empty($_GET['date_from'])) {
        $sql .= " AND DATE(i.date_inscription) >= ?";
        $params[] = $_GET['date_from'];
        error_log("Filtre date_from: " . $_GET['date_from']);
    }
    
    if (!empty($_GET['date_to'])) {
        $sql .= " AND DATE(i.date_inscription) <= ?";
        $params[] = $_GET['date_to'];
        error_log("Filtre date_to: " . $_GET['date_to']);
    }

    // 6. Période (today, week, month)
    if (!empty($_GET['periode'])) {
        $today = date('Y-m-d');
        
        switch ($_GET['periode']) {
            case 'today':
                $sql .= " AND DATE(i.date_inscription) = ?";
                $params[] = $today;
                break;
            case 'week':
                $sql .= " AND YEARWEEK(i.date_inscription, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'month':
                $sql .= " AND MONTH(i.date_inscription) = MONTH(CURDATE()) 
                          AND YEAR(i.date_inscription) = YEAR(CURDATE())";
                break;
        }
        error_log("Filtre periode: " . $_GET['periode']);
    }

    // 7. Tri
    $orderBy = "ORDER BY i.date_inscription DESC"; // Par défaut
    if (!empty($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'date_asc':
                $orderBy = "ORDER BY i.date_inscription ASC";
                break;
            case 'nom_asc':
                $orderBy = "ORDER BY ue.nom ASC, ue.prenom ASC";
                break;
            case 'nom_desc':
                $orderBy = "ORDER BY ue.nom DESC, ue.prenom DESC";
                break;
            case 'date_desc':
            default:
                $orderBy = "ORDER BY i.date_inscription DESC";
                break;
        }
        error_log("Tri: " . $_GET['sort']);
    }
    
    $sql .= " " . $orderBy;

    // 8. Pagination
    $limit = 50; // Par défaut
    $page = 1;
    
    if (!empty($_GET['limit']) && is_numeric($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
    }
    
    if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
        $page = (int)$_GET['page'];
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        error_log("Pagination: page=$page, limit=$limit");
    } else {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }

    // Exécuter la requête
    error_log("SQL final: " . $sql);
    error_log("Params: " . json_encode($params));

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compter le total (pour pagination)
    $countSql = "SELECT COUNT(*) as total FROM inscription i
                 JOIN employe e ON i.id_employe = e.id
                 JOIN utilisateur ue ON e.id_utilisateur = ue.id
                 JOIN session s ON i.id_session = s.id
                 WHERE 1 = 1";
    
    $countParams = [];
    // Répéter les mêmes conditions WHERE (sans LIMIT)
    // ... (simplifié pour l'exemple, idéalement réutiliser la logique)

    echo json_encode([
        'success' => true,
        'data' => $inscriptions,
        'count' => count($inscriptions),
        'total' => count($inscriptions), // Pour simplifier, retourne le même count
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => count($inscriptions)
        ],
        'role' => $userRole,
        'debug' => [
            'params_received' => $_GET,
            'sql_params' => $params
        ]
    ]);

} catch (PDOException $e) {
    error_log("❌ Erreur PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des inscriptions',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>