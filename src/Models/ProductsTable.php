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
        self::DESCRIPTION,
        self::UNIT_PRICE,
        self::WEIGHT,
        self::LENGTH,
        self::HEIGHT,
        self::QUANTITY
    ];

    const REQUIRED_FIELDS = [
        self::SKU,
        self::MERCHANT_ID,
        self::DESCRIPTION,
        self::UNIT_PRICE,
        self::WEIGHT,
        self::LENGTH,
        self::HEIGHT
    ];

    public function __construct() {
        // Get our database connection
        $db = new Database();
        $this->db = $db->getDbConnection();
    }

    public function find($productId) {
        // Not all fields need to be included in response
        $fields = implode(',', self::PUBLIC_FIELDS);
        $statement = "
            SELECT 
                $fields
            FROM 
                products
            WHERE id = :productId;
        ";

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
        $statement = "
            SELECT 
                $fields
            FROM 
                products
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            // LOG or otherwise handle the error. For a simple
            // find of a the products, there's no real useful
            // error states to handle.
            return false;
        }
    }

    public function createProduct($product) {
        // validate product, return errors if any
        $errors = self::getValidationErrors($product);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // try to insert the new record and check for exceptions
        // on unique keys
        $statement = <<<ENDQUERY
INSERT INTO products
    (sku, alt_sku, merchant_id, description, unit_price, weight, length, height, quantity)
VALUES
    (:sku, :alt_sku, :merchant_id, :description, :unit_price, :weight, :length, :height, :quantity)
ENDQUERY;
        $statement = $this->db->prepare($statement);

        // :sku, :alt_sku, :merchant_id, :description, :unit_price, :weight, :length, :height, :quantity)
        $statement->execute([
            self::SKU => $product[self::SKU],
            self::ALT_SKU => isset($product[self::ALT_SKU])
                ? $product[self::ALT_SKU] : null,
            self::MERCHANT_ID => $product[self::MERCHANT_ID],
            self::DESCRIPTION => $product[self::DESCRIPTION],
            self::UNIT_PRICE => $product[self::UNIT_PRICE],
            self::WEIGHT => $product[self::WEIGHT],
            self::LENGTH => $product[self::LENGTH],
            self::HEIGHT => $product[self::HEIGHT],
            self::QUANTITY => isset($product[self::QUANTITY])
                ? $product[self::QUANTITY] : null // has default of 0
        ]);

        // LEFT OFF HERE

        // return ['id' => lastInsertId]
        return false;
    }

    public function updateProduct($product) {}

    public function deleteProduct($productId) {}

    public static function getValidationErrors($product) {
        $errors = [];
        // check for existing product ID
        if (isset($product['id'])) {
            $errors['id'] = 'product already has been created';
            return $errors;
        }
        // check all required fields
        foreach(self::REQUIRED_FIELDS as $field) {
            // first, check if empty
            if (empty($product[$field])) {
                $errors[$field] = 'required field';
            } else {
                // second check individual field requirements
                // NOTE: could refactor to storing field validation array
                // with [$field => $validatorFunc] and calling them in an
                // iteration
                $value = $product[$field];
                switch($field) {
                    case self::SKU:
                        if (!self::isAlphaNumMaxChars($value, 16)) {
                            $errors[$field] = 'must be 16 alpha numeric string';
                        }
                        break;
                    case self::MERCHANT_ID:
                        if (!is_numeric($value)) {
                            // reasonable guess
                            $errors[$field] = 'merchant ID must be numeric';
                        }
                        break;
                    case self::DESCRIPTION:
                        if (strlen($value) > 250) {
                            $errors[$field] = 'description must be 250 chars max';
                        }
                        break;
                    case self::UNIT_PRICE:
                        if (!self::isPositiveMaxPrecision($value, 2)) {
                            $errors[$field] = 'must be positive number with at most 2 decimal places';
                        }
                        break;
                    case self::WEIGHT:
                    case self::LENGTH:
                    case self::HEIGHT:
                        if (!self::isPositiveMaxPrecision($value, 4)) {
                            $errors[$field] = 'must be positive number with at most 4 decimal places';
                        }
                        break;
                    case self::QUANTITY:
                        if (!is_numeric($value)) {
                            $errors[$field] = 'quantity must be numeric'; // prob need more specificity here
                        }
                        break;
                    default:
                        $errors[$field] = $field . ' is not a valid field';
                }
            }
            // Special case for ALT_SKU
            if (isset($product[self::ALT_SKU])) {
                $value = $product[self::ALT_SKU];
                if ($product[self::SKU] == $value) {
                    $errors[self::ALT_SKU] = 'sku and alt sku must be different';
                } elseif (!self::isAlphaNumMaxChars($value, 16)) {
                    $errors[self::ALT_SKU] = 'must be 16 alpha numeric string';
                }
            }
        }

        return $errors;
    }

    // NOTE: these could be broken into two validations for uber specific
    // error messaging
    public static function isAlphaNumMaxChars($string, $maxChars) {
        $pattern = '/^([a-zA-Z0-9_-]){1,' . $maxChars . '}$/';
        return preg_match($pattern, $string) === 1;
    }

    public static function isPositiveMaxPrecision($number, $maxPrecision) {
        if ($number < 0) {
            // not positive, return
            return false;
        }
        $string = (string) $number;
        $stringParts = explode('.', $string);
        if (isset($stringParts[1]) && strlen($stringParts[1]) > $maxPrecision) {
            return false;
        }
        return true;
    }

    /*
     * NOTE: Unit tests would cover all of the methods in this class.
     * I'll implement unit tests for a representative sample of the
     * specified field validations.
     * -- testRequiredFields
     * -- testAlphaNumMaxChars
     * -- testPositivePrecision
     */
}