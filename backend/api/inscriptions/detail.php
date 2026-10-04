<?php
// backend/api/inscriptions/detail.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$inscriptionId = $_GET['id'] ?? null;
if (!$inscriptionId || !is_numeric($inscriptionId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inscription invalide']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    $userId   = getCurrentUserId();
    $userRole = getCurrentUserRole();

    $sql = "
        SELECT
            i.*,
            ue.nom AS employe_nom,
            ue.prenom AS employe_prenom,
            ue.email AS employe_email,

            s.id AS session_id,
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
        WHERE i.id = ?
    ";

    $params = [$inscriptionId];

    /* 🔐 Gestion des permissions */
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

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscription) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Inscription non trouvée ou accès refusé'
        ]);
        exit;
    }

    /* 🧠 Actions possibles */
    $actions = [];

    if (
        $userRole === 'EMPLOYE' &&
        $inscription['statut'] === 'EN_ATTENTE' &&
        strtotime($inscription['date_debut']) > time()
    ) {
        $actions[] = 'annuler';
    }

    if (
        in_array($userRole, ['FORMATEUR', 'ADMIN']) &&
        $inscription['statut'] === 'EN_ATTENTE'
    ) {
        $actions[] = 'valider';
        $actions[] = 'refuser';
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'inscription' => $inscription,
            'actions_possibles' => $actions
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