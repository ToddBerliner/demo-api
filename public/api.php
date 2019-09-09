<?php

/*
 * Index page for the API.
 */
require '../vendor/autoload.php';
use Api\Config\Database;
use Api\Controllers\ProductsController;

// Sensible defaults, especially the specification of content types
// and allowed methods. We also want to set the Access-Control-Allow-Origin
// to support our single page application front end.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");

// Validate request is for an available endpoint
// and that the path is complete
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriParts = explode( '/', $uri );

function returnInvalidEndpoint() {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => 'Requested endpoint is invalid'
        ]
    ]);
    exit;
}

// Required path parts
if (!isset($uriParts[2])) {
    returnInvalidEndpoint();
}
// Request method
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Simplistic routing
switch($uriParts[2]) {
    case 'products':
        // check for product id
        $productId = isset($uriParts[3])
            ? $uriParts[3]
            : null;
        $controller = new ProductsController($requestMethod, $productId);
        $controller->processRequest();
        break;
    default:
        returnInvalidEndpoint();
}