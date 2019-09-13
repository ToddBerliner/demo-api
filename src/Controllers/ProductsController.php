<?php
namespace Api\Controllers;

use Api\Models\ProductsTable;

class ProductsController extends ApiController {

    private $requestMethod;
    private $productId;
    private $ProductsTable;

    public function __construct($requestMethod, $productId) {
        $this->requestMethod = $requestMethod;
        $this->productId = $productId;
        $this->ProductsTable = new ProductsTable();
    }

    public function processRequest() {
        // Finally, let's get to work!
        switch($this->requestMethod) {
            case 'GET':
                if (isset($this->productId)) {
                    $this->_getProduct($this->productId);
                } else {
                    // check for paging params
                    $this->_getProducts();
                }
                break;
            case 'POST':
                // Get the post data
                $postData = json_decode(file_get_contents("php://input"), TRUE);
                if (!$postData) {
                    $this->badRequestResponse();
                }
                $productData = $postData['product'];
                $this->_createProduct($productData);
                break;
            case 'PUT':
                $putData = json_decode(file_get_contents("php://input"), TRUE);
                if (!$putData) {
                    $this->badRequestResponse();
                }
                $productData = $putData['product'];
                $this->_updateProduct($productData);
                break;
            case 'DELETE':
                $this->_deleteProduct($this->productId);
                break;
            default:
                $this->notFoundResponse();
        }
    }

    private function _getProduct($productId) {
        $product = $this->ProductsTable->find($productId);
        if (!$product) {
            $this->notFoundResponse();
        } else {
            $this->itemResponse($product);
        }
    }

    private function _getProducts() {
        // NOTE: I'm omitting the handling of offset & limit
        // to support paging for brevity. Ideally, paging would
        // be handled and a proper response would include the following
        // keys: offset, limit, prev, next, href and total.
        $items = $this->ProductsTable->findAll();
        $this->listResponse($items);
    }

    private function _createProduct($productData) {
        /*
         * Response will either be a 201, created, with Location header set
         * or a 422, unprocessable, with an error message indicating the
         * unprocessable fields.
         */
        $result = $this->ProductsTable->createProduct($productData);
        if (isset($result['errors'])) {
            $this->unprocessableResponse($result['errors']);
        } else {
            $this->createdResponse($result[ProductsTable::ID]);
        }
    }

    private function _updateProduct($productData) {
        /*
         * Response will either be 204, no content, or
         * 422, unprocessable, with an error message indicating
         * the unprocessable fields.
         */
        $result = $this->ProductsTable->updateProduct($productData);
        if (isset($result['errors'])) {
            $this->unprocessableResponse($result['errors']);
        } else {
            $this->noContentResponse();
        }
    }

    private function _deleteProduct($productId) {
        /*
         * Response will either be 204, no content, or
         * 422, unprocessable, with an error message indicating
         * the reason.
         */
        $result = $this->ProductsTable->deleteProduct($productId);
        if (isset($result['errors'])) {
            $this->unprocessableResponse($result['errors']);
        } else {
            $this->noContentResponse();
        }
    }

}
/*
 * TODO: refactor to include a Model/Product class instead of just
 * lumping them together in the ProducsTable class.
 */