<?php
// backend/api/sessions/assign.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

error_log("=== 🔄 DÉBUT ASSIGN FORMATEUR ===");

// Admin uniquement
if (!isAdmin()) {
    error_log("❌ isAdmin() retourne FALSE - Accès refusé");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin uniquement']);
    exit;
}

error_log("✅ isAdmin() retourne TRUE - Accès autorisé");

$sessionId = $_GET['session_id'] ?? null;
error_log("🔍 Session ID reçu: " . $sessionId);

if (!$sessionId || !is_numeric($sessionId)) {
    error_log("❌ ID de session invalide");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de session invalide']);
    exit;
}

$sessionId = (int)$sessionId;
error_log("🔢 Session ID converti: " . $sessionId);

$db = new Database();
$pdo = $db->getConnection();

$input = file_get_contents('php://input');
error_log("📥 Données JSON reçues: " . $input);

$data = json_decode($input, true);

if (!$data) {
    error_log("❌ Données JSON invalides");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
    exit;
}

error_log("📋 Données décodées: " . json_encode($data, JSON_PRETTY_PRINT));

if (empty($data['id_formateur'])) {
    error_log("❌ id_formateur est requis mais vide");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID formateur requis']);
    exit;
}

$id_formateur = (int)$data['id_formateur'];
error_log("🔍 Formateur ID reçu: " . $id_formateur);

try {
    // Vérifier que la session existe
    error_log("🔍 Vérification session ID: " . $sessionId);
    $stmt = $pdo->prepare("SELECT s.*, f.titre as formation_titre 
                           FROM session s
                           JOIN formation f ON s.id_formation = f.id
                           WHERE s.id = ? AND s.deleted_at IS NULL");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        error_log("❌ Session non trouvée ID: " . $sessionId);
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Session non trouvée']);
        exit;
    }
    
    error_log("✅ Session trouvée: " . $session['formation_titre']);
    
    // Vérifier que le formateur existe
    error_log("🔍 Vérification formateur ID: " . $id_formateur);
    $stmt = $pdo->prepare("SELECT f.*, u.email, u.nom, u.prenom 
                           FROM formateur f 
                           JOIN utilisateur u ON f.id_utilisateur = u.id 
                           WHERE f.id = ? AND f.deleted_at IS NULL");
    $stmt->execute([$id_formateur]);
    $formateur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$formateur) {
        error_log("❌ Formateur non trouvé ID: " . $id_formateur);
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Formateur non trouvé']);
        exit;
    }
    
    error_log("✅ Formateur trouvé: " . $formateur['prenom'] . " " . $formateur['nom']);
    
    // Vérifier les conflits de dates
    $stmt = $pdo->prepare("SELECT COUNT(*) as count 
                           FROM session 
                           WHERE id_formateur = ? 
                           AND id != ?
                           AND deleted_at IS NULL
                           AND statut_formateur != 'ANNULEE'
                           AND ((date_debut BETWEEN ? AND ?) 
                           OR (date_fin BETWEEN ? AND ?)
                           OR (? BETWEEN date_debut AND date_fin))");
    $stmt->execute([
        $id_formateur, 
        $sessionId,
        $session['date_debut'], 
        $session['date_fin'],
        $session['date_debut'], 
        $session['date_fin'],
        $session['date_debut']
    ]);
    $conflict = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conflict['count'] > 0) {
        error_log("⚠️ Conflit de dates détecté pour le formateur");
        echo json_encode([
            'success' => false,
            'message' => 'Le formateur est déjà assigné à une autre session pendant cette période.',
            'warning' => true
        ]);
        exit;
    }
    
    // Si la session avait déjà un formateur, notifier l'ancien formateur
    /*
    if (!empty($session['id_formateur']) && $session['id_formateur'] != $id_formateur) {
        error_log("🔄 Changement de formateur détecté");
        error_log("   Ancien formateur: " . $session['id_formateur']);
        error_log("   Nouveau formateur: " . $id_formateur);
        
        $notificationOld = "INSERT INTO notifications (id_utilisateur, type, message, id_session, created_at)
                            VALUES ((SELECT id_utilisateur FROM formateur WHERE id = ?), 
                                    'SESSION_DESASSIGNEE',
                                    'Vous avez été désassigné d\'une session',
                                    ?, 
                                    NOW())";
        $stmt = $pdo->prepare($notificationOld);
        $stmt->execute([$session['id_formateur'], $sessionId]);
        error_log("📨 Notification envoyée à l\'ancien formateur");
    }
    */
    // Assigner le nouveau formateur
    $sql = "UPDATE session 
            SET id_formateur = ?, 
                statut_formateur = 'EN_ATTENTE', 
                updated_at = NOW() 
            WHERE id = ?";
    
    error_log("📝 Requête SQL: $sql");
    error_log("📝 Paramètres: [" . $id_formateur . ", " . $sessionId . "]");
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$id_formateur, $sessionId]);
    
    if ($success) {
        // Notifier le nouveau formateur
        /*
        $notificationNew = "INSERT INTO notifications (id_utilisateur, type, message, id_session, created_at)
                            VALUES (?, 'SESSION_ASSIGNEE', 
                                    'Une nouvelle session vous a été assignée. Veuillez l\'accepter ou la refuser.',
                                    ?, NOW())";
        $stmt = $pdo->prepare($notificationNew);
        $stmt->execute([$formateur['id_utilisateur'], $sessionId]);
        error_log("📨 Notification envoyée au nouveau formateur");
        */
        error_log("✅ Formateur assigné avec succès!");
        
        echo json_encode([
            'success' => true,
            'message' => 'Formateur assigné avec succès',
            'formateur' => [
                'id' => $formateur['id'],
                'nom' => $formateur['nom'],
                'prenom' => $formateur['prenom'],
                'email' => $formateur['email']
            ],
            'session' => [
                'id' => $sessionId,
                'formation_titre' => $session['formation_titre'],
                'date_debut' => $session['date_debut'],
                'date_fin' => $session['date_fin'],
                'salle' => $session['salle']
            ]
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        error_log("❌ Erreur SQL lors de l'assignation: " . json_encode($errorInfo));
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Erreur lors de l\'assignation',
            'error' => $errorInfo[2] ?? 'Erreur SQL inconnue'
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

error_log("=== ✅ FIN ASSIGN FORMATEUR ===");
?>