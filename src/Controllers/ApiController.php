<?php
namespace Api\Controllers;

abstract class ApiController
{

    // All inheriting classes must implement the processRequest method
    abstract function processRequest();

    /*
     * NOTE: the client is responsible for checking the response status
     * headers.
     */

    public function itemResponse($item) {
        // 200 - OK
        http_response_code(200);
        echo json_encode([
            'item' => $item
        ]);
        exit;
    }

    public function listResponse($items) {
        // 200 - OK
        http_response_code(200);
        echo json_encode([
            'items' => $items,
            'total' => count($items),
            'limit' => 'TBD',
            'offset' => 'TBD',
            'prev' => 'TBD',
            'next' => 'TBD',
            'href' => 'TBD'
        ]);
        exit;
    }

    public function createdResponse($resourceId) {
        // 201 - successful create
        http_response_code(201);
        // include location of the new resource
        $newResourceLocation = $this->_buildUriBase() . $resourceId;
        header($newResourceLocation);
        exit;
    }

    public function noContentResponse() {
        // 204 - successful, DELETE or PUT
        http_response_code(204);
        exit;
    }

    public function badRequestResponse() {
        // 400 - malformed or bad request
        http_response_code(400);
        exit;
    }

    public function unprocessableResponse($errors) {
        // 422 - well formed but unprocessable POST or PUT
        // Must include error messages so client
        // can alert user or highlight unacceptable
        // input.
        http_response_code(422);
        echo json_encode([
            'errors' => $errors
        ]);
        exit;
    }

    public function notFoundResponse() {
        http_response_code(404);
        exit;
    }

    private function _buildUriBase() {
        // TODO: this should compile full URL from
        // the $_SERVER properties
        return 'http://localhost/api/products/';
    }
}