<?php
// backend/includes/response-format.php

/**
 * Format de réponse standard pour succès
 */
function jsonSuccess($data = null, $message = 'Opération réussie') {
    $response = [
        'success' => true,
        'message' => $message,
        'timestamp' => time()
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    return $response;
}

/**
 * Format de réponse standard pour erreur
 */
function jsonError($message = 'Une erreur est survenue', $code = 'ERROR', $details = null) {
    $response = [
        'success' => false,
        'message' => $message,
        'error_code' => $code,
        'timestamp' => time()
    ];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    return $response;
}

/**
 * Envoyer une réponse JSON avec code HTTP
 */
function sendJsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}
?>