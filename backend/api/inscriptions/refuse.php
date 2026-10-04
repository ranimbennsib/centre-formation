<?php
// backend/api/inscriptions/refuse.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

// === ACTIVER LE DÉBOGAGE ===
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Log de début
error_log("=== REFUSE INSCRIPTION START ===");
error_log("Méthode: " . $_SERVER['REQUEST_METHOD']);
error_log("GET params: " . json_encode($_GET));
error_log("Headers: " . json_encode(getallheaders()));

// Formateur ou Admin uniquement
if (!isFormateur() && !isAdmin()) {
    error_log("❌ Accès refusé - pas formateur ni admin");
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Accès non autorisé',
        'debug' => 'Requiert formateur ou admin'
    ]);
    exit;
}

$inscriptionId = $_GET['id'] ?? null;
if (!$inscriptionId || !is_numeric($inscriptionId)) {
    error_log("❌ ID inscription invalide: " . $inscriptionId);
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'ID inscription invalide',
        'received_id' => $inscriptionId
    ]);
    exit;
}

// Récupérer les données POST
$input = file_get_contents('php://input');
error_log("Input raw: " . $input);
$data = json_decode($input, true);

// Vérifier si $data est valide
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("❌ Erreur JSON: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Données JSON invalides',
        'json_error' => json_last_error_msg(),
        'input_received' => $input
    ]);
    exit;
}

if (empty($data['raison'])) {
    error_log("❌ Raison manquante dans données: " . json_encode($data));
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Raison du refus obligatoire',
        'data_received' => $data
    ]);
    exit;
}

$db = new Database();
try {
    $pdo = $db->getConnection();
    error_log("✅ Connexion BD réussie");
} catch (Exception $e) {
    error_log("❌ Erreur connexion BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données',
        'error' => $e->getMessage()
    ]);
    exit;
}

try {
    $userId   = getCurrentUserId();
    $userRole = getCurrentUserRole();
    error_log("Utilisateur: ID=$userId, Role=$userRole");

    // Vérifier l'inscription + récupérer l'utilisateur
    $sql = "
        SELECT i.id, i.statut, e.id_utilisateur, i.id_session
        FROM inscription i
        JOIN session s ON i.id_session = s.id
        JOIN employe e ON i.id_employe = e.id
        WHERE i.id = ?
    ";

    $params = [$inscriptionId];

    // Restriction formateur
    if ($userRole === 'FORMATEUR') {
        $sql .= " AND s.id_formateur IN (
                    SELECT id FROM formateur WHERE id_utilisateur = ?
                  )";
        $params[] = $userId;
        error_log("Filtre formateur appliqué");
    }

    error_log("SQL vérification: $sql");
    error_log("Params vérification: " . json_encode($params));

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscription) {
        error_log("❌ Inscription non trouvée: ID $inscriptionId");
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'message' => 'Inscription introuvable ou non accessible'
        ]);
        exit;
    }

    error_log("Inscription trouvée: " . json_encode($inscription));

    if ($inscription['statut'] !== 'EN_ATTENTE') {
        error_log("❌ Statut incorrect: " . $inscription['statut']);
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Inscription déjà traitée (statut: ' . $inscription['statut'] . ')'
        ]);
        exit;
    }

    // Refuser l'inscription
    $stmtUpdate = $pdo->prepare("
        UPDATE inscription
        SET statut = 'REFUSEE',
            raison_refus = ?
        WHERE id = ?
    ");
    
    $updateSuccess = $stmtUpdate->execute([$data['raison'], $inscriptionId]);
    $rowsAffected = $stmtUpdate->rowCount();
    
    error_log("Update résultat: success=$updateSuccess, rows=$rowsAffected");

    if (!$updateSuccess || $rowsAffected === 0) {
        error_log("❌ Échec mise à jour inscription");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors du refus de l\'inscription'
        ]);
        exit;
    }

    // Notification (optionnel - si vous avez une table notifications)
   /*
    try {
        $sqlNotif = "
            INSERT INTO notifications (id_utilisateur, type, message, id_inscription, created_at)
            VALUES (?, 'INSCRIPTION_REFUSEE', ?, ?, NOW())
        ";
        $stmtNotif = $pdo->prepare($sqlNotif);
        $message = 'Votre inscription a été refusée : ' . $data['raison'];
        $stmtNotif->execute([
            $inscription['id_utilisateur'],
            $message,
            $inscriptionId
        ]);
        error_log("✅ Notification créée");
    } catch (Exception $e) {
        error_log("⚠️ Erreur création notification: " . $e->getMessage());
        // Ne pas échouer si la notification échoue
    }*/

    error_log("✅ Inscription refusée avec succès");
    echo json_encode([
        'success' => true,
        'message' => 'Inscription refusée avec succès',
        'inscription_id' => $inscriptionId
    ]);

} catch (PDOException $e) {
    error_log("❌ Erreur PDO: " . $e->getMessage());
    error_log("❌ Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur lors du traitement',
        'error' => $e->getMessage(),
        'sql_error' => $pdo->errorInfo()
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

error_log("=== REFUSE INSCRIPTION END ===\n");
?>