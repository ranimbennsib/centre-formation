<?php
// backend/api/formateurs/create.php - VERSION AVEC CIN
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Admin uniquement
requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

// Validation - AJOUT du CIN
$required = ['email', 'nom', 'prenom', 'mot_de_passe', 'cin']; // ← AJOUT de 'cin'
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Champ '$field' manquant"
        ]);
        exit;
    }
}

$email = trim($data['email']);
$nom = trim($data['nom']);
$prenom = trim($data['prenom']);
$cin = trim($data['cin']); // ← NOUVEAU CHAMP
$motDePasse = trim($data['mot_de_passe']);
$specialite = $data['specialite'] ?? 'Non spécifiée';

// Validation supplémentaire pour le mot de passe
if (strlen($motDePasse) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Le mot de passe doit contenir au moins 6 caractères'
    ]);
    exit;
}

// Validation du CIN
if (strlen($cin) < 5 || strlen($cin) > 20) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Le CIN doit contenir entre 5 et 20 caractères'
    ]);
    exit;
}

try {
    // Vérifier si email déjà utilisé
    $stmtCheck = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $stmtCheck->execute([$email]);

    if ($stmtCheck->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email déjà utilisé'
        ]);
        exit;
    }

    // Vérifier si CIN déjà utilisé dans la table formateur
    $stmtCheckCin = $pdo->prepare("SELECT id FROM formateur WHERE cin = ?");
    $stmtCheckCin->execute([$cin]);

    if ($stmtCheckCin->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'CIN déjà utilisé par un autre formateur'
        ]);
        exit;
    }

    // 🔐 Hacher le mot de passe fourni par l'admin
    $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);

    // 🚀 DÉBUT TRANSACTION
    $pdo->beginTransaction();

    // 1️⃣ Créer utilisateur
    $sqlUser = "INSERT INTO utilisateur 
                (email, nom, prenom, role, mot_de_passe_hash, date_inscription)
                VALUES (?, ?, ?, 'FORMATEUR', ?, NOW())";

    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([
        $email,
        $nom,
        $prenom,
        $motDePasseHash
    ]);

    $userId = $pdo->lastInsertId();

    // 2️⃣ Créer formateur AVEC CIN
    $sqlFormateur = "INSERT INTO formateur (id_utilisateur, specialite, cin)
                     VALUES (?, ?, ?)"; // ← AJOUT de cin

    $stmtFormateur = $pdo->prepare($sqlFormateur);
    $stmtFormateur->execute([
        $userId,
        $specialite,
        $cin // ← AJOUT
    ]);

    $formateurId = $pdo->lastInsertId();

    // ✅ TOUT OK
    $pdo->commit();

    // Retourner une réponse avec le CIN
    echo json_encode([
        'success' => true,
        'message' => 'Formateur créé avec succès',
        'data' => [
            'formateur_id' => $formateurId,
            'utilisateur_id' => $userId,
            'email' => $email,
            'nom_complet' => $prenom . ' ' . $nom,
            'cin' => $cin, // ← AJOUT
            'specialite' => $specialite,
            'note' => 'Le formateur doit utiliser le mot de passe défini par l\'admin'
        ]
    ]);

} catch (Exception $e) {
    // ❌ ANNULATION AUTOMATIQUE
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la création du formateur',
        'error' => $e->getMessage()
    ]);
}
?>