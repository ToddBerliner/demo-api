<?php
namespace Api\Models;

use Api\Config\Database;

class ProductsTable {

    private $db;
    private $requestMethod;
    private $productId;

    const SKU = 'sku';
    const ALT_SKU = 'alt_sku';
    const MERCHANT_ID = 'merchant_id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const UNIT_PRICE = 'unit_price';
    const WEIGHT = 'weight';
    const LENGTH = 'length';
    const HEIGHT = 'height';
    const IS_ACTIVE = 'is_active';
    const QUANTITY = 'quantity';

    const PUBLIC_FIELDS = [
        self::SKU,
        self::ALT_SKU,
        self::MERCHANT_ID,
        self::NAME,
        self::DESCRIPTION,
        self::UNIT_PRICE,
        self::WEIGHT,
        self::LENGTH,
        self::HEIGHT,
        self::QUANTITY
    ];

    public function __construct() {
        // Get our database connection
        $db = new Database();
        $this->db = $db->getDbConnection();
    }

    public function find($productId) {
        // Not all fields need to be included in response
        $fields = implode(',', self::PUBLIC_FIELDS);
        $statement = <<<ENDQUERY
SELECT 
    $fields
FROM 
    products
WHERE id = :productId
ENDQUERY;

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(['productId' => $productId]);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            // LOG or otherwise handle the error. For a simple
            // find of a given productId, there's no real useful
            // error states to handle.
            return false;
        }
    }

    public function findAll() {
        // Not all fields need to be included in response
        $fields = implode(',', self::PUBLIC_FIELDS);
        $statement = <<<ENDQUERY
SELECT 
    $fields
FROM 
    products
ENDQUERY;

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(['productId' => $productId]);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            // LOG or otherwise handle the error. For a simple
            // find of a given productId, there's no real useful
            // error states to handle.
            return false;
        }
    }

    public function createProduct($product) {}

    public function updateProduct($product) {}

    public function deleteProduct($productId) {}

    public function validateProduct($product) {
        return true;
    }
}