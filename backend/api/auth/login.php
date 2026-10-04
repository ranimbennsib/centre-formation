<?php
// backend/api/auth/login.php - VERSION DEBUG
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

// Log pour debug
error_log("=== DÉBUT REQUÊTE LOGIN ===");
error_log("Méthode: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Non défini'));

// Répondre aux pré-vols CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    error_log("Réponse OPTIONS CORS");
    exit();
}

// Test: Si GET, montrer des infos de debug
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    error_log("Requête GET reçue - DEBUG MODE");
    echo json_encode([
        'success' => false,
        'message' => 'Méthode GET non autorisée. Utilisez POST.',
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'php_input' => file_get_contents('php://input'),
            'post_data' => $_POST,
            'server' => [
                'HTTP_ORIGIN' => $_SERVER['HTTP_ORIGIN'] ?? 'Non défini',
                'HTTP_CONTENT_TYPE' => $_SERVER['HTTP_CONTENT_TYPE'] ?? 'Non défini'
            ]
        ]
    ]);
    exit();
}

// Connexion BDD
$host = 'localhost';
$dbname = 'centre_formation';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    error_log("Erreur BDD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données'
    ]);
    exit;
}

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Lire les données
    $json = file_get_contents('php://input');
    error_log("php://input: " . $json);
    
    $data = json_decode($json, true);
    
    // Fallback pour form-data
    if (!$data && isset($_POST['email'])) {
        error_log("Utilisation de POST standard");
        $data = [
            'email' => $_POST['email'],
            'password' => $_POST['password']
        ];
    }
    
    error_log("Données reçues: " . print_r($data, true));
    
    if (empty($data) || empty($data['email']) || empty($data['password'])) {
        error_log("Données manquantes");
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email et mot de passe requis',
            'debug' => $data
        ]);
        exit;
    }
    
    $email = trim($data['email']);
    $password = $data['password'];
    
    try {
        // MODIFICATION ICI : Ajouter "AND deleted_at IS NULL"
        $stmt = $pdo->prepare("SELECT id, email, nom, prenom, role, mot_de_passe_hash, deleted_at 
                               FROM utilisateur 
                               WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            error_log("Utilisateur trouvé: " . $user['email']);
            
            // Vérifier le mot de passe
            if (password_verify($password, $user['mot_de_passe_hash'])) {
                // Vérifier aussi que deleted_at est bien NULL (double vérification)
                if ($user['deleted_at'] !== null) {
                    error_log("❌ Utilisateur supprimé (deleted_at: " . $user['deleted_at'] . ")");
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Compte désactivé. Veuillez contacter l\'administrateur.'
                    ]);
                    exit;
                }
                
                // Créer session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Retirer le hash et deleted_at des données retournées
                unset($user['mot_de_passe_hash']);
                unset($user['deleted_at']);
                
                error_log("✅ Connexion réussie pour: " . $email);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => $user,
                    'session_id' => session_id()
                ]);
            } else {
                error_log("❌ Mot de passe incorrect");
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ]);
            }
        } else {
            // Vérifier si l'utilisateur existe mais est supprimé
            $stmtDeleted = $pdo->prepare("SELECT email, deleted_at FROM utilisateur WHERE email = ? AND deleted_at IS NOT NULL");
            $stmtDeleted->execute([$email]);
            $deletedUser = $stmtDeleted->fetch();
            
            if ($deletedUser) {
                error_log("❌ Utilisateur supprimé (deleted_at: " . $deletedUser['deleted_at'] . ")");
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Compte désactivé. Veuillez contacter l\'administrateur.',
                    'debug' => 'Compte supprimé le: ' . $deletedUser['deleted_at']
                ]);
            } else {
                error_log("❌ Utilisateur non trouvé");
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ]);
            }
        }
        
    } catch (PDOException $e) {
        error_log("Erreur PDO: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur serveur',
            'error' => $e->getMessage()
        ]);
    }
    
    error_log("=== FIN REQUÊTE LOGIN ===\n");
    
} else {
    // Ce ne devrait pas arriver mais au cas où
    error_log("Méthode non supportée: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez POST.'
    ]);
}
?>