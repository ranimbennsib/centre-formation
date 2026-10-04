<?php
// backend/api/sessions/cancel.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Admin uniquement
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin uniquement']);
    exit;
}

$sessionId = $_GET['id'] ?? null;
if (!$sessionId || !is_numeric($sessionId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de session invalide']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    // 1. Vérifier que la session existe
    $stmt = $pdo->prepare("SELECT * FROM session WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Session non trouvée']);
        exit;
    }
    
    // 2. Mettre deleted_at = NOW() pour "annuler" la session
    $sql = "UPDATE session SET deleted_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$sessionId]);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Session annulée avec succès'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'annulation']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
}
?>