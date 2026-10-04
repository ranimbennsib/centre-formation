<?php
// backend/api/formateurs/delete.php
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

// Récupérer l'ID du formateur
$formateurId = $_GET['id'] ?? null;
if (!$formateurId || !is_numeric($formateurId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID formateur invalide']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    // 1. Vérifier que le formateur existe et n'est pas déjà supprimé
    $stmtCheck = $pdo->prepare("SELECT f.*, u.id as utilisateur_id, u.deleted_at as user_deleted_at
                                FROM formateur f 
                                JOIN utilisateur u ON f.id_utilisateur = u.id 
                                WHERE f.id = ?");
    $stmtCheck->execute([$formateurId]);
    $formateur = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$formateur) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Formateur non trouvé']);
        exit;
    }
    
    // Vérifier si déjà supprimé
    if ($formateur['deleted_at'] !== null || $formateur['user_deleted_at'] !== null) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Formateur déjà supprimé',
            'details' => [
                'formateur_deleted_at' => $formateur['deleted_at'],
                'utilisateur_deleted_at' => $formateur['user_deleted_at']
            ]
        ]);
        exit;
    }
    
    // 2. Vérifier s'il a des sessions futures
    $stmtSessions = $pdo->prepare("SELECT COUNT(*) as count 
                                   FROM session 
                                   WHERE id_formateur = ? 
                                   AND date_debut > NOW()
                                   AND deleted_at IS NULL");
    $stmtSessions->execute([$formateurId]);
    $sessionsFutures = $stmtSessions->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($sessionsFutures > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Impossible de supprimer ce formateur',
            'raison' => "Le formateur a $sessionsFutures session(s) future(s) programmée(s)",
            'solution' => "Réassignez d'abord ses sessions avant de le supprimer"
        ]);
        exit;
    }
    
    // 3. SOFT DELETE dans les DEUX tables (formateur ET utilisateur)
    $pdo->beginTransaction();
    
    try {
        $currentDateTime = date('Y-m-d H:i:s');
        
        // Marquer le formateur comme supprimé
        $sqlFormateurDelete = "UPDATE formateur SET deleted_at = ? WHERE id = ?";
        $stmtFormateur = $pdo->prepare($sqlFormateurDelete);
        $stmtFormateur->execute([$currentDateTime, $formateurId]);
        
        // Marquer l'utilisateur comme supprimé
        $sqlUtilisateurDelete = "UPDATE utilisateur SET deleted_at = ? WHERE id = ?";
        $stmtUtilisateur = $pdo->prepare($sqlUtilisateurDelete);
        $stmtUtilisateur->execute([$currentDateTime, $formateur['utilisateur_id']]);
        
        $pdo->commit();
        
        // 4. Réponse de succès
        echo json_encode([
            'success' => true,
            'message' => 'Formateur supprimé avec succès',
            'details' => [
                'formateur_id' => $formateurId,
                'utilisateur_id' => $formateur['utilisateur_id'],
                'deleted_at' => $currentDateTime,
                'tables_affectees' => ['formateur', 'utilisateur'],
                'type_suppression' => 'soft_delete'
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression',
        'error' => $e->getMessage()
    ]);
}
?>