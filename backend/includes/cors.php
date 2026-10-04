<?php
// backend/includes/cors.php

// Obtenir l'origine de la requête
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

// DEBUG
error_log("=== CORS DEBUG ===");
error_log("Origin reçu: " . $origin);
error_log("Méthode: " . $_SERVER['REQUEST_METHOD']);
error_log("Headers reçus: " . ($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? 'Aucun'));

// Liste des origines autorisées
$allowedOrigins = [
    'http://localhost:8080',
    'http://localhost:8081', 
    'http://localhost:8082',
    'http://localhost:3000',
    'http://localhost:5173'
];

// Vérifier si l'origine est autorisée
$allowOrigin = '*';
if (in_array($origin, $allowedOrigins)) {
    $allowOrigin = $origin;
}

// Headers CORS ESSENTIELS
header("Access-Control-Allow-Origin: $allowOrigin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-User-Id, X-User-Role, Accept");
header("Access-Control-Expose-Headers: Content-Type, Authorization, X-User-Id");
header("Access-Control-Max-Age: 86400"); // Cache 24h

// IMPORTANT: Quand on utilise *, credentials doit être false
header("Access-Control-Allow-Credentials: false");

// Gestion OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    error_log("CORS - Réponse OPTIONS pour $origin");
    http_response_code(200);
    exit();
}

error_log("CORS - Headers envoyés pour $origin - Méthode: " . $_SERVER['REQUEST_METHOD']);
?>