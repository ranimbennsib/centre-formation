<?php
// backend/api/admin/employes/index.php - VERSION AVEC RECHERCHE FONCTIONNELLE

// === GESTION CORS ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:8081");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role, X-Requested-With, Accept");
    header("Access-Control-Allow-Credentials: false");
    header("Access-Control-Max-Age: 86400");
    header("Content-Length: 0");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role, X-Requested-With, Accept");
header("Access-Control-Allow-Credentials: false");
header("Access-Control-Max-Age: 86400");
header('Content-Type: application/json');

// === CONNEXION BDD ===
try {
    $host = "localhost";
    $db_name = "centre_formation";
    $username = "root";
    $password = "";
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $username,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données',
        'error' => $e->getMessage()
    ]);
    exit;
}

// === RÉCUPÉRATION DES PARAMÈTRES ===
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$departement = isset($_GET['departement']) ? trim($_GET['departement']) : '';
$statut = isset($_GET['statut']) ? trim($_GET['statut']) : '';
$poste = isset($_GET['poste']) ? trim($_GET['poste']) : '';

$offset = ($page - 1) * $limit;

try {
    // === CONSTRUCTION DYNAMIQUE DE LA REQUÊTE ===
    $whereConditions = [];
    $params = [];
    
    // DEBUG: Afficher les paramètres reçus
    error_log("Paramètres reçus: search='$search', departement='$departement', statut='$statut', poste='$poste'");
    
    // FILTRE DE RECHERCHE - VERSION CORRIGÉE
    if (!empty($search)) {
        $whereConditions[] = "(u.nom LIKE :search_nom OR u.prenom LIKE :search_prenom OR u.email LIKE :search_email OR e.cin LIKE :search_cin)";
        $params[':search_nom'] = "%$search%";
        $params[':search_prenom'] = "%$search%";
        $params[':search_email'] = "%$search%";
        $params[':search_cin'] = "%$search%";
    }
    
    // FILTRE DÉPARTEMENT
    if (!empty($departement)) {
        $whereConditions[] = "e.departement = :departement";
        $params[':departement'] = $departement;
    }
    
    // FILTRE POSTE
    if (!empty($poste)) {
        $whereConditions[] = "e.poste = :poste";
        $params[':poste'] = $poste;
    }
    
    // FILTRE STATUT
    if ($statut === 'actif') {
        $whereConditions[] = "u.deleted_at IS NULL";
    } elseif ($statut === 'inactif') {
        $whereConditions[] = "u.deleted_at IS NOT NULL";
    }
    
    // Rôle employé uniquement
    $whereConditions[] = "u.role = 'EMPLOYE'";
    
    // Construction WHERE clause
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // DEBUG: Afficher la requête SQL
    error_log("Clause WHERE: $whereClause");
    error_log("Paramètres: " . print_r($params, true));
    
    // 1. REQUÊTE POUR COMPTER LE TOTAL
    $sqlCount = "SELECT COUNT(*) as total 
                 FROM utilisateur u
                 LEFT JOIN employe e ON u.id = e.id_utilisateur
                 $whereClause";
    
    $stmtCount = $pdo->prepare($sqlCount);
    
    // Binder les paramètres pour COUNT
    foreach ($params as $key => $value) {
        $stmtCount->bindValue($key, $value);
    }
    
    $stmtCount->execute();
    $totalResult = $stmtCount->fetch(PDO::FETCH_ASSOC);
    $totalItems = (int)($totalResult['total'] ?? 0);
    $totalPages = $limit > 0 ? ceil($totalItems / $limit) : 0;
    
    // 2. REQUÊTE POUR RÉCUPÉRER LES DONNÉES
    $sqlData = "SELECT 
                    u.id, 
                    u.email, 
                    u.nom, 
                    u.prenom, 
                    u.role, 
                    u.date_inscription,
                    u.deleted_at,
                    e.poste, 
                    e.departement, 
                    e.date_embauche, 
                    e.cin
                FROM utilisateur u
                LEFT JOIN employe e ON u.id = e.id_utilisateur
                $whereClause
                ORDER BY u.nom, u.prenom
                LIMIT :limit OFFSET :offset";
    
    $stmtData = $pdo->prepare($sqlData);
    
    // Binder tous les paramètres de filtre
    foreach ($params as $key => $value) {
        $stmtData->bindValue($key, $value);
    }
    
    // Binder les paramètres de pagination
    $stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmtData->execute();
    $employes = $stmtData->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. RÉCUPÉRER LES FILTRES DISPONIBLES
    // Départements
    $sqlDepartements = "SELECT DISTINCT departement 
                        FROM employe 
                        WHERE departement IS NOT NULL 
                        AND departement != ''
                        ORDER BY departement";
    
    $departements = [];
    try {
        $stmtDept = $pdo->query($sqlDepartements);
        if ($stmtDept) {
            $departements = $stmtDept->fetchAll(PDO::FETCH_COLUMN, 0);
        }
    } catch (Exception $e) {
        error_log("Erreur récupération départements: " . $e->getMessage());
    }
    
    // Postes
    $sqlPostes = "SELECT DISTINCT poste 
                  FROM employe 
                  WHERE poste IS NOT NULL 
                  AND poste != ''
                  ORDER BY poste";
    
    $postes = [];
    try {
        $stmtPostes = $pdo->query($sqlPostes);
        if ($stmtPostes) {
            $postes = $stmtPostes->fetchAll(PDO::FETCH_COLUMN, 0);
        }
    } catch (Exception $e) {
        error_log("Erreur récupération postes: " . $e->getMessage());
    }
    
    // 4. FORMATAGE DES DONNÉES
    $formattedEmployes = [];
    foreach ($employes as $employe) {
        $formattedEmployes[] = [
            'id' => (int)$employe['id'],
            'nom' => $employe['nom'] ?? '',
            'prenom' => $employe['prenom'] ?? '',
            'cin' => $employe['cin'] ?? '',
            'email' => $employe['email'] ?? '',
            'role' => $employe['role'] ?? 'EMPLOYE',
            'poste' => $employe['poste'] ?? null,
            'departement' => $employe['departement'] ?? null,
            'date_embauche' => $employe['date_embauche'] ?? null,
            'date_inscription' => $employe['date_inscription'] ?? null,
            'deleted_at' => $employe['deleted_at'] ?? null,
            'statut' => (!empty($employe['deleted_at'])) ? 'inactif' : 'actif'
        ];
    }
    
    // 5. RÉPONSE
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $formattedEmployes,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ],
        'available_filters' => [
            'departements' => $departements,
            'postes' => $postes
        ],
        'metadata' => [
            'query_params' => [
                'search' => $search,
                'departement' => $departement,
                'statut' => $statut,
                'poste' => $poste
            ],
            'count' => count($formattedEmployes)
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage(),
        'error_info' => isset($pdo) ? $pdo->errorInfo() : null,
        'sql' => isset($sqlData) ? $sqlData : 'Non défini',
        'params' => $params ?? [],
        'where_clause' => $whereClause ?? 'Non défini'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des employés',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>