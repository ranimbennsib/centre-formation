<?php
// backend/api/formateurs/update.php - AVEC GESTION DU CIN

// DÉBUT - Nettoyer tout output
while (ob_get_level()) {
    ob_end_clean();
}

// Headers CORS
header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-User-Id, X-User-Role");
header("Access-Control-Allow-Credentials: false");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';

// Fonction pour envoyer une réponse JSON
function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Récupérer l'ID utilisateur depuis les headers
$headers = getallheaders();
$userId = isset($headers['X-User-Id']) ? intval($headers['X-User-Id']) : null;
$userRole = isset($headers['X-User-Role']) ? strtoupper($headers['X-User-Role']) : null;

// DEBUG
error_log("=== UPDATE.PHP DEBUG ===");
error_log("User ID from header: " . $userId);
error_log("User Role from header: " . $userRole);
error_log("All headers: " . print_r($headers, true));

if (!$userId) {
    sendResponse(false, 'Utilisateur non authentifié. Header X-User-Id manquant.', null, 401);
}

$formateurId = $_GET['id'] ?? null;
if (!$formateurId || !is_numeric($formateurId)) {
    sendResponse(false, 'ID formateur invalide', null, 400);
}

$db = new Database();
$pdo = $db->getConnection();

try {
    // Si pas de rôle dans les headers, le récupérer de la base
    if (!$userRole) {
        error_log("Rôle non trouvé dans headers, recherche en base...");
        $stmtUser = $pdo->prepare("SELECT id, role FROM utilisateur WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendResponse(false, 'Utilisateur non trouvé en base de données', null, 401);
        }
        
        $userRole = strtoupper($user['role']);
        error_log("Rôle trouvé en base: " . $userRole);
    }

    // Vérifier que le formateur existe (INCLURE LE CIN)
    $stmtCheck = $pdo->prepare("SELECT f.*, u.id as utilisateur_id, u.email, u.nom, u.prenom, u.role
                                FROM formateur f 
                                JOIN utilisateur u ON f.id_utilisateur = u.id 
                                WHERE f.id = ?");
    $stmtCheck->execute([$formateurId]);
    $formateur = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$formateur) {
        sendResponse(false, 'Formateur non trouvé', null, 404);
    }

    error_log("Formateur trouvé:");
    error_log("Formateur utilisateur_id: " . $formateur['utilisateur_id']);
    error_log("Formateur CIN: " . ($formateur['cin'] ?? 'Non défini'));
    error_log("Formateur role: " . $formateur['role']);
    error_log("Current user ID: " . $userId);
    error_log("Current user role: " . $userRole);

    // Vérifier les permissions - ADMIN peut tout modifier
    // FORMATEUR peut seulement se modifier lui-même
    $canUpdate = false;
    
    if ($userRole === 'ADMIN') {
        $canUpdate = true;
        error_log("Permission accordée: utilisateur est ADMIN");
    } elseif ($userRole === 'FORMATEUR') {
        // Vérifier si le formateur essaye de se modifier lui-même
        if ($formateur['utilisateur_id'] == $userId) {
            $canUpdate = true;
            error_log("Permission accordée: formateur modifie son propre profil");
        } else {
            error_log("Permission refusée: formateur essaye de modifier un autre formateur");
        }
    } else {
        error_log("Permission refusée: rôle inconnu " . $userRole);
    }
    
    if (!$canUpdate) {
        sendResponse(false, 'Permission refusée', [
            'debug_info' => [
                'user_id' => $userId,
                'user_role' => $userRole,
                'formateur_user_id' => $formateur['utilisateur_id'],
                'formateur_role' => $formateur['role'],
                'is_admin' => ($userRole === 'ADMIN'),
                'is_same_user' => ($formateur['utilisateur_id'] == $userId)
            ]
        ], 403);
    }

    // Lire les données PUT
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(false, 'Données JSON invalides: ' . json_last_error_msg(), null, 400);
    }

    // Si pas de données, retourner les données existantes
    if (empty($data)) {
        sendResponse(true, 'Données existantes', $formateur, 200);
    }

    error_log("Données reçues pour mise à jour: " . print_r($data, true));

    // Début de transaction
    $pdo->beginTransaction();

    // ==============================
    // 1. Mettre à jour la table formateur
    // ==============================
    $updatesFormateur = [];
    $paramsFormateur = [];
    
    // Spécialité
    if (isset($data['specialite']) && $data['specialite'] !== $formateur['specialite']) {
        $updatesFormateur[] = "specialite = ?";
        $paramsFormateur[] = $data['specialite'];
        error_log("Spécialité à mettre à jour: " . $data['specialite']);
    }
    
    // CIN - Seulement ADMIN peut modifier le CIN
    if ($userRole === 'ADMIN' && isset($data['cin'])) {
        $cin = trim($data['cin']);
        
        // Validation du CIN
        if (empty($cin)) {
            $pdo->rollBack();
            sendResponse(false, 'Le CIN ne peut pas être vide', null, 400);
        }
        
        if (strlen($cin) < 5 || strlen($cin) > 20) {
            $pdo->rollBack();
            sendResponse(false, 'Le CIN doit contenir entre 5 et 20 caractères', null, 400);
        }
        
        // Vérifier si le CIN n'est pas déjà utilisé par un autre formateur
        if ($cin !== ($formateur['cin'] ?? '')) {
            $stmtCheckCin = $pdo->prepare("SELECT id FROM formateur WHERE cin = ? AND id != ?");
            $stmtCheckCin->execute([$cin, $formateurId]);
            
            if ($stmtCheckCin->fetch()) {
                $pdo->rollBack();
                sendResponse(false, 'CIN déjà utilisé par un autre formateur', null, 400);
            }
            
            $updatesFormateur[] = "cin = ?";
            $paramsFormateur[] = $cin;
            error_log("CIN à mettre à jour: " . $cin);
        }
    } elseif (isset($data['cin']) && $userRole !== 'ADMIN') {
        error_log("Tentative de modification du CIN par un non-admin: " . $userRole);
        // On ignore silencieusement pour les non-admins
    }
    
    // Exécuter la mise à jour de la table formateur si nécessaire
    if (!empty($updatesFormateur)) {
        $paramsFormateur[] = $formateurId;
        $sqlFormateur = "UPDATE formateur SET " . implode(', ', $updatesFormateur) . " WHERE id = ?";
        $stmtFormateur = $pdo->prepare($sqlFormateur);
        $stmtFormateur->execute($paramsFormateur);
        error_log("Formateur mis à jour avec " . count($updatesFormateur) . " champs");
    }

    // ==============================
    // 2. Mettre à jour la table utilisateur
    // ==============================
    $updatesUtilisateur = [];
    $paramsUtilisateur = [];

    // Si admin, peut modifier nom/prénom/email
    // Si formateur, peut modifier seulement nom/prénom (pas email)
    if (($userRole === 'ADMIN') || ($userRole === 'FORMATEUR' && $formateur['utilisateur_id'] == $userId)) {
        
        // Email - seulement pour ADMIN
        if ($userRole === 'ADMIN' && isset($data['email']) && $data['email'] !== $formateur['email']) {
            // Vérifier doublon email
            $stmtEmail = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ? AND id != ?");
            $stmtEmail->execute([$data['email'], $formateur['utilisateur_id']]);
            if ($stmtEmail->fetch()) {
                $pdo->rollBack();
                sendResponse(false, 'Email déjà utilisé', null, 400);
            }
            $updatesUtilisateur[] = "email = ?";
            $paramsUtilisateur[] = $data['email'];
            error_log("Email à mettre à jour: " . $data['email']);
        }

        // Nom
        if (isset($data['nom']) && $data['nom'] !== $formateur['nom']) {
            $updatesUtilisateur[] = "nom = ?";
            $paramsUtilisateur[] = $data['nom'];
            error_log("Nom à mettre à jour: " . $data['nom']);
        }

        // Prénom
        if (isset($data['prenom']) && $data['prenom'] !== $formateur['prenom']) {
            $updatesUtilisateur[] = "prenom = ?";
            $paramsUtilisateur[] = $data['prenom'];
            error_log("Prénom à mettre à jour: " . $data['prenom']);
        }
    }

    // Exécuter les mises à jour utilisateur si nécessaire
    if (!empty($updatesUtilisateur)) {
        $paramsUtilisateur[] = $formateur['utilisateur_id'];
        $sqlUser = "UPDATE utilisateur SET " . implode(', ', $updatesUtilisateur) . " WHERE id = ?";
        $stmtUser = $pdo->prepare($sqlUser);
        $stmtUser->execute($paramsUtilisateur);
        error_log("Utilisateur mis à jour avec " . count($updatesUtilisateur) . " champs");
    }

    $pdo->commit();

    // Récupérer le formateur mis à jour (INCLURE LE CIN)
    $stmtFinal = $pdo->prepare("SELECT f.*, u.email, u.nom, u.prenom, u.role, u.date_inscription
                                FROM formateur f
                                JOIN utilisateur u ON f.id_utilisateur = u.id
                                WHERE f.id = ?");
    $stmtFinal->execute([$formateurId]);
    $updatedFormateur = $stmtFinal->fetch(PDO::FETCH_ASSOC);

    // Nettoyer les données sensibles
    unset($updatedFormateur['mot_de_passe_hash']);
    unset($updatedFormateur['deleted_at']);

    sendResponse(true, 'Formateur mis à jour avec succès', $updatedFormateur, 200);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur PDO: " . $e->getMessage());
    sendResponse(false, 'Erreur base de données: ' . $e->getMessage(), null, 500);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur générale: " . $e->getMessage());
    sendResponse(false, 'Erreur système: ' . $e->getMessage(), null, 500);
}
?>