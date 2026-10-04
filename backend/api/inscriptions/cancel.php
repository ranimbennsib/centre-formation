<?php
// backend/api/inscriptions/cancel.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Employé uniquement
if (! isEmploye()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Accès réservé aux employés'
    ]);
    exit;
}

$inscriptionId = $_GET['id'] ?? null;
if (!$inscriptionId || !is_numeric($inscriptionId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID inscription invalide'
    ]);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    $userId = getCurrentUserId();

    /* 1️⃣ Vérifier que l'inscription existe
          et qu'elle appartient à l'employé connecté */
    $sql = "
        SELECT 
            i.id,
            i.statut,
            i.id_session,
            s.date_debut,
            s.id_formateur
        FROM inscription i
        JOIN employe e ON i.id_employe = e.id
        JOIN session s ON i.id_session = s.id
        WHERE i.id = ? AND e.id_utilisateur = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inscriptionId, $userId]);
    $inscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscription) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Inscription introuvable'
        ]);
        exit;
    }

    /* 2️⃣ Vérifications métier */
    if ($inscription['statut'] === 'ANNULEE') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Inscription déjà annulée'
        ]);
        exit;
    }

    if ($inscription['statut'] === 'REFUSEE') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Inscription refusée, annulation impossible'
        ]);
        exit;
    }

    // La session ne doit pas avoir commencé
    if (strtotime($inscription['date_debut']) <= time()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'La session a déjà commencé'
        ]);
        exit;
    }

    /* 3️⃣ Annuler l'inscription */
    $sqlUpdate = "
        UPDATE inscription
        SET statut = 'ANNULEE',
            raison_refus = 'Annulé par l\'employé'
        WHERE id = ?
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([$inscriptionId]);
     /* 4️⃣ Notifier le formateur */
    /*
     if (!empty($inscription['id_formateur'])) {

        // Récupérer l'utilisateur du formateur
        $sqlFormateur = "SELECT id_utilisateur FROM formateur WHERE id = ?";
        $stmtFormateur = $pdo->prepare($sqlFormateur);
        $stmtFormateur->execute([$inscription['id_formateur']]);
        $formateur = $stmtFormateur->fetch(PDO::FETCH_ASSOC);

        if ($formateur) {
            $sqlNotif = "
                INSERT INTO notifications (id_utilisateur, type, message, id_session)
                VALUES (?, 'INSCRIPTION_ANNULEE',
                        'Un employé a annulé son inscription à votre session',
                        ?)
            ";

            $stmtNotif = $pdo->prepare($sqlNotif);
            $stmtNotif->execute([
                $formateur['id_utilisateur'],
                $inscription['id_session']
            ]);
        }
    }
    */
   
   

    echo json_encode([
        'success' => true,
        'message' => 'Inscription annulée avec succès'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur',
        'error' => $e->getMessage()
    ]);
}
