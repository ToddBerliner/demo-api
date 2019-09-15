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
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    // Answer the OPTIONS request to the subsequent DELETE call can be made
    exit(0);
}


// Validate request is for an available endpoint
// and that the path is complete
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriParts = explode( '/', $uri );
$endpoint = null;
$productId = null;
foreach($uriParts as $idx => $part) {
    if ($part === 'api' || $part === 'api.php') {
        $endpoint = $uriParts[$idx + 1];
        if (isset($uriParts[($idx + 2)])) {
            $productId = $uriParts[($idx + 2)];
        }
    }
}

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
if (!isset($endpoint)) {
    returnInvalidEndpoint();
}
// Request method
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Simplistic routing
switch($endpoint) {
    case 'products':
        $controller = new ProductsController($requestMethod, $productId);
        $controller->processRequest();
        break;
    default:
        returnInvalidEndpoint();
}