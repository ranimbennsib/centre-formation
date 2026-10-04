<?php
// backend/api/inscriptions/employe.php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Vérifier que c'est bien un employé
if (!isEmploye()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux employés']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    $userId = getCurrentUserId();
    
    // Récupérer l'ID de l'employé
    $stmtEmp = $pdo->prepare("SELECT id FROM employe WHERE id_utilisateur = ?");
    $stmtEmp->execute([$userId]);
    $employe = $stmtEmp->fetch(PDO::FETCH_ASSOC);
    
    if (!$employe) {
        echo json_encode(['success' => false, 'message' => 'Employé non trouvé']);
        exit;
    }
    
    $idEmploye = $employe['id'];
    
    // Requête de base
    $sql = "
        SELECT 
            i.*,
            s.date_debut,
            s.date_fin,
            s.salle,
            s.places_max,
            s.statut_formateur,
            f.titre AS formation_titre,
            f.description AS formation_description,
            fo.id AS formateur_id,
            uf.nom AS formateur_nom,
            uf.prenom AS formateur_prenom,
            uf.email AS formateur_email
        FROM inscription i
        JOIN session s ON i.id_session = s.id
        JOIN formation f ON s.id_formation = f.id
        LEFT JOIN formateur fo ON s.id_formateur = fo.id
        LEFT JOIN utilisateur uf ON fo.id_utilisateur = uf.id
        WHERE i.id_employe = ?
    ";
    
    $params = [$idEmploye];
    
    // Filtre par statut
    if (!empty($_GET['statut'])) {
        $sql .= " AND i.statut = ?";
        $params[] = $_GET['statut'];
    }
    
    // Trier par date d'inscription (plus récent d'abord)
    $sql .= " ORDER BY i.date_inscription DESC";
    
    // Exécuter la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $inscriptions,
        'count' => count($inscriptions)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des inscriptions',
        'error' => $e->getMessage()
    ]);
}
?>