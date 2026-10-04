<?php
// backend/api/inscriptions/validate.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

// Activer le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Log de début
error_log("=== VALIDATE INSCRIPTION START ===");

// Formateur ou Admin uniquement
if (!isFormateur() && !isAdmin()) {
    error_log("❌ Accès refusé - pas formateur ni admin");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Formateur ou Admin uniquement']);
    exit;
}

$inscriptionId = $_GET['id'] ?? null;
if (!$inscriptionId || !is_numeric($inscriptionId)) {
    error_log("❌ ID invalide: " . $inscriptionId);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inscription invalide']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    $userId = getCurrentUserId();
    $userRole = getCurrentUserRole();
    error_log("Utilisateur: ID=$userId, Role=$userRole");

    // 1. Vérifier que l'inscription existe et est accessible
    $sql = "SELECT i.*, s.date_debut, s.id_formateur, s.places_max, e.id_utilisateur,
                   (SELECT COUNT(*) FROM inscription i2 
                    WHERE i2.id_session = s.id AND i2.statut = 'VALIDEE') as places_occupees
            FROM inscription i
            JOIN session s ON i.id_session = s.id
            JOIN employe e ON i.id_employe = e.id -- Joindre employe pour avoir id_utilisateur
            WHERE i.id = ?";
    
    $params = [$inscriptionId];
    
    if ($userRole === 'FORMATEUR') {
        $sql .= " AND s.id_formateur IN (
                    SELECT id FROM formateur WHERE id_utilisateur = ?
                  )";
        $params[] = $userId;
    }
    
    error_log("SQL: " . $sql);
    error_log("Params: " . json_encode($params));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inscription) {
        error_log("❌ Inscription non trouvée: ID $inscriptionId");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Inscription non trouvée ou non accessible']);
        exit;
    }
    
    error_log("Inscription trouvée: " . json_encode($inscription));
    
    // 2. Vérifier le statut
    if ($inscription['statut'] !== 'EN_ATTENTE') {
        error_log("❌ Statut incorrect: " . $inscription['statut']);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Inscription déjà traitée (statut: ' . $inscription['statut'] . ')']);
        exit;
    }
    
    // 3. Vérifier places disponibles
    $placesDisponibles = $inscription['places_max'] - $inscription['places_occupees'];
    error_log("Places: max=" . $inscription['places_max'] . ", occupées=" . $inscription['places_occupees'] . ", disponibles=$placesDisponibles");
    
    if ($placesDisponibles <= 0) {
        error_log("❌ Plus de places disponibles");
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Plus de places disponibles dans cette session'
        ]);
        exit;
    }
    
    // 4. Valider l'inscription
    $sqlUpdate = "UPDATE inscription 
                  SET statut = 'VALIDEE',
                      raison_refus = NULL
                  WHERE id = ?";
    
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $success = $stmtUpdate->execute([$inscriptionId]);
    $rowsAffected = $stmtUpdate->rowCount();
    
    error_log("Update résultat: success=$success, rows=$rowsAffected");
    
    if (!$success || $rowsAffected === 0) {
        error_log("❌ Échec mise à jour");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation']);
        exit;
    }
    
    // 5. Notifier l'employé (optionnel)
    /*
    try {
        if (!empty($inscription['id_utilisateur'])) {
            $sqlNotif = "INSERT INTO notifications (id_utilisateur, type, message, id_session, created_at)
                         VALUES (?, 'INSCRIPTION_VALIDEE', 
                                 'Votre inscription à la session a été validée',
                                 ?, NOW())";
            $stmtNotif = $pdo->prepare($sqlNotif);
            $stmtNotif->execute([$inscription['id_utilisateur'], $inscription['id_session']]);
            error_log("✅ Notification créée pour utilisateur ID: " . $inscription['id_utilisateur']);
        } else {
            error_log("⚠️ id_utilisateur non trouvé, notification ignorée");
        }
    } catch (Exception $e) {
        error_log("⚠️ Erreur création notification: " . $e->getMessage());
        // Ne pas échouer si la notification échoue
    }*/
    
    error_log("✅ Inscription validée avec succès");
    echo json_encode([
        'success' => true,
        'message' => 'Inscription validée avec succès'
    ]);

} catch (PDOException $e) {
    error_log("❌ Erreur PDO: " . $e->getMessage());
    error_log("❌ Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("❌ Erreur générale: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur',
        'error' => $e->getMessage()
    ]);
}

error_log("=== VALIDATE INSCRIPTION END ===\n");
?>