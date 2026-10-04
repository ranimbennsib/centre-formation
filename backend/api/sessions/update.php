<?php
// backend/api/sessions/update.php
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

$sessionId = $_GET['id'] ?? null;
if (!$sessionId || !is_numeric($sessionId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de session invalide']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Vérifier que la session existe
    $stmt = $pdo->prepare("SELECT * FROM session WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$sessionId]);
    $currentSession = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentSession) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Session non trouvée']);
        exit;
    }
    
    // PRINCIPE : L'admin NE PEUT PAS modifier le statut manuellement
    // Le statut ne peut être changé que :
    // 1. Par le formateur (acceptation/refus)
    // 2. Par réassignation (refusé -> en attente)
    
    // Si l'admin change le formateur ET que le statut est "REFUSEE"
    // Alors le statut repasse automatiquement à "EN_ATTENTE"
    $shouldResetStatus = false;
    
    if (isset($data['id_formateur']) && $data['id_formateur'] != $currentSession['id_formateur']) {
        if ($currentSession['statut_formateur'] === 'REFUSEE') {
            $shouldResetStatus = true;
        }
    }
    
    // Préparer les mises à jour
    $updates = [];
    $params = [];
    
    // Champs que l'admin peut modifier (sans statut_formateur ni raison_refus_formateur)
    $adminEditableFields = ['date_debut', 'date_fin', 'salle', 'description', 'id_formateur'];
    
    foreach ($adminEditableFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    // Gestion spéciale pour places_max
    if (isset($data['places_max'])) {
        $newPlacesMax = (int)$data['places_max'];
        $currentPlacesMax = (int)$currentSession['places_max'];
        $currentPlacesRestantes = (int)$currentSession['places_restantes'];
        
        $difference = $newPlacesMax - $currentPlacesMax;
        $newPlacesRestantes = $currentPlacesRestantes + $difference;
        $newPlacesRestantes = max(0, $newPlacesRestantes);
        
        $updates[] = "places_max = ?";
        $params[] = $newPlacesMax;
        
        $updates[] = "places_restantes = ?";
        $params[] = $newPlacesRestantes;
    }
    
    // Si réassignation après refus, réinitialiser le statut
    if ($shouldResetStatus) {
        $updates[] = "statut_formateur = 'EN_ATTENTE'";
        $updates[] = "raison_refus_formateur = NULL"; // Supprimer la raison de refus
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'Aucune donnée à mettre à jour']);
        exit;
    }
    
    // Exécuter la mise à jour (UPDATE, pas INSERT!)
    $params[] = $sessionId;
    $sql = "UPDATE session SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success) {
        // Récupérer la session mise à jour
        $stmt = $pdo->prepare("SELECT s.*, 
                                      f.titre as formation_titre,
                                      u.nom as formateur_nom,
                                      u.prenom as formateur_prenom
                               FROM session s
                               JOIN formation f ON s.id_formation = f.id
                               LEFT JOIN formateur fr ON s.id_formateur = fr.id
                               LEFT JOIN utilisateur u ON fr.id_utilisateur = u.id
                               WHERE s.id = ?");
        $stmt->execute([$sessionId]);
        $updatedSession = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Session mise à jour avec succès',
            'session' => $updatedSession
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
}
?>