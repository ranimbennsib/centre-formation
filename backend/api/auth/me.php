<?php
// backend/api/auth/me.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';

// L'utilisateur est déjà vérifié par requireAuth()
echo json_encode([
    'success' => true,
    'user' => [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'nom' => $_SESSION['nom'],
        'prenom' => $_SESSION['prenom'],
       
    ]
]);
?>