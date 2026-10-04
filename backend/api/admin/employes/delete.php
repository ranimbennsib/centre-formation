<?php
// backend/api/admin/employes/delete.php

// === GESTION CORS ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:8081");
    header("Access-Control-Allow-Methods: DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role, X-Requested-With, Accept");
    header("Access-Control-Allow-Credentials: false");
    header("Access-Control-Max-Age: 86400");
    header("Content-Length: 0");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role, X-Requested-With, Accept");
header("Access-Control-Allow-Credentials: false");
header("Access-Control-Max-Age: 86400");
header('Content-Type: application/json');

// === INCLUSIONS ===
//require_once '../../includes/cors.php';
require_once '../../../includes/auth-check.php';
require_once '../../../config/database.php';

// === VÉRIFICATION ADMIN ===
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé. Admin uniquement.']);
    exit;
}

// === VÉRIFICATION MÉTHODE ===
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// === RÉCUPÉRATION ID ===
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

// === RÉCUPÉRATION DONNÉES (pour action) ===
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
$action = $data['action'] ?? 'soft_delete'; // Par défaut soft delete

try {
    // === INITIALISATION BDD ===
    $db = new Database();
    $pdo = $db->getConnection();
    
    // 1. Vérifier si l'employé existe
    $stmtCheck = $pdo->prepare("
        SELECT u.id, u.email, u.deleted_at 
        FROM utilisateur u
        JOIN employe e ON u.id = e.id_utilisateur
        WHERE u.id = ?
    ");
    $stmtCheck->execute([$id]);
    $employe = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$employe) {
        throw new Exception('Employé non trouvé');
    }
    
    if ($employe['deleted_at'] !== null) {
        throw new Exception('Cet employé est déjà désactivé');
    }
    
    // 2. SOFT DELETE (désactivation)
    if ($action === 'soft_delete') {
        $sqlSoftDelete = "UPDATE utilisateur SET deleted_at = NOW() WHERE id = ?";
        $stmtSoftDelete = $pdo->prepare($sqlSoftDelete);
        $success = $stmtSoftDelete->execute([$id]);
        
        if (!$success) {
            throw new Exception('Erreur lors de la désactivation de l\'employé');
        }
        
        $message = 'Employé désactivé avec succès (soft delete)';
        
    // 3. HARD DELETE (suppression définitive)  
    } elseif ($action === 'hard_delete') {
        // Commencer une transaction
        $pdo->beginTransaction();
        
        try {
            // Supprimer d'abord l'employé (table enfant)
            $sqlDeleteEmploye = "DELETE FROM employe WHERE id_utilisateur = ?";
            $stmtDeleteEmploye = $pdo->prepare($sqlDeleteEmploye);
            $stmtDeleteEmploye->execute([$id]);
            
            // Puis supprimer l'utilisateur (table parent)
            $sqlDeleteUser = "DELETE FROM utilisateur WHERE id = ?";
            $stmtDeleteUser = $pdo->prepare($sqlDeleteUser);
            $stmtDeleteUser->execute([$id]);
            
            $pdo->commit();
            $message = 'Employé supprimé définitivement avec succès';
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception('Erreur lors de la suppression définitive: ' . $e->getMessage());
        }
        
    } else {
        throw new Exception('Action non valide. Utilisez "soft_delete" ou "hard_delete"');
    }
    
    // 4. Réponse de succès
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'id' => $id,
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression',
        'error' => $e->getMessage()
    ]);
}
?>