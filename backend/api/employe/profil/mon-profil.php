<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, x-user-id, x-user-role');
header('Access-Control-Allow-Credentials: false');

// Gérer la requête OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log pour débogage
error_log("=== Appel mon-profil.php ===");
error_log("Méthode: " . $_SERVER['REQUEST_METHOD']);
error_log("Headers reçus: " . json_encode(getallheaders()));
error_log("User ID: " . ($_SERVER['HTTP_X_USER_ID'] ?? 'Non fourni'));
error_log("User Role: " . ($_SERVER['HTTP_X_USER_ROLE'] ?? 'Non fourni'));

try {
    // ==================== CHEMIN DES INCLUDES ====================
    // Utiliser un chemin absolu depuis la racine du serveur
    $rootDir = $_SERVER['DOCUMENT_ROOT'] . '/centre-formation/backend';
    
    $authFile = $rootDir . '/includes/auth-check.php';
    $dbFile = $rootDir . '/config/database.php';
    
    error_log("Chemin auth: " . $authFile);
    error_log("Chemin db: " . $dbFile);
    error_log("Auth existe: " . (file_exists($authFile) ? 'OUI' : 'NON'));
    error_log("DB existe: " . (file_exists($dbFile) ? 'OUI' : 'NON'));
    
    if (!file_exists($authFile) || !file_exists($dbFile)) {
        throw new Exception("Fichiers de configuration introuvables");
    }
    
    require_once $authFile;
    require_once $dbFile;
    
    // ==================== VÉRIFICATION AUTH SIMPLIFIÉE ====================
    // Votre auth-check.php n'a pas de classe Auth, ce sont des fonctions
    // Donc on utilise directement les fonctions
    
    $userId = $_SERVER['HTTP_X_USER_ID'] ?? null;
    $userRole = $_SERVER['HTTP_X_USER_ROLE'] ?? null;
    
    error_log("User ID depuis header: " . $userId);
    error_log("User Role depuis header: " . $userRole);
    
    if (!$userId || !$userRole) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Headers d\'authentification manquants',
            'headers_received' => getallheaders()
        ]);
        exit();
    }
    
    // Vérifier que c'est un employé
    if (strtolower($userRole) !== 'employe') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Accès réservé aux employés. Rôle détecté: ' . $userRole
        ]);
        exit();
    }
    
    // ==================== CONNEXION BD ====================
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception("Échec de connexion à la base de données");
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        getProfil($conn, $userId);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    }
    
} catch (Exception $e) {
    error_log("Erreur dans mon-profil.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur serveur: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}

// ==================== FONCTIONS ====================

// Récupérer le profil complet
function getProfil($conn, $userId) {
    error_log("getProfil appelé pour user ID: " . $userId);
    
    // 1. Récupérer les informations de base utilisateur + employé
    $query = "
        SELECT 
            u.id,
            u.email,
            u.nom,
            u.prenom,
            u.role,
            u.date_inscription,
            e.poste,
            e.departement,
            e.date_embauche,
            e.cin
        FROM utilisateur u
        LEFT JOIN employe e ON u.id = e.id_utilisateur
        WHERE u.id = :id
        AND u.deleted_at IS NULL
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profil) {
        error_log("Profil non trouvé pour ID: " . $userId);
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'message' => 'Profil non trouvé',
            'user_id' => $userId
        ]);
        return;
    }
    
    error_log("Profil trouvé: " . json_encode($profil));
    
    // 2. Récupérer les statistiques
    $stats = getStats($conn, $userId);
    error_log("Stats calculées: " . json_encode($stats));
    
    // 3. Récupérer les inscriptions avec détails des formations
    $inscriptions = getInscriptionsAvecFormations($conn, $userId);
    error_log("Inscriptions trouvées: " . count($inscriptions));
    
    // 4. Organiser les formations par statut
    $formations = organiseFormations($inscriptions);
    error_log("Formations organisées: " . json_encode(array_map('count', $formations)));
    
    // 5. Préparer la réponse
    $response = [
        'success' => true,
        'message' => 'Profil chargé avec succès',
        'timestamp' => date('Y-m-d H:i:s'),
        'profil' => $profil,
        'stats' => $stats,
        'formations' => $formations,
        'inscriptions' => $inscriptions
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    error_log("=== Fin mon-profil.php avec succès ===");
}

// Récupérer les statistiques
function getStats($conn, $userId) {
    try {
        $query = "
            SELECT 
                COUNT(DISTINCT i.id) as inscriptions_total,
                COUNT(DISTINCT CASE WHEN i.statut = 'VALIDEE' THEN i.id END) as inscriptions_validees,
                COUNT(DISTINCT CASE WHEN i.statut = 'EN_ATTENTE' THEN i.id END) as inscriptions_en_attente,
                COUNT(DISTINCT CASE WHEN i.statut = 'ANNULEE' THEN i.id END) as inscriptions_annulees,
                COUNT(DISTINCT CASE WHEN i.statut = 'REFUSEE' THEN i.id END) as inscriptions_refusees,
                COUNT(DISTINCT f.id) as formations_total
            FROM utilisateur u
            LEFT JOIN employe e ON u.id = e.id_utilisateur
            LEFT JOIN inscription i ON e.id = i.id_employe
            LEFT JOIN session s ON i.id_session = s.id
            LEFT JOIN formation f ON s.id_formation = f.id
            WHERE u.id = :id
            AND u.deleted_at IS NULL
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return [
                'inscriptions_total' => 0,
                'inscriptions_validees' => 0,
                'inscriptions_en_attente' => 0,
                'inscriptions_annulees' => 0,
                'inscriptions_refusees' => 0,
                'formations_total' => 0
            ];
        }
        
        // Convertir les valeurs en entiers
        foreach ($result as $key => $value) {
            $result[$key] = (int)$value;
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Erreur getStats: " . $e->getMessage());
        return [
            'inscriptions_total' => 0,
            'inscriptions_validees' => 0,
            'inscriptions_en_attente' => 0,
            'inscriptions_annulees' => 0,
            'inscriptions_refusees' => 0,
            'formations_total' => 0
        ];
    }
}

// Récupérer les inscriptions avec détails des formations
function getInscriptionsAvecFormations($conn, $userId) {
    try {
        $query = "
            SELECT 
                i.id as inscription_id,
                i.date_inscription,
                i.statut as inscription_statut,
                i.raison_refus,
                s.id as session_id,
                s.date_debut,
                s.date_fin,
                s.salle,
                s.places_max,
                s.description as session_description,
                f.id as formation_id,
                f.titre as formation_titre,
                f.description as formation_description,
                f.duree_heures,
                f.competences_visees,
                uf.nom as formateur_nom,
                uf.prenom as formateur_prenom
            FROM utilisateur u
            JOIN employe e ON u.id = e.id_utilisateur
            JOIN inscription i ON e.id = i.id_employe
            JOIN session s ON i.id_session = s.id
            JOIN formation f ON s.id_formation = f.id
            LEFT JOIN formateur fr ON s.id_formateur = fr.id
            LEFT JOIN utilisateur uf ON fr.id_utilisateur = uf.id
            WHERE u.id = :id
            AND u.deleted_at IS NULL
            AND f.deleted_at IS NULL
            AND s.deleted_at IS NULL
            ORDER BY s.date_debut DESC
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $result ?: [];
        
    } catch (Exception $e) {
        error_log("Erreur getInscriptionsAvecFormations: " . $e->getMessage());
        return [];
    }
}

// Organiser les formations par statut
function organiseFormations($inscriptions) {
    $formations = [
        'completed' => [],
        'in_progress' => [],
        'upcoming' => []
    ];
    
    if (empty($inscriptions)) {
        return $formations;
    }
    
    $now = new DateTime();
    
    foreach ($inscriptions as $inscription) {
        try {
            if (empty($inscription['date_debut']) || empty($inscription['date_fin'])) {
                continue;
            }
            
            $dateDebut = new DateTime($inscription['date_debut']);
            $dateFin = new DateTime($inscription['date_fin']);
            
            $formation = [
                'id' => $inscription['formation_id'] ?? 0,
                'titre' => $inscription['formation_titre'] ?? 'Titre non disponible',
                'description' => $inscription['formation_description'] ?? '',
                'date_debut' => $inscription['date_debut'],
                'date_fin' => $inscription['date_fin'],
                'duree_heures' => $inscription['duree_heures'] ?? 0,
                'formateur' => trim(($inscription['formateur_nom'] ?? '') . ' ' . ($inscription['formateur_prenom'] ?? '')),
                'salle' => $inscription['salle'] ?? 'Non spécifiée',
                'inscription_statut' => $inscription['inscription_statut'] ?? 'EN_ATTENTE'
            ];
            
            if (empty($formation['formateur'])) {
                $formation['formateur'] = 'Non assigné';
            }
            
            if ($dateFin < $now && ($inscription['inscription_statut'] ?? '') === 'VALIDEE') {
                $formation['statut'] = 'completed';
                $formations['completed'][] = $formation;
            } elseif ($dateDebut <= $now && $dateFin >= $now && ($inscription['inscription_statut'] ?? '') === 'VALIDEE') {
                $formation['statut'] = 'in_progress';
                $formations['in_progress'][] = $formation;
            } elseif ($dateDebut > $now && ($inscription['inscription_statut'] ?? '') === 'VALIDEE') {
                $formation['statut'] = 'upcoming';
                $formations['upcoming'][] = $formation;
            } elseif ($inscription['inscription_statut'] === 'EN_ATTENTE') {
                // Les inscriptions en attente sont considérées comme à venir
                if ($dateDebut > $now) {
                    $formation['statut'] = 'upcoming';
                    $formations['upcoming'][] = $formation;
                }
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    return $formations;
}