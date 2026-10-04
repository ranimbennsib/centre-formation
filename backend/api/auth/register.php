<?php
// backend/api/admin/employes/register.php

// === IMPORTANT : GESTION CORS AVEC OPTIONS ===
// Répondre aux requêtes OPTIONS (preflight) AVANT tout
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:8081");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role, X-Requested-With, Accept");
    header("Access-Control-Allow-Credentials: false");
    header("Access-Control-Max-Age: 86400");
    header("Content-Length: 0");
    http_response_code(200);
    exit();
}

// Headers CORS pour les autres requêtes
header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-User-Role, X-Requested-With, Accept");
header("Access-Control-Allow-Credentials: false");
header("Access-Control-Max-Age: 86400");

// === INCLUSIONS ===
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

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

// === INITIALISATION ===
$db = new Database();
$pdo = $db->getConnection();

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

$nom = trim($data['nom'] ?? '');
$prenom = trim($data['prenom'] ?? '');
$cin = trim($data['cin'] ?? '');
$email = trim($data['email'] ?? '');
$mot_de_passe = $data['mot_de_passe'] ?? '';
$poste = trim($data['poste'] ?? '');
$departement = trim($data['departement'] ?? '');
$date_embauche = $data['date_embauche'] ?? date('Y-m-d');

// Validation nom
if (empty($nom)) {
    $errors[] = 'Le nom est obligatoire';
} elseif (strlen($nom) > 50) {
    $errors[] = 'Le nom ne doit pas dépasser 50 caractères';
}

// Validation prénom
if (empty($prenom)) {
    $errors[] = 'Le prénom est obligatoire';
} elseif (strlen($prenom) > 50) {
    $errors[] = 'Le prénom ne doit pas dépasser 50 caractères';
}

// Validation CIN
if (empty($cin)) {
    $errors[] = 'Le CIN est obligatoire';
} elseif (strlen($cin) > 20) {
    $errors[] = 'Le CIN ne doit pas dépasser 20 caractères';
}

// Validation email
if (empty($email)) {
    $errors[] = 'L\'email est obligatoire';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format d\'email invalide';
} elseif (strlen($email) > 100) {
    $errors[] = 'L\'email ne doit pas dépasser 100 caractères';
}

// Validation mot de passe
if (empty($mot_de_passe)) {
    $errors[] = 'Le mot de passe est obligatoire';
} elseif (strlen($mot_de_passe) < 6) {
    $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
} elseif (strlen($mot_de_passe) > 255) {
    $errors[] = 'Le mot de passe est trop long';
}

// Validation date d'embauche
if (!empty($date_embauche)) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $date_embauche);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date_embauche) {
        $errors[] = 'Format de date invalide (doit être YYYY-MM-DD)';
    } elseif ($dateObj > new DateTime('today')) {
        $errors[] = 'La date d\'embauche ne peut pas être dans le futur';
    }
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
    // === DÉBUT TRANSACTION ===
    $pdo->beginTransaction();
    
    // 1. Vérifier si l'email existe déjà
    $stmtCheckEmail = $pdo->prepare("SELECT id, deleted_at FROM utilisateur WHERE email = ?");
    $stmtCheckEmail->execute([$email]);
    $existingEmail = $stmtCheckEmail->fetch(PDO::FETCH_ASSOC);
    
    if ($existingEmail) {
        if ($existingEmail['deleted_at'] === null) {
            throw new Exception('Cet email est déjà utilisé par un compte actif');
        } else {
            throw new Exception('Cet email appartient à un compte supprimé (soft delete)');
        }
    }
    
    // 2. Vérifier si le CIN existe déjà dans la table employe
    $stmtCheckCIN = $pdo->prepare("SELECT id FROM employe WHERE cin = ?");
    $stmtCheckCIN->execute([$cin]);
    $existingCIN = $stmtCheckCIN->fetch(PDO::FETCH_ASSOC);
    
    if ($existingCIN) {
        throw new Exception('Ce numéro de CIN est déjà utilisé par un autre employé');
    }
    
    // 3. Hachage du mot de passe
    $hashedPassword = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    // 4. Créer l'utilisateur dans la table utilisateur
    $sqlUser = "INSERT INTO utilisateur (email, mot_de_passe_hash, nom, prenom, role, date_inscription) 
                VALUES (?, ?, ?, ?, 'EMPLOYE', NOW())";
    
    $stmtUser = $pdo->prepare($sqlUser);
    $successUser = $stmtUser->execute([$email, $hashedPassword, $nom, $prenom]);
    
    if (!$successUser) {
        throw new Exception('Erreur lors de la création de l\'utilisateur');
    }
    
    $userId = $pdo->lastInsertId();
    
    // 5. Créer l'employé dans la table employe avec le CIN
    $sqlEmploye = "INSERT INTO employe (id_utilisateur, poste, departement, date_embauche, cin) 
                   VALUES (?, ?, ?, ?, ?)";
    
    $stmtEmploye = $pdo->prepare($sqlEmploye);
    $successEmploye = $stmtEmploye->execute([
        $userId, 
        $poste ?: null, 
        $departement ?: null, 
        $date_embauche,
        $cin
    ]);
    
    if (!$successEmploye) {
        throw new Exception('Erreur lors de la création de l\'employé');
    }
    
    // 6. Validation de la transaction
    $pdo->commit();
    
    // 7. Récupérer l'employé créé
    $sqlGetEmploye = "SELECT 
                        u.id, u.email, u.nom, u.prenom, u.role, u.date_inscription,
                        e.poste, e.departement, e.date_embauche, e.cin
                      FROM utilisateur u
                      LEFT JOIN employe e ON u.id = e.id_utilisateur
                      WHERE u.id = ?";
    
    $stmtGet = $pdo->prepare($sqlGetEmploye);
    $stmtGet->execute([$userId]);
    $employeCree = $stmtGet->fetch(PDO::FETCH_ASSOC);
    
    // 8. Réponse de succès
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Employé créé avec succès',
        'data' => [
            'id' => $employeCree['id'],
            'nom' => $employeCree['nom'],
            'prenom' => $employeCree['prenom'],
            'cin' => $employeCree['cin'],
            'email' => $employeCree['email'],
            'role' => $employeCree['role'],
            'poste' => $employeCree['poste'],
            'departement' => $employeCree['departement'],
            'date_embauche' => $employeCree['date_embauche'],
            'date_inscription' => $employeCree['date_inscription'],
            'identifiants' => [
                'email' => $employeCree['email'],
                'password_set' => true
            ]
        ]
    ]);
    
} catch (PDOException $e) {
    // Rollback en cas d'erreur SQL
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Gestion des erreurs SQL spécifiques
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
    
    if (strpos($errorMessage, 'Duplicate entry') !== false) {
        if (strpos($errorMessage, 'email') !== false) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Cet email est déjà utilisé',
                'error' => 'EMAIL_DUPLICATE'
            ]);
        } elseif (strpos($errorMessage, 'cin') !== false) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Ce numéro de CIN est déjà utilisé',
                'error' => 'CIN_DUPLICATE'
            ]);
        } else {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Doublon détecté dans la base de données',
                'error' => $errorMessage
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur base de données',
            'error' => $errorMessage,
            'code' => $errorCode
        ]);
    }
    
} catch (Exception $e) {
    // Rollback en cas d'autre erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la création de l\'employé',
        'error' => $e->getMessage()
    ]);
}
?>