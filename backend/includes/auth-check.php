<?php
// backend/includes/auth-check.php

// Démarrer la session seulement si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie l'authentification via headers ou session
 */
function checkAuth() {
    // Vérifier d'abord les headers (pour API)
    if (isset($_SERVER['HTTP_X_USER_ID']) && isset($_SERVER['HTTP_X_USER_ROLE'])) {
        return [
            'id' => $_SERVER['HTTP_X_USER_ID'],
            'role' => $_SERVER['HTTP_X_USER_ROLE']
        ];
    }
    
    // Sinon vérifier la session (pour requêtes traditionnelles)
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        return [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['role']
        ];
    }
    
    return null;
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function requireAuth() {
    $user = checkAuth();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Non authentifié. Veuillez vous connecter.'
        ]);
        exit;
    }
    
    return $user;
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 */
function requireRole($requiredRole) {
    $user = requireAuth(); // Cette fonction arrête déjà si non authentifié
    
    // Convertir en majuscules pour la comparaison
    $userRole = strtoupper($user['role']);
    $requiredRole = strtoupper($requiredRole);
    
    if ($userRole !== $requiredRole) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => "Accès non autorisé. Rôle requis: $requiredRole (vous êtes: $userRole)"
        ]);
        exit;
    }
    
    return $user;
}

/**
 * Vérifie si l'utilisateur est admin
 */
function requireAdmin() {
    return requireRole('ADMIN');
}

/**
 * Vérifie si l'utilisateur est formateur
 */
function requireFormateur() {
    return requireRole('FORMATEUR');
}

/**
 * Vérifie si l'utilisateur est employé
 */
function requireEmploye() {
    return requireRole('EMPLOYE');
}

/**
 * Récupère l'ID de l'utilisateur connecté
 */
function getCurrentUserId() {
    $user = checkAuth();
    return $user ? $user['id'] : null;
}

/**
 * Récupère le rôle de l'utilisateur connecté
 */
function getCurrentUserRole() {
    $user = checkAuth();
    return $user ? $user['role'] : null;
}

/**
 * Vérifie si l'utilisateur est admin (sans arrêter l'exécution)
 */
function isAdmin() {
    $user = checkAuth();
    if (!$user) return false;
    
    return strtoupper($user['role']) === 'ADMIN';
}

/**
 * Vérifie si l'utilisateur est formateur
 */
function isFormateur() {
    $user = checkAuth();
    if (!$user) return false;
    
    return strtoupper($user['role']) === 'FORMATEUR';
}

/**
 * Vérifie si l'utilisateur est employé
 */
function isEmploye() {
    if (!isset($_SERVER['HTTP_X_USER_ROLE'])) {
        return false;
    }
    return strtolower($_SERVER['HTTP_X_USER_ROLE']) === 'employe';
}

/**
 * Middleware pour vérifier l'authentification
 */
function authMiddleware($requiredRole = null) {
    return function() use ($requiredRole) {
        if ($requiredRole) {
            requireRole($requiredRole);
        } else {
            requireAuth();
        }
    };
}

// Ajouter ce header pour voir les headers reçus (débogage)
// header('X-Debug-Headers: ' . json_encode(getallheaders()));
?>