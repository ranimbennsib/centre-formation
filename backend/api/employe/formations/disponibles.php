<?php
// backend/api/employe/formations/disponibles.php

// === CONFIGURATION CORS ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:8081");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role");
    header("Access-Control-Allow-Credentials: false");
    header("Access-Control-Max-Age: 86400");
    header("Content-Length: 0");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role");
header("Access-Control-Allow-Credentials: false");
header('Content-Type: application/json');

// === INCLUSIONS ===
$baseDir = dirname(__FILE__) . '/../../../';
require_once $baseDir . 'includes/cors.php';
require_once $baseDir . 'includes/auth-check.php';
require_once $baseDir . 'config/database.php';

// === VÉRIFICATION EMPLOYÉ ===
if (!isEmploye() && !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux employés']);
    exit;
}

// === RÉCUPÉRATION USER ID ===
$userId = getUserId();
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// === INITIALISATION BDD ===
$db = new Database();
$pdo = $db->getConnection();

try {
    // Récupérer les paramètres de filtrage
    $categorie = $_GET['categorie'] ?? null;
    $search = $_GET['search'] ?? null;
    
    // Requête SQL de base
    $sql = "SELECT 
                f.id, 
                f.titre, 
                f.description AS description_short,
                f.description_long,
                f.categorie,
                f.duree,
                f.prix,
                f.niveau,
                f.places_max,
                f.statut,
                f.prerequis,
                f.objectifs,
                f.created_at,
                
                -- Nombre de places disponibles
                (f.places_max - COALESCE(COUNT(i.id), 0)) as places_restantes,
                
                -- Prochaine session
                MIN(s.date_debut) as prochaine_session,
                
                -- Formateur
                CONCAT(u.nom, ' ', u.prenom) as formateur_nom,
                u.email as formateur_email
                
            FROM formation f
            
            -- Jointure pour les sessions
            LEFT JOIN session s ON f.id = s.id_formation 
                AND s.date_debut > NOW() 
                AND s.statut = 'planifiee'
            
            -- Jointure pour les inscriptions
            LEFT JOIN inscription i ON s.id = i.id_session 
                AND i.statut IN ('validee', 'en_attente')
            
            -- Jointure pour le formateur
            LEFT JOIN utilisateur u ON s.id_formateur = u.id
            
            -- Conditions de base
            WHERE f.statut = 'active' 
                AND f.places_max > 0
                AND (s.id IS NULL OR s.date_debut > DATE_ADD(NOW(), INTERVAL 1 DAY))
            
            -- Filtres optionnels
            " . ($categorie ? " AND f.categorie = :categorie" : "") . "
            " . ($search ? " AND (f.titre LIKE :search OR f.description LIKE :search)" : "") . "
            
            GROUP BY f.id
            ORDER BY prochaine_session ASC, f.titre ASC";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind des paramètres
    if ($categorie) {
        $stmt->bindValue(':categorie', $categorie);
    }
    if ($search) {
        $stmt->bindValue(':search', '%' . $search . '%');
    }
    
    $stmt->execute();
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pour chaque formation, récupérer les sessions disponibles
    foreach ($formations as &$formation) {
        // Sessions disponibles pour cette formation
        $sqlSessions = "SELECT 
                            s.id, 
                            s.date_debut, 
                            s.date_fin,
                            s.heure_debut,
                            s.heure_fin,
                            s.lieu,
                            s.statut,
                            s.places_max,
                            (s.places_max - COALESCE(COUNT(i.id), 0)) as places_restantes,
                            CONCAT(u.nom, ' ', u.prenom) as formateur_nom
                        FROM session s
                        LEFT JOIN inscription i ON s.id = i.id_session 
                            AND i.statut IN ('validee', 'en_attente')
                        LEFT JOIN utilisateur u ON s.id_formateur = u.id
                        WHERE s.id_formation = :formation_id
                            AND s.statut = 'planifiee'
                            AND s.date_debut > NOW()
                        GROUP BY s.id
                        HAVING places_restantes > 0
                        ORDER BY s.date_debut ASC";
        
        $stmtSessions = $pdo->prepare($sqlSessions);
        $stmtSessions->bindValue(':formation_id', $formation['id']);
        $stmtSessions->execute();
        $sessions = $stmtSessions->fetchAll(PDO::FETCH_ASSOC);
        
        $formation['sessions'] = $sessions;
        
        // Vérifier si l'employé est déjà inscrit
        $sqlCheckInscription = "SELECT COUNT(*) as deja_inscrit
                                FROM inscription i
                                JOIN session s ON i.id_session = s.id
                                WHERE i.id_utilisateur = :user_id
                                AND s.id_formation = :formation_id
                                AND i.statut IN ('validee', 'en_attente')";
        
        $stmtCheck = $pdo->prepare($sqlCheckInscription);
        $stmtCheck->bindValue(':user_id', $userId);
        $stmtCheck->bindValue(':formation_id', $formation['id']);
        $stmtCheck->execute();
        $check = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        $formation['deja_inscrit'] = $check['deja_inscrit'] > 0;
        
        // Formater les dates
        if ($formation['prochaine_session']) {
            $formation['prochaine_session'] = date('Y-m-d', strtotime($formation['prochaine_session']));
        }
    }
    
    // Récupérer les catégories uniques
    $sqlCategories = "SELECT DISTINCT categorie FROM formation WHERE statut = 'active' ORDER BY categorie";
    $stmtCategories = $pdo->query($sqlCategories);
    $categories = $stmtCategories->fetchAll(PDO::FETCH_COLUMN);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $formations,
        'categories' => $categories,
        'count' => count($formations)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur',
        'error' => $e->getMessage()
    ]);
}
?>