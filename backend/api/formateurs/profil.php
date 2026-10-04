<?php
// backend/api/formateurs/profil.php - VERSION CORRIGÉE
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Formateur uniquement - UTILISEZ isFormateur() au lieu de getCurrentUser()
if (!isFormateur()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux formateurs']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    // Utilisez getCurrentUserId() et getCurrentUserRole() qui existent
    $userId = getCurrentUserId();
    $userRole = getCurrentUserRole();
    
    error_log("Profil formateur - User ID: $userId, Role: $userRole");
    
    // Récupérer les informations COMPLÈTES avec la spécialité
    $sql = "SELECT 
                u.id,
                u.email,
                u.nom,
                u.prenom,
                u.role,
                u.date_inscription,
                COALESCE(f.specialite, 'Non spécifiée') as specialite,
                f.cin
            FROM utilisateur u
            LEFT JOIN formateur f ON u.id = f.id_utilisateur
            WHERE u.id = ? 
            AND u.deleted_at IS NULL";
    
    error_log("SQL: $sql");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $formateur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Résultat SQL: " . json_encode($formateur));
    
    if ($formateur) {
        // Vérifier si c'est bien un formateur
        if (strtoupper($formateur['role']) !== 'FORMATEUR') {
            error_log("Utilisateur $userId n'est pas un formateur, rôle: " . $formateur['role']);
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Utilisateur n\'est pas un formateur',
                'user_role' => $formateur['role']
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Profil formateur chargé avec succès',
            'data' => $formateur
        ]);
    } else {
        error_log("Formateur non trouvé pour user ID: $userId");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Formateur non trouvé dans la base de données',
            'user_id' => $userId
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>