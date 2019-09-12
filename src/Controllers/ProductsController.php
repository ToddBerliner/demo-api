<?php
namespace Api\Controllers;

use Api\Models\ProductsTable;

class ProductsController extends ApiController {

    private $requestMethod;
    private $productId;
    private $Products;

    public function __construct($requestMethod, $productId) {
        $this->requestMethod = $requestMethod;
        $this->productId = $productId;
        $this->Products = new ProductsTable();
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
                $productData = json_decode(file_get_contents("php://input"), TRUE);
                $this->_createProduct($productData);
                break;
            default:
                $this->notFoundResponse();
        }
    }

    private function _getProduct($productId) {
        $product = $this->Products->find($productId);
        if (!$product) {
            $this->notFoundResponse();
        } else {
            $this->itemResponse($product);
        }
    }

    private function _createProduct($productData) {
        /*
         * Requirements for creation of product:
         * * product must validate, all required fields & field types/sizes
         * * product should not already exist
         * * sku and alt_sku, if provided, must be different
         * * NOTE: merchant sku and alt_sku uniqueness will be enforced via database
         * *    to prevent need for additional query to check for existing sku/alt_sku
         * *    for merchant
         *
         * Response will either be a 201, created, with Location header set
         * or a 422, unprocessable, with an error message indicating the
         * unprocessable fields.
         */
        $result = $this->Products->createProduct($productData);
        if (isset($result['errors'])) {
            $this->unprocessableResponse($result['errors']);
        } else {
            $this->createdResponse($result['id']);
        }
    }

    private function _getProducts() {
        // NOTE: I'm omitting the handling of offset & limit
        // to support paging for brevity. Ideally, paging would
        // be handled and a proper response would include the following
        // keys: offset, limit, prev, next, href and total.
        $items = $this->Products->findAll();
        $this->listResponse($items);
    }

}