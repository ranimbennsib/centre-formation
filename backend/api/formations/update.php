<?php
// backend/api/formations/update.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// ============ DEBUG ============
error_log("=== DEBUG UPDATE FORMATION ===");
error_log("Headers reçus: " . json_encode(getallheaders()));
error_log("X-User-Role: " . ($_SERVER['HTTP_X_USER_ROLE'] ?? 'NOT SET'));
error_log("GET id: " . ($_GET['id'] ?? 'NOT SET'));
// ============ FIN DEBUG ============

// Seul l'admin peut modifier
if (!isAdmin()) {
    error_log("❌ isAdmin() retourne FALSE - Accès refusé");
    error_log("Headers: " . json_encode([
        'X-User-Role' => $_SERVER['HTTP_X_USER_ROLE'] ?? 'none',
        'X-User-Id' => $_SERVER['HTTP_X_USER_ID'] ?? 'none'
    ]));
    
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Accès non autorisé',
        'debug' => [
            'headers_received' => getallheaders(),
            'expected_role' => 'ADMIN'
        ]
    ]);
    exit;
}

error_log("✅ isAdmin() retourne TRUE - Accès autorisé");

$db = new Database();
$pdo = $db->getConnection();

// Récupérer l'ID
$formationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($formationId === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID formation requis']);
    exit;
}

// Récupérer les données JSON
$input = file_get_contents('php://input');
error_log("📥 Données reçues: " . $input);
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

error_log("📋 Données après traitement: " . json_encode($data));

// Vérifier que la formation existe
$stmt = $pdo->prepare("SELECT id FROM formation WHERE id = ?");
$stmt->execute([$formationId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Formation non trouvée']);
    exit;
}

try {
    // Champs autorisés à être modifiés
    $allowedFields = [
        'titre' => 'string',
        'description' => 'string', 
        'duree_heures' => 'int',
        'competences_visees' => 'string',
        'prix' => 'float',
        'est_actif' => 'bool'
    ];
    
    // Construire la requête dynamiquement
    $updates = [];
    $values = [];
    
    foreach ($allowedFields as $field => $type) {
        if (isset($data[$field])) {
            // Validation basique selon le type
            $value = $data[$field];
            
            switch ($type) {
                case 'int':
                    $value = (int)$value;
                    break;
                case 'float':
                    $value = (float)$value;
                    break;
                case 'bool':
                    $value = (bool)$value;
                    break;
                default:
                    $value = trim($value);
            }
            
            $updates[] = "$field = ?";
            $values[] = $value;
            
            error_log("✅ Champ à mettre à jour: $field = " . json_encode($value));
        }
    }
    
    // Si aucun champ à mettre à jour
    if (empty($updates)) {
        error_log("❌ Aucun champ à mettre à jour");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aucune donnée à mettre à jour']);
        exit;
    }
    
    // Ajouter l'ID à la fin pour le WHERE
    $values[] = $formationId;
    
    // Exécuter la mise à jour
    $sql = "UPDATE formation SET " . implode(', ', $updates) . " WHERE id = ?";
    error_log("📝 Requête SQL: $sql");
    error_log("📝 Valeurs: " . json_encode($values));
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($values);
    
    if ($success) {
        // Récupérer la formation mise à jour
        $stmt = $pdo->prepare("SELECT * FROM formation WHERE id = ?");
        $stmt->execute([$formationId]);
        $formation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("✅ Formation mise à jour avec succès: " . json_encode($formation));
        
        echo json_encode([
            'success' => true,
            'message' => 'Formation mise à jour avec succès',
            'data' => $formation
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("❌ Erreur SQL: " . json_encode($errorInfo));
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour',
            'error' => $errorInfo[2] ?? 'Erreur inconnue'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("❌ Exception PDO: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour',
        'error' => $e->getMessage()
    ]);
}
?>