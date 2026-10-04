<?php
// backend/api/formateurs/detail.php - AVEC CIN

require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$formateurId = $_GET['id'] ?? null;

if (!$formateurId || !is_numeric($formateurId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID formateur invalide'
    ]);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    /* =========================
       1️⃣ Infos formateur AVEC CIN
    ========================= */
    $sqlFormateur = "
        SELECT 
            f.*,
            u.id AS utilisateur_id,
            u.email,
            u.nom,
            u.prenom,
            u.date_inscription
        FROM formateur f
        INNER JOIN utilisateur u ON f.id_utilisateur = u.id
        WHERE f.id = ?
    ";

    $stmt = $pdo->prepare($sqlFormateur);
    $stmt->execute([$formateurId]);
    $formateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$formateur) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Formateur non trouvé'
        ]);
        exit;
    }

    /* =========================
       2️⃣ Statistiques
    ========================= */
    $sqlStats = "
        SELECT
            COUNT(DISTINCT s.id) AS sessions_total,
            COUNT(DISTINCT CASE WHEN s.statut_formateur = 'ACCEPTEE' THEN s.id END) AS sessions_acceptees,
            COUNT(DISTINCT CASE WHEN s.statut_formateur = 'EN_ATTENTE' THEN s.id END) AS sessions_attente,
            COUNT(DISTINCT CASE WHEN s.statut_formateur = 'REFUSEE' THEN s.id END) AS sessions_refusees,

            COUNT(DISTINCT i.id) AS inscriptions_totales,
            COUNT(DISTINCT CASE WHEN i.statut = 'VALIDEE' THEN i.id END) AS inscriptions_validees,
            COUNT(DISTINCT CASE WHEN i.statut = 'EN_ATTENTE' THEN i.id END) AS inscriptions_attente,
            COUNT(DISTINCT CASE WHEN i.statut = 'REFUSEE' THEN i.id END) AS inscriptions_refusees,
            COUNT(DISTINCT CASE WHEN i.statut = 'ANNULEE' THEN i.id END) AS inscriptions_annulees,

            AVG(s.places_max) AS places_moyennes,
            SUM(s.places_max) AS capacite_totale,
            MIN(s.date_debut) AS premiere_session,
            MAX(s.date_debut) AS derniere_session
        FROM session s
        LEFT JOIN inscription i ON s.id = i.id_session
        WHERE s.id_formateur = ?
        AND s.deleted_at IS NULL
    ";

    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute([$formateurId]);
    $statistiques = $stmtStats->fetch(PDO::FETCH_ASSOC);

    /* =========================
       3️⃣ Sessions futures
    ========================= */
    $sqlFutures = "
        SELECT 
            s.*,
            f.titre AS formation_titre,
            f.description AS formation_description
        FROM session s
        INNER JOIN formation f ON s.id_formation = f.id
        WHERE s.id_formateur = ?
        AND s.statut_formateur = 'ACCEPTEE'
        AND s.date_debut > NOW()
        AND s.deleted_at IS NULL
        ORDER BY s.date_debut ASC
        LIMIT 10
    ";

    $stmtFutures = $pdo->prepare($sqlFutures);
    $stmtFutures->execute([$formateurId]);
    $sessions_futures = $stmtFutures->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       4️⃣ Sessions passées
    ========================= */
    $sqlPassees = "
        SELECT 
            s.*,
            f.titre AS formation_titre
        FROM session s
        INNER JOIN formation f ON s.id_formation = f.id
        WHERE s.id_formateur = ?
        AND s.date_debut <= NOW()
        AND s.deleted_at IS NULL
        ORDER BY s.date_debut DESC
        LIMIT 5
    ";

    $stmtPassees = $pdo->prepare($sqlPassees);
    $stmtPassees->execute([$formateurId]);
    $sessions_passees = $stmtPassees->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       ✅ Réponse finale AVEC CIN
    ========================= */
    echo json_encode([
        'success' => true,
        'data' => [
            'formateur' => $formateur,
            'statistiques' => $statistiques,
            'sessions_futures' => $sessions_futures,
            'sessions_passees' => $sessions_passees
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur',
        'error' => $e->getMessage()
    ]);
}
?>