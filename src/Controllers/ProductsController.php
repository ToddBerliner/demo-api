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
        $this->Products = new ProductsModel();
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
            default:
                $this->notFoundResponse();
        }
    }

    private function _getProduct($productId) {
        $product = $this->Products->getProduct($productId);
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
        $items = [];



        $this->listResponse($items);
    }

}