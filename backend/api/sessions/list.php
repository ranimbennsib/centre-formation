<?php
// backend/api/sessions/list.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Sélectionner toutes les sessions non supprimées
    $stmt = $pdo->query("SELECT id, id_formation, id_formateur, date_debut, date_fin, salle, places_max, places_restantes, statut_formateur 
                         FROM session 
                         WHERE deleted_at IS NULL 
                         ORDER BY date_debut ASC");
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'sessions' => $sessions
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
