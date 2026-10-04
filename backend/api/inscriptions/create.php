<?php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Employé uniquement
if (!isEmploye()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux employés']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id_session']) || !is_numeric($data['id_session'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID session invalide']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    $userId = getCurrentUserId();
    $sessionId = (int)$data['id_session'];

    /* 1️⃣ récupérer l’employé */
    $stmtEmp = $pdo->prepare(
        "SELECT id FROM employe WHERE id_utilisateur = ?"
    );
    $stmtEmp->execute([$userId]);
    $employe = $stmtEmp->fetch(PDO::FETCH_ASSOC);

    if (!$employe) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Profil employé introuvable']);
        exit;
    }

    $idEmploye = $employe['id'];

    /* 2️⃣ vérifier la session */
    $sqlSession = "
        SELECT s.*, f.titre AS formation_titre
        FROM session s
        JOIN formation f ON s.id_formation = f.id
        WHERE s.id = ?
          AND s.deleted_at IS NULL
          AND s.statut_formateur = 'ACCEPTEE'
          AND s.date_debut > NOW()
          AND s.places_restantes > 0
    ";

    $stmt = $pdo->prepare($sqlSession);
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Session non disponible']);
        exit;
    }

    /* 3️⃣ vérifier inscription existante */
    $sqlCheck = "
        SELECT id FROM inscription
        WHERE id_employe = ? AND id_session = ?
          AND statut NOT IN ('ANNULEE', 'REFUSEE')
    ";

    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$idEmploye, $sessionId]);

    if ($stmtCheck->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Déjà inscrit à cette session']);
        exit;
    }

    /* 4️⃣ créer l’inscription */
    $sqlInsert = "
        INSERT INTO inscription (id_employe, id_session, statut)
        VALUES (?, ?, 'EN_ATTENTE')
    ";

    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$idEmploye, $sessionId]);

    $inscriptionId = $pdo->lastInsertId();

    /* 5️⃣ décrémenter les places */
    $pdo->prepare(
        "UPDATE session SET places_restantes = places_restantes - 1 WHERE id = ?"
    )->execute([$sessionId]);

    /* 6️⃣ notifier le formateur */
    /*
    if (!empty($session['id_formateur'])) {
        $stmtFormateur = $pdo->prepare(
            "SELECT id_utilisateur FROM formateur WHERE id = ?"
        );
        $stmtFormateur->execute([$session['id_formateur']]);
        $formateur = $stmtFormateur->fetch(PDO::FETCH_ASSOC);

        if ($formateur) {
            $pdo->prepare(
                "INSERT INTO notifications (id_utilisateur, type, message, id_session)
                 VALUES (?, 'NOUVELLE_INSCRIPTION',
                         'Un employé a demandé une inscription à votre session',
                         ?)"
            )->execute([$formateur['id_utilisateur'], $sessionId]);
        }
    }*/

    echo json_encode([
        'success' => true,
        'message' => 'Inscription créée avec succès',
        'inscription_id' => $inscriptionId,
        'session' => [
            'titre' => $session['formation_titre'],
            'date_debut' => $session['date_debut']
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