<?php
// backend/api/admin/employes/update.php

// === GESTION CORS ===
header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
   http_response_code(200);
    exit();
}

// === INCLUSIONS ===
//require_once '../../../includes/cors.php';
require_once '../../../includes/auth-check.php';
require_once '../../../config/database.php';

// === VÉRIFICATION ADMIN ===
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé. Admin uniquement.']);
    exit;
}

// === VÉRIFICATION MÉTHODE ===
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

// === RÉCUPÉRATION DONNÉES ===
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'JSON invalide', 
        'error' => json_last_error_msg()
    ]);
    exit;
}

// === VALIDATION DES DONNÉES ===
$errors = [];

// Récupération des données
$nom = trim($data['nom'] ?? '');
$prenom = trim($data['prenom'] ?? '');
$cin = trim($data['cin'] ?? '');
$poste = trim($data['poste'] ?? '');
$departement = trim($data['departement'] ?? '');
$date_embauche = $data['date_embauche'] ?? null;
$nouveau_mot_de_passe = $data['nouveau_mot_de_passe'] ?? $data['mot_de_passe'] ?? '';

// Validation CIN
if (empty($cin)) {
    $errors[] = 'Le CIN est obligatoire';
} elseif (strlen($cin) > 20) {
    $errors[] = 'Le CIN ne doit pas dépasser 20 caractères';
}

// Validation nom
if (empty($nom)) {
    $errors[] = 'Le nom est obligatoire';
}

// Validation prénom
if (empty($prenom)) {
    $errors[] = 'Le prénom est obligatoire';
}

// Validation mot de passe (si fourni)
if (!empty($nouveau_mot_de_passe) && strlen($nouveau_mot_de_passe) < 6) {
    $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
}

// Si erreurs de validation
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Erreurs de validation', 
        'errors' => $errors
    ]);
    exit;
}

try {
    // === INITIALISATION BDD ===
    $db = new Database();
    $pdo = $db->getConnection();
    
    // 1. Vérifier si le CIN existe déjà pour un autre employé
    $stmtCheckCIN = $pdo->prepare("
        SELECT e.cin, u.id, u.nom, u.prenom 
        FROM employe e
        JOIN utilisateur u ON e.id_utilisateur = u.id
        WHERE e.cin = ? AND u.id != ? AND u.deleted_at IS NULL
    ");
    $stmtCheckCIN->execute([$cin, $id]);
    $existingCIN = $stmtCheckCIN->fetch(PDO::FETCH_ASSOC);
    
    if ($existingCIN) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Ce CIN est déjà utilisé par l'employé: " . 
                         $existingCIN['nom'] . " " . $existingCIN['prenom']
        ]);
        exit;
    }
    
    // 2. Vérifier si l'employé existe
    $stmtCheck = $pdo->prepare("
        SELECT u.id, u.email, e.cin 
        FROM utilisateur u
        JOIN employe e ON u.id = e.id_utilisateur
        WHERE u.id = ? AND u.deleted_at IS NULL
    ");
    $stmtCheck->execute([$id]);
    $employe = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$employe) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Employé non trouvé ou déjà supprimé']);
        exit;
    }
    
    // 3. Mettre à jour l'utilisateur
    $sqlUser = "UPDATE utilisateur SET nom = ?, prenom = ? WHERE id = ?";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$nom, $prenom, $id]);
    
    // 4. Mettre à jour l'employé
    $sqlEmploye = "UPDATE employe SET 
                    cin = ?, 
                    poste = ?, 
                    departement = ?, 
                    date_embauche = ? 
                   WHERE id_utilisateur = ?";
    $stmtEmploye = $pdo->prepare($sqlEmploye);
    $stmtEmploye->execute([
        $cin,
        $poste ?: null,
        $departement ?: null,
        $date_embauche ?: null,
        $id
    ]);
    
    // 5. Mettre à jour le mot de passe si fourni
    if (!empty($nouveau_mot_de_passe)) {
        $hashedPassword = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
        $sqlPassword = "UPDATE utilisateur SET mot_de_passe_hash = ? WHERE id = ?";
        $stmtPassword = $pdo->prepare($sqlPassword);
        $stmtPassword->execute([$hashedPassword, $id]);
    }
    
    // 6. Récupérer l'employé mis à jour
    $sqlGetEmploye = "SELECT 
                        u.id, 
                        u.email, 
                        u.nom, 
                        u.prenom, 
                        u.role, 
                        u.date_inscription,
                        u.deleted_at,
                        e.poste, 
                        e.departement, 
                        e.date_embauche, 
                        e.cin
                      FROM utilisateur u
                      LEFT JOIN employe e ON u.id = e.id_utilisateur
                      WHERE u.id = ?";
    
    $stmtGet = $pdo->prepare($sqlGetEmploye);
    $stmtGet->execute([$id]);
    $employeMaj = $stmtGet->fetch(PDO::FETCH_ASSOC);
    
    // 7. Réponse de succès
    echo json_encode([
        'success' => true,
        'message' => 'Employé mis à jour avec succès',
        'data' => [
            'id' => (int)$employeMaj['id'],
            'nom' => $employeMaj['nom'],
            'prenom' => $employeMaj['prenom'],
            'cin' => $employeMaj['cin'],
            'email' => $employeMaj['email'],
            'role' => $employeMaj['role'],
            'poste' => $employeMaj['poste'],
            'departement' => $employeMaj['departement'],
            'date_embauche' => $employeMaj['date_embauche'],
            'date_inscription' => $employeMaj['date_inscription'],
            'deleted_at' => $employeMaj['deleted_at']
        ]
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
        'message' => 'Erreur lors de la mise à jour',
        'error' => $e->getMessage()
    ]);
}
?>