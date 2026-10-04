<?php
// backend/api/sessions/detail.php - AVEC CORS COMPLET
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8081');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: X-User-Id, X-User-Role, Content-Type, Authorization');
header('Access-Control-Allow-Credentials: false');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log pour débogage
error_log("=== DÉTAIL SESSION ===");
error_log("ID reçu: " . ($_GET['id'] ?? 'NULL'));
error_log("Méthode: " . $_SERVER['REQUEST_METHOD']);
error_log("Headers: " . print_r(getallheaders(), true));

// Vérifier l'ID de la session
$sessionId = $_GET['id'] ?? null;

if (!$sessionId || !is_numeric($sessionId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'ID de session invalide',
        'debug' => ['id_reçu' => $sessionId]
    ]);
    exit;
}

$sessionId = intval($sessionId);

try {
    // Connexion à la base de données
    require_once '../../config/database.php';
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Requête principale SIMPLIFIÉE
    $sql = "SELECT 
                s.*,
                f.titre as formation_titre,
                f.description as formation_description,
                COALESCE(u_formateur.nom, 'Non assigné') as formateur_nom,
                COALESCE(u_formateur.prenom, '') as formateur_prenom,
                COALESCE(u_formateur.email, '') as formateur_email
            FROM session s
            JOIN formation f ON s.id_formation = f.id
            LEFT JOIN formateur form ON s.id_formateur = form.id
            LEFT JOIN utilisateur u_formateur ON form.id_utilisateur = u_formateur.id
            WHERE s.id = ?";
    
    $stmtSession = $pdo->prepare($sql);
    $stmtSession->execute([$sessionId]);
    $session = $stmtSession->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'message' => 'Session non trouvée',
            'session_id' => $sessionId
        ]);
        exit;
    }

    // Réponse finale
    echo json_encode([
        'success' => true,
        'session' => $session,
        'inscrits' => [] // Pour l'instant vide, on ajoutera plus tard
    ]);

} catch (PDOException $e) {
    error_log("❌ Erreur PDO: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("❌ Erreur générale: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur système',
        'error' => $e->getMessage()
    ]);
}
?>