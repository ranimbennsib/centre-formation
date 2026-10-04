<?php
// backend/api/auth-simple.php - VERSION TEST SIMPLE
session_start();
header('Content-Type: application/json');

// Connexion BDD
$pdo = new PDO('mysql:host=localhost;dbname=centre_formation;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Récupérer les données
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['email']) || empty($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$email = $input['email'];
$password = $input['password'];

// Test SIMPLE : mot de passe = "password" sans hash
if ($password === 'password') {
    $stmt = $pdo->prepare("SELECT id, email, nom, prenom, role FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Connecté (test simple)',
            'user' => $user
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Mot de passe = "password" pour test']);
}
?>