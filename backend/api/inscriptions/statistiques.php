<?php
require_once '../../includes/cors.php';
require_once '../../includes/auth-check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin uniquement']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

try {
    // Récupérer les paramètres
    $period = $_GET['period'] ?? 'all';
    $formationId = $_GET['formation_id'] ?? 'all';
    $comparison = $_GET['comparison'] ?? 'none';
    
    // Construire la clause WHERE
    $whereClause = "WHERE 1=1";
    
    // Filtre par période
    if ($period !== 'all') {
        switch ($period) {
            case 'today':
                $whereClause .= " AND DATE(i.date_inscription) = CURDATE()";
                break;
            case 'week':
                $whereClause .= " AND YEARWEEK(i.date_inscription) = YEARWEEK(CURDATE())";
                break;
            case 'month':
                $whereClause .= " AND MONTH(i.date_inscription) = MONTH(CURDATE()) 
                                 AND YEAR(i.date_inscription) = YEAR(CURDATE())";
                break;
            case 'quarter':
                $whereClause .= " AND QUARTER(i.date_inscription) = QUARTER(CURDATE()) 
                                 AND YEAR(i.date_inscription) = YEAR(CURDATE())";
                break;
            case 'year':
                $whereClause .= " AND YEAR(i.date_inscription) = YEAR(CURDATE())";
                break;
        }
    }
    
    // Filtre par formation
    if ($formationId !== 'all') {
        $whereClause .= " AND f.id = :formation_id";
    }
    
    // Statistiques globales
    $sqlGlobal = "SELECT 
                    COUNT(*) as total_inscriptions,
                    SUM(CASE WHEN i.statut = 'VALIDEE' THEN 1 ELSE 0 END) as validees,
                    SUM(CASE WHEN i.statut = 'EN_ATTENTE' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN i.statut = 'REFUSEE' THEN 1 ELSE 0 END) as refusees,
                    SUM(CASE WHEN i.statut = 'ANNULEE' THEN 1 ELSE 0 END) as annulees
                  FROM inscription i
                  LEFT JOIN session s ON i.id_session = s.id
                  LEFT JOIN formation f ON s.id_formation = f.id
                  $whereClause";
    
    $stmtGlobal = $pdo->prepare($sqlGlobal);
    if ($formationId !== 'all') {
        $stmtGlobal->bindParam(':formation_id', $formationId);
    }
    $stmtGlobal->execute();
    $global = $stmtGlobal->fetch(PDO::FETCH_ASSOC);

    // Statistiques par formation (corrigé)
    $sqlFormations = "SELECT 
                        f.id,
                        f.titre,
                        COUNT(i.id) as total_inscriptions,
                        SUM(CASE WHEN i.statut = 'VALIDEE' THEN 1 ELSE 0 END) as validees,
                        SUM(CASE WHEN i.statut = 'EN_ATTENTE' THEN 1 ELSE 0 END) as en_attente,
                        SUM(CASE WHEN i.statut = 'REFUSEE' THEN 1 ELSE 0 END) as refusees,
                        SUM(CASE WHEN i.statut = 'ANNULEE' THEN 1 ELSE 0 END) as annulees
                      FROM formation f
                      LEFT JOIN session s ON f.id = s.id_formation
                      LEFT JOIN inscription i ON s.id = i.id_session
                      WHERE 1=1";
    
    // Ajouter le filtre formation si spécifié
    if ($formationId !== 'all') {
        $sqlFormations .= " AND f.id = :formation_id2";
    }
    
    $sqlFormations .= " GROUP BY f.id
                       ORDER BY total_inscriptions DESC";
    
    $stmtFormations = $pdo->prepare($sqlFormations);
    if ($formationId !== 'all') {
        $stmtFormations->bindParam(':formation_id2', $formationId);
    }
    $stmtFormations->execute();
    $parFormation = $stmtFormations->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques par mois avec filtre
    $sqlMois = "SELECT 
                  DATE_FORMAT(i.date_inscription, '%Y-%m') as mois,
                  COUNT(*) as inscriptions
                FROM inscription i
                LEFT JOIN session s ON i.id_session = s.id
                LEFT JOIN formation f ON s.id_formation = f.id
                WHERE 1=1";
    
    if ($period !== 'all') {
        switch ($period) {
            case 'today':
                $sqlMois .= " AND DATE(i.date_inscription) = CURDATE()";
                break;
            case 'week':
                $sqlMois .= " AND YEARWEEK(i.date_inscription) = YEARWEEK(CURDATE())";
                break;
            case 'month':
                $sqlMois .= " AND MONTH(i.date_inscription) = MONTH(CURDATE()) 
                             AND YEAR(i.date_inscription) = YEAR(CURDATE())";
                break;
            case 'quarter':
                $sqlMois .= " AND QUARTER(i.date_inscription) = QUARTER(CURDATE()) 
                             AND YEAR(i.date_inscription) = YEAR(CURDATE())";
                break;
            case 'year':
                $sqlMois .= " AND YEAR(i.date_inscription) = YEAR(CURDATE())";
                break;
            default:
                $sqlMois .= " AND i.date_inscription >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
                break;
        }
    } else {
        $sqlMois .= " AND i.date_inscription >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
    }
    
    if ($formationId !== 'all') {
        $sqlMois .= " AND f.id = :formation_id3";
    }
    
    $sqlMois .= " GROUP BY DATE_FORMAT(i.date_inscription, '%Y-%m')
                 ORDER BY mois DESC";
    
    $stmtMois = $pdo->prepare($sqlMois);
    if ($formationId !== 'all') {
        $stmtMois->bindParam(':formation_id3', $formationId);
    }
    $stmtMois->execute();
    $parMois = $stmtMois->fetchAll(PDO::FETCH_ASSOC);

    // Taux de conversion
    $total = (int)($global['total_inscriptions'] ?? 0);
    $validees = (int)($global['validees'] ?? 0);
    $tauxConversion = $total > 0 
        ? round(($validees / $total) * 100, 2)
        : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'global' => $global ?: [
                'total_inscriptions' => 0,
                'validees' => 0,
                'en_attente' => 0,
                'refusees' => 0,
                'annulees' => 0
            ],
            'par_formation' => $parFormation ?: [],
            'par_mois' => $parMois ?: [],
            'taux_conversion' => $tauxConversion . '%'
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du calcul des statistiques',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}