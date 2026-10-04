<?php
// backend/api/sessions/response.php - VERSION AMÉLIORÉE
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Créer un log pour debug
$logFile = dirname(__FILE__) . '/../../response_debug.log';
file_put_contents($logFile, "\n=== " . date('Y-m-d H:i:s') . " ===", FILE_APPEND);

// Log les headers
$headers = getallheaders();
file_put_contents($logFile, "\nHeaders: " . json_encode($headers), FILE_APPEND);
file_put_contents($logFile, "\nMethod: " . $_SERVER['REQUEST_METHOD'], FILE_APPEND);
file_put_contents($logFile, "\nGET: " . json_encode($_GET), FILE_APPEND);

require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Formateur uniquement
if (!isFormateur()) {
    http_response_code(403);
    $response = ['success' => false, 'message' => 'Accès réservé aux formateurs'];
    file_put_contents($logFile, "\nErreur 403: " . json_encode($response), FILE_APPEND);
    echo json_encode($response);
    exit;
}

$sessionId = $_GET['session_id'] ?? null;
file_put_contents($logFile, "\nSession ID: " . $sessionId, FILE_APPEND);

if (!$sessionId || !is_numeric($sessionId)) {
    http_response_code(400);
    $response = ['success' => false, 'message' => 'ID de session invalide'];
    file_put_contents($logFile, "\nErreur 400: " . json_encode($response), FILE_APPEND);
    echo json_encode($response);
    exit;
}

// Lire les données POST
$input = file_get_contents('php://input');
file_put_contents($logFile, "\nRaw input: " . $input, FILE_APPEND);
$data = json_decode($input, true);

if (!$data || !isset($data['reponse']) || !in_array($data['reponse'], ['ACCEPTEE', 'REFUSEE'])) {
    http_response_code(400);
    $response = [
        'success' => false, 
        'message' => 'Réponse invalide. Utiliser "ACCEPTEE" ou "REFUSEE"',
        'data_received' => $data
    ];
    file_put_contents($logFile, "\nErreur 400 - données invalides: " . json_encode($response), FILE_APPEND);
    echo json_encode($response);
    exit;
}

file_put_contents($logFile, "\nDonnées valides: " . json_encode($data), FILE_APPEND);

$db = new Database();
$pdo = $db->getConnection();

try {
    $userId = getCurrentUserId();
    file_put_contents($logFile, "\nUser ID: " . $userId, FILE_APPEND);
    
    // 1. Récupérer l'ID du formateur connecté
    $stmtFormateur = $pdo->prepare("SELECT id FROM formateur WHERE id_utilisateur = ?");
    $stmtFormateur->execute([$userId]);
    $formateur = $stmtFormateur->fetch(PDO::FETCH_ASSOC);
    
    if (!$formateur) {
        http_response_code(404);
        $response = ['success' => false, 'message' => 'Formateur non trouvé'];
        file_put_contents($logFile, "\nErreur 404 - formateur non trouvé: " . json_encode($response), FILE_APPEND);
        echo json_encode($response);
        exit;
    }
    
    $formateurId = $formateur['id'];
    file_put_contents($logFile, "\nFormateur ID: " . $formateurId, FILE_APPEND);
    
    // 2. Vérifier que la session existe et est assignée à ce formateur
    $stmt = $pdo->prepare("SELECT s.*, f.titre as formation_titre
                           FROM session s
                           JOIN formation f ON s.id_formation = f.id
                           WHERE s.id = ? 
                           AND (s.id_formateur = ? OR s.id_formateur IS NULL)
                           AND s.statut_formateur = 'EN_ATTENTE'
                           AND s.deleted_at IS NULL");
    $stmt->execute([$sessionId, $formateurId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        http_response_code(404);
        $response = [
            'success' => false, 
            'message' => 'Session non trouvée, non assignée à vous, ou déjà traitée',
            'debug' => ['session_id' => $sessionId, 'formateur_id' => $formateurId]
        ];
        file_put_contents($logFile, "\nErreur 404 - session non trouvée: " . json_encode($response), FILE_APPEND);
        echo json_encode($response);
        exit;
    }
    
    file_put_contents($logFile, "\nSession trouvée: " . json_encode($session), FILE_APPEND);
    
    // 3. Mettre à jour le statut
    $sql = "UPDATE session 
            SET statut_formateur = ?, 
                updated_at = NOW()";
    
    $params = [$data['reponse']];
    
    // Si la session n'avait pas de formateur, l'assigner
    if (!$session['id_formateur']) {
        $sql .= ", id_formateur = ?";
        $params[] = $formateurId;
    }
    
    // Stocker le commentaire
    if (!empty($data['commentaire'])) {
        $sql .= ", raison_refus_formateur = ?";
        $params[] = $data['commentaire'];
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $sessionId;
    
    file_put_contents($logFile, "\nSQL: " . $sql, FILE_APPEND);
    file_put_contents($logFile, "\nParams: " . json_encode($params), FILE_APPEND);
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success) {
        $rowsUpdated = $stmt->rowCount();
        file_put_contents($logFile, "\nRows updated: " . $rowsUpdated, FILE_APPEND);
        
        $response = [
            'success' => true,
            'message' => 'Réponse enregistrée avec succès',
            'reponse' => $data['reponse'],
            'rows_updated' => $rowsUpdated,
            'session' => [
                'id' => $sessionId,
                'formation' => $session['formation_titre'],
                'date_debut' => $session['date_debut'],
                'statut_formateur' => $data['reponse']
            ]
        ];
        
        file_put_contents($logFile, "\nSuccès: " . json_encode($response), FILE_APPEND);
        echo json_encode($response);
        
    } else {
        $errorInfo = $stmt->errorInfo();
        http_response_code(500);
        $response = [
            'success' => false, 
            'message' => 'Erreur lors de l\'enregistrement',
            'error_info' => $errorInfo
        ];
        file_put_contents($logFile, "\nErreur 500: " . json_encode($response), FILE_APPEND);
        echo json_encode($response);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ];
    file_put_contents($logFile, "\nErreur PDO: " . json_encode($response), FILE_APPEND);
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Erreur générale',
        'error' => $e->getMessage()
    ];
    file_put_contents($logFile, "\nErreur générale: " . json_encode($response), FILE_APPEND);
    echo json_encode($response);
}

file_put_contents($logFile, "\n=== FIN ===\n", FILE_APPEND);
?>