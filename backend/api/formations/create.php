<?php
// backend/api/formations/create.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// ============ DEBUG ============
error_log("=== DEBUG CREATE FORMATION ===");
error_log("Headers reçus: " . json_encode(getallheaders()));
error_log("X-User-Role: " . ($_SERVER['HTTP_X_USER_ROLE'] ?? 'NOT SET'));
error_log("X-User-Id: " . ($_SERVER['HTTP_X_USER_ID'] ?? 'NOT SET'));
// ============ FIN DEBUG ============

// Seul l'admin peut créer des formations - CORRIGEZ CETTE LIGNE
if (!isAdmin()) {
    error_log("❌ isAdmin() retourne FALSE - Accès refusé");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

error_log("✅ isAdmin() retourne TRUE - Accès autorisé");

$db = new Database();
$pdo = $db->getConnection();

// Récupérer les données JSON
$input = file_get_contents('php://input');
error_log("📥 Données JSON reçues: " . $input);
$data = json_decode($input, true);

// Vérifier si on a reçu des données
if (!$data) {
    error_log("⚠️ Données JSON vides, tentative avec POST");
    $data = $_POST; // Fallback pour form-data
    error_log("📥 Données POST: " . json_encode($_POST));
}

// Validation des champs obligatoires
$errors = [];
if (empty($data['titre'])) $errors[] = 'Le titre est requis';
if (empty($data['description'])) $errors[] = 'La description est requise';
if (empty($data['duree_heures'])) $errors[] = 'La durée est requise';

if (!empty($errors)) {
    error_log("❌ Erreurs de validation: " . json_encode($errors));
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreurs de validation',
        'errors' => $errors
    ]);
    exit;
}

error_log("📋 Données validées: " . json_encode($data));

try {
    // Préparation de la requête
    $stmt = $pdo->prepare("
        INSERT INTO formation (
            titre, 
            description, 
            duree_heures, 
            competences_visees, 
            prix, 
            est_actif
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $titre = trim($data['titre']);
    $description = trim($data['description']);
    $duree = (int)$data['duree_heures'];
    $competences = trim($data['competences_visees'] ?? '');
    $prix = (float)($data['prix'] ?? 0);
    $est_actif = (bool)($data['est_actif'] ?? true);
    
    error_log("📝 Valeurs à insérer:");
    error_log("  - titre: $titre");
    error_log("  - description: $description");
    error_log("  - duree_heures: $duree");
    error_log("  - competences_visees: $competences");
    error_log("  - prix: $prix");
    error_log("  - est_actif: " . ($est_actif ? 'true' : 'false'));
    
    // Exécution avec les valeurs
    $success = $stmt->execute([
        $titre,
        $description,
        $duree,
        $competences,
        $prix,
        $est_actif ? 1 : 0
    ]);
    
    if ($success) {
        // Récupérer la formation créée
        $formationId = $pdo->lastInsertId();
        error_log("✅ Formation créée avec ID: $formationId");
        
        $stmt = $pdo->prepare("SELECT * FROM formation WHERE id = ?");
        $stmt->execute([$formationId]);
        $formation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("📊 Formation récupérée: " . json_encode($formation));
        
        echo json_encode([
            'success' => true,
            'message' => 'Formation créée avec succès',
            'data' => $formation
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("❌ Erreur SQL: " . json_encode($errorInfo));
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création',
            'error' => $errorInfo[2] ?? 'Erreur inconnue'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("❌ Exception PDO: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
}
?>