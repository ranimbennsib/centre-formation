<?php
// backend/api/admin/employes/restore.php

// === GESTION CORS ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:8081");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role, X-Requested-With, Accept");
    header("Access-Control-Allow-Credentials: false");
    header("Access-Control-Max-Age: 86400");
    header("Content-Length: 0");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

try {
    // === INITIALISATION BDD ===
    $db = new Database();
    $pdo = $db->getConnection();
    
    // 1. Vérifier si l'employé existe et est désactivé
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
    
    if ($employe['deleted_at'] === null) {
        throw new Exception('Cet employé est déjà actif');
    }
    
    // 2. Restaurer l'employé (soft delete)
    $sqlRestore = "UPDATE utilisateur SET deleted_at = NULL WHERE id = ?";
    $stmtRestore = $pdo->prepare($sqlRestore);
    $success = $stmtRestore->execute([$id]);
    
    if (!$success) {
        throw new Exception('Erreur lors de la restauration de l\'employé');
    }
    
    // 3. Récupérer les données de l'employé restauré
    $sqlGetEmploye = "SELECT 
                        u.id, u.email, u.nom, u.prenom, u.role, u.date_inscription,
                        e.poste, e.departement, e.date_embauche, e.cin
                      FROM utilisateur u
                      LEFT JOIN employe e ON u.id = e.id_utilisateur
                      WHERE u.id = ?";
    
    $stmtGet = $pdo->prepare($sqlGetEmploye);
    $stmtGet->execute([$id]);
    $employeRestore = $stmtGet->fetch(PDO::FETCH_ASSOC);
    
    // 4. Réponse de succès
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Employé restauré avec succès',
        'data' => [
            'id' => $employeRestore['id'],
            'nom' => $employeRestore['nom'],
            'prenom' => $employeRestore['prenom'],
            'cin' => $employeRestore['cin'],
            'email' => $employeRestore['email'],
            'role' => $employeRestore['role'],
            'poste' => $employeRestore['poste'],
            'departement' => $employeRestore['departement'],
            'date_embauche' => $employeRestore['date_embauche'],
            'date_inscription' => $employeRestore['date_inscription'],
            'restored_at' => date('Y-m-d H:i:s')
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
        'message' => 'Erreur lors de la restauration',
        'error' => $e->getMessage()
    ]);
}
?>