<?php
// backend/api/sessions/create.php (version simplifiée)
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Admin uniquement
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin uniquement']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

// Validation
$required = ['id_formation', 'id_formateur', 'date_debut', 'date_fin', 'salle', 'places_max'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Champ '$field' manquant"]);
        exit;
    }
}

try {
    // Vérifier que la formation existe
    $stmt = $pdo->prepare("SELECT id FROM formation WHERE id = ?");
    $stmt->execute([$data['id_formation']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Formation non trouvée']);
        exit;
    }
    
    // Vérifier que le formateur existe
    $stmt = $pdo->prepare("SELECT id FROM formateur WHERE id = ?");
    $stmt->execute([$data['id_formateur']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Formateur non trouvé']);
        exit;
    }
    
    // Créer la session
    $sql = "INSERT INTO session 
            (id_formation, id_formateur, date_debut, date_fin, salle, places_max, 
             places_restantes, statut_formateur, description, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'EN_ATTENTE', ?, NOW())";
    
    $places_restantes = $data['places_max'];
    $description = $data['description'] ?? '';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['id_formation'],
        $data['id_formateur'],
        $data['date_debut'],
        $data['date_fin'],
        $data['salle'],
        $data['places_max'],
        $places_restantes,
        $description
    ]);
    
    $sessionId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Session créée avec succès',
        'session_id' => $sessionId
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
}
?>