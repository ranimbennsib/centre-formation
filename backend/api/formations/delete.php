<?php
// backend/api/formations/delete.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// ============ DEBUG ============
error_log("=== DEBUG DELETE FORMATION ===");
error_log("Headers reçus: " . json_encode(getallheaders()));
error_log("X-User-Role: " . ($_SERVER['HTTP_X_USER_ROLE'] ?? 'NOT SET'));
error_log("X-User-Id: " . ($_SERVER['HTTP_X_USER_ID'] ?? 'NOT SET'));
error_log("ID à supprimer: " . ($_GET['id'] ?? 'NOT SET'));
// ============ FIN DEBUG ============

// Seul l'admin peut supprimer - CORRIGEZ CETTE LIGNE
if (!isAdmin()) {
    error_log("❌ isAdmin() retourne FALSE - Accès refusé");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

error_log("✅ isAdmin() retourne TRUE - Accès autorisé");

$db = new Database();
$pdo = $db->getConnection();

// Récupérer l'ID
$formationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($formationId === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID formation requis']);
    exit;
}

try {
    // Vérifier si la formation existe
    $stmt = $pdo->prepare("SELECT id, titre FROM formation WHERE id = ?");
    $stmt->execute([$formationId]);
    $formation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$formation) {
        error_log("❌ Formation non trouvée ID: $formationId");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Formation non trouvée']);
        exit;
    }
    
    error_log("✅ Formation trouvée: {$formation['titre']} (ID: {$formation['id']})");
    
    // Vérifier si la formation a des sessions actives
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM session 
        WHERE id_formation = ? 
        AND date_fin > NOW() -- Sessions futures
    ");
    $stmt->execute([$formationId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    
    error_log("🔍 Sessions futures trouvées: $count");
    
    if ($count > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Impossible de supprimer : cette formation a des sessions planifiées',
            'sessions_count' => $count
        ]);
        exit;
    }
    
    // Option 1: Soft delete (recommandé) - décommenter pour utiliser
    
    error_log("🔄 Soft delete de la formation ID: $formationId");
    $stmt = $pdo->prepare("UPDATE formation SET est_actif = 0 WHERE id = ?");
    $success = $stmt->execute([$formationId]);
    
    if ($success) {
        error_log("✅ Formation désactivée avec succès");
        echo json_encode([
            'success' => true,
            'message' => "Formation '{$formation['titre']}' désactivée avec succès"
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("❌ Erreur soft delete: " . json_encode($errorInfo));
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la désactivation'
        ]);
    }
    
    
    // Option 2: Hard delete
    /*
    error_log("🗑️ Hard delete de la formation ID: $formationId");
    $stmt = $pdo->prepare("DELETE FROM formation WHERE id = ?");
    $success = $stmt->execute([$formationId]);
    
    if ($success) {
        error_log("✅ Formation supprimée avec succès");
        echo json_encode([
            'success' => true,
            'message' => "Formation '{$formation['titre']}' supprimée avec succès"
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("❌ Erreur suppression: " . json_encode($errorInfo));
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la suppression',
            'error' => $errorInfo[2] ?? 'Erreur inconnue'
        ]);
    }*/
    
} catch (PDOException $e) {
    error_log("❌ Exception PDO: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression',
        'error' => $e->getMessage()
    ]);
}
?>