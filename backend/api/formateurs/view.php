<?php
// backend/api/formateurs/view.php

// Headers CORS EN PREMIER
header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role");
header("Access-Control-Allow-Credentials: false");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';

// Fonction de réponse
function sendResponse($success, $message, $data = null, $status = 200) {
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Récupérer l'ID du formateur depuis l'URL
$formateurId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($formateurId <= 0) {
    sendResponse(false, 'ID formateur invalide', null, 400);
}

$db = new Database();
$pdo = $db->getConnection();

try {
    // 1. INFORMATIONS DU FORMATEUR (pas de l'admin connecté)
    $sqlFormateur = "
        SELECT 
            f.id as formateur_id,
            f.specialite,
            f.bio,
            f.experience,
            u.id as utilisateur_id,
            u.nom,
            u.prenom,
            u.email,
            u.date_inscription,
            u.role,
            DATE_FORMAT(u.date_inscription, '%d/%m/%Y %H:%i') as date_inscription_format
        FROM formateur f
        INNER JOIN utilisateur u ON f.id_utilisateur = u.id
        WHERE f.id = ? AND u.role = 'FORMATEUR'
    ";

    $stmtFormateur = $pdo->prepare($sqlFormateur);
    $stmtFormateur->execute([$formateurId]);
    $formateur = $stmtFormateur->fetch(PDO::FETCH_ASSOC);

    if (!$formateur) {
        sendResponse(false, 'Formateur non trouvé', null, 404);
    }

    // 2. STATISTIQUES DU FORMATEUR
    $sqlStats = "
        SELECT 
            COUNT(DISTINCT sf.id_session) as sessions_total,
            COUNT(DISTINCT CASE 
                WHEN s.date_debut > NOW() THEN sf.id_session 
            END) as sessions_futures,
            COUNT(DISTINCT CASE 
                WHEN s.date_debut <= NOW() THEN sf.id_session 
            END) as sessions_passees,
            COUNT(DISTINCT i.id) as inscriptions_totales,
            COUNT(DISTINCT CASE 
                WHEN i.statut = 'VALIDEE' THEN i.id 
            END) as inscriptions_validees,
            COALESCE(SUM(s.places_max), 0) as capacite_totale,
            COALESCE(AVG(s.places_max), 0) as places_moyennes,
            MIN(s.date_debut) as premiere_session,
            MAX(s.date_debut) as derniere_session
        FROM session_formateur sf
        LEFT JOIN session s ON sf.id_session = s.id
        LEFT JOIN inscription i ON s.id = i.id_session
        WHERE sf.id_formateur = ?
        AND s.deleted_at IS NULL
    ";

    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute([$formateurId]);
    $statistiques = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // 3. SESSIONS FUTURES DU FORMATEUR
    $sqlFutures = "
        SELECT 
            s.id as session_id,
            s.date_debut,
            s.date_fin,
            s.heure_debut,
            s.heure_fin,
            s.places_max,
            s.places_prises,
            s.lieu,
            s.statut,
            frm.titre as formation_titre,
            frm.description as formation_description,
            DATE_FORMAT(s.date_debut, '%d/%m/%Y') as date_debut_format,
            DATE_FORMAT(s.date_fin, '%d/%m/%Y') as date_fin_format
        FROM session_formateur sf
        INNER JOIN session s ON sf.id_session = s.id
        INNER JOIN formation frm ON s.id_formation = frm.id
        WHERE sf.id_formateur = ?
        AND s.date_debut > NOW()
        AND s.deleted_at IS NULL
        ORDER BY s.date_debut ASC
        LIMIT 5
    ";

    $stmtFutures = $pdo->prepare($sqlFutures);
    $stmtFutures->execute([$formateurId]);
    $sessions_futures = $stmtFutures->fetchAll(PDO::FETCH_ASSOC);

    // 4. SESSIONS PASSÉES DU FORMATEUR
    $sqlPassees = "
        SELECT 
            s.id as session_id,
            s.date_debut,
            s.date_fin,
            s.places_max,
            s.places_prises,
            s.lieu,
            frm.titre as formation_titre,
            DATE_FORMAT(s.date_debut, '%d/%m/%Y') as date_debut_format,
            DATE_FORMAT(s.date_fin, '%d/%m/%Y') as date_fin_format
        FROM session_formateur sf
        INNER JOIN session s ON sf.id_session = s.id
        INNER JOIN formation frm ON s.id_formation = frm.id
        WHERE sf.id_formateur = ?
        AND s.date_debut <= NOW()
        AND s.deleted_at IS NULL
        ORDER BY s.date_debut DESC
        LIMIT 5
    ";

    $stmtPassees = $pdo->prepare($sqlPassees);
    $stmtPassees->execute([$formateurId]);
    $sessions_passees = $stmtPassees->fetchAll(PDO::FETCH_ASSOC);

    // 5. FORMATIONS ENSEIGNÉES
    $sqlFormations = "
        SELECT DISTINCT
            frm.id as formation_id,
            frm.titre,
            frm.description,
            frm.duree,
            frm.niveau
        FROM session_formateur sf
        INNER JOIN session s ON sf.id_session = s.id
        INNER JOIN formation frm ON s.id_formation = frm.id
        WHERE sf.id_formateur = ?
        AND s.deleted_at IS NULL
        ORDER BY frm.titre
        LIMIT 10
    ";

    $stmtFormations = $pdo->prepare($sqlFormations);
    $stmtFormations->execute([$formateurId]);
    $formations = $stmtFormations->fetchAll(PDO::FETCH_ASSOC);

    // Formater la réponse
    $responseData = [
        'formateur' => [
            'id' => $formateur['formateur_id'],
            'utilisateur_id' => $formateur['utilisateur_id'],
            'nom' => $formateur['nom'],
            'prenom' => $formateur['prenom'],
            'email' => $formateur['email'],
            'role' => $formateur['role'],
            'specialite' => $formateur['specialite'],
            'bio' => $formateur['bio'],
            'experience' => $formateur['experience'],
            'date_inscription' => $formateur['date_inscription'],
            'date_inscription_format' => $formateur['date_inscription_format'],
            'initials' => strtoupper(substr($formateur['prenom'], 0, 1) . substr($formateur['nom'], 0, 1))
        ],
        'statistiques' => $statistiques,
        'sessions_futures' => $sessions_futures,
        'sessions_passees' => $sessions_passees,
        'formations' => $formations
    ];

    sendResponse(true, 'Détails du formateur récupérés', $responseData, 200);

} catch (PDOException $e) {
    sendResponse(false, 'Erreur base de données', ['error' => $e->getMessage()], 500);
} catch (Exception $e) {
    sendResponse(false, 'Erreur système', ['error' => $e->getMessage()], 500);
}
?>