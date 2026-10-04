<?php
// backend/api/employe/inscriptions/mes-inscriptions.php

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
    // Paramètres
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT 
                i.id as inscription_id,
                i.date_inscription,
                i.statut as inscription_statut,
                i.raison_refus,
                i.date_validation,
                
                -- Session
                s.id as session_id,
                s.date_debut,
                s.date_fin,
                s.heure_debut,
                s.heure_fin,
                s.lieu,
                s.statut as session_statut,
                
                -- Formation
                f.id as formation_id,
                f.titre as formation_titre,
                f.categorie as formation_categorie,
                f.duree as formation_duree,
                f.niveau as formation_niveau,
                
                -- Formateur
                CONCAT(u_formateur.nom, ' ', u_formateur.prenom) as formateur_nom
                
            FROM inscription i
            
            JOIN session s ON i.id_session = s.id
            JOIN formation f ON s.id_formation = f.id
            LEFT JOIN utilisateur u_formateur ON s.id_formateur = u_formateur.id
            
            WHERE i.id_utilisateur = :user_id
            AND i.deleted_at IS NULL
            
            " . ($status ? " AND i.statut = :status" : "") . "
            
            ORDER BY 
                CASE i.statut 
                    WHEN 'en_attente' THEN 1
                    WHEN 'validee' THEN 2
                    WHEN 'refusee' THEN 3
                    WHEN 'annulee' THEN 4
                    ELSE 5
                END,
                s.date_debut ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $userId);
    
    if ($status) {
        $stmt->bindValue(':status', $status);
    }
    
    $stmt->execute();
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les données
    foreach ($inscriptions as &$inscription) {
        // Dates formatées
        $inscription['date_inscription_formatted'] = date('d/m/Y H:i', strtotime($inscription['date_inscription']));
        
        if ($inscription['date_debut']) {
            $inscription['date_debut_formatted'] = date('d/m/Y', strtotime($inscription['date_debut']));
            $inscription['date_fin_formatted'] = date('d/m/Y', strtotime($inscription['date_fin']));
            $inscription['session_periode'] = $inscription['date_debut_formatted'] . ' - ' . $inscription['date_fin_formatted'];
        }
        
        // Statut avec icône
        $inscription['statut_icon'] = getStatusIcon($inscription['inscription_statut']);
        $inscription['statut_color'] = getStatusColor($inscription['inscription_statut']);
        
        // Peut annuler ?
        $inscription['can_cancel'] = in_array($inscription['inscription_statut'], ['en_attente', 'validee']) 
            && strtotime($inscription['date_debut']) > time();
    }
    
    // Statistiques
    $stats = [
        'total' => count($inscriptions),
        'en_attente' => 0,
        'validees' => 0,
        'refusees' => 0,
        'annulees' => 0
    ];
    
    foreach ($inscriptions as $inscription) {
        $stats[$inscription['inscription_statut']] = ($stats[$inscription['inscription_statut']] ?? 0) + 1;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $inscriptions,
        'stats' => $stats,
        'count' => count($inscriptions)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
}

// Fonctions utilitaires
function getStatusIcon($status) {
    $icons = [
        'en_attente' => 'bi-hourglass',
        'validee' => 'bi-check-circle',
        'refusee' => 'bi-x-circle',
        'annulee' => 'bi-dash-circle'
    ];
    return $icons[$status] ?? 'bi-question-circle';
}

function getStatusColor($status) {
    $colors = [
        'en_attente' => 'warning',
        'validee' => 'success',
        'refusee' => 'danger',
        'annulee' => 'secondary'
    ];
    return $colors[$status] ?? 'light';
}
?>