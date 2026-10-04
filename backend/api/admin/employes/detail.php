<?php
// backend/api/admin/employes/detail.php

// === INCLUSIONS ===
require_once '../../../includes/cors.php';  // Doit contenir TOUS les headers
require_once '../../../includes/auth-check.php';
require_once '../../../config/database.php';

// === VÉRIFICATION ADMIN ===
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé. Admin uniquement.']);
    exit;
}

// === VÉRIFICATION MÉTHODE ===
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
    
    // Requête pour récupérer l'employé
    $sql = "SELECT 
                u.id, u.email, u.nom, u.prenom, u.role, 
                u.date_inscription, u.deleted_at,
                e.poste, e.departement, e.date_embauche, e.cin
            FROM utilisateur u
            LEFT JOIN employe e ON u.id = e.id_utilisateur
            WHERE u.id = ? AND u.role = 'employe'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $employe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employe) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Employé non trouvé']);
        exit;
    }
    
    // Formatage des données
    $formattedData = [
        'id' => (int)$employe['id'],
        'nom' => $employe['nom'],
        'prenom' => $employe['prenom'],
        'cin' => $employe['cin'],
        'email' => $employe['email'],
        'role' => $employe['role'],
        'poste' => $employe['poste'],
        'departement' => $employe['departement'],
        'date_embauche' => $employe['date_embauche'],
        'date_inscription' => $employe['date_inscription'],
        'deleted_at' => $employe['deleted_at']
    ];
    
    // Réponse
    echo json_encode([
        'success' => true,
        'data' => $formattedData
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur',
        'error' => $e->getMessage()
    ]);
}
?>