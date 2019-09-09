<?php
namespace Api\Controllers;

abstract class ApiController
{

    // All inheriting classes must implement the processRequest method
    abstract function processRequest();

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

    public function unprocessableResponse() {
        // 422 - bad POST or PUT
        // Must include error messages so client
        // can alert user or highlight unacceptable
        // input.
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