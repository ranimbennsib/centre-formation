<?php
// backend/api/auth/logout.php
require_once '../../includes/cors.php';
session_start();

// Détruire la session
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Déconnexion réussie'
]);
?>