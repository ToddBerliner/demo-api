<?php
namespace Api\Models;

use Api\Config\Database;

class ProductsTable {

    private $db;
    private $requestMethod;
    private $productId;

    const ID = 'id';
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
        self::ID,
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
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
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

        // create specific errors
        if (isset($product[self::ID])) {
            return ['errors' => [self::ID => 'product already exists']];
        }

        // validate product, return errors if any
        $errors = $this->getValidationErrors($product);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // try to insert the new record
        $statement = <<<ENDQUERY
INSERT INTO products
    (sku, alt_sku, merchant_id, description, unit_price, weight, length, height, quantity)
VALUES
    (:sku, :alt_sku, :merchant_id, :description, :unit_price, :weight, :length, :height, :quantity)
ENDQUERY;

        // try insert query and return new product id
        try {
            $statement = $this->db->prepare($statement);
            // :sku, :alt_sku, :merchant_id, :description, :unit_price, :weight, :length, :height, :quantity)
            if ($statement->execute([
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
                    ? $product[self::QUANTITY] : 0
            ])) {
                $product[self::ID] = $this->db->lastInsertId();
                return $product;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            return ['errors' => ['db' => $e->getMessage()]];
        }
    }

    public function updateProduct($product) {
        // validate updated fields
        $errors = $this->getValidationErrors($product);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // get dirty fields to update
        $dirtyFields = $this->_getDirtyFields($product);
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // build update statement
        $setStatements = [];
        $values = [];
        foreach($dirtyFields as $field) {
            $setStatements[] = $field . ' = :' . $field;
            $values[$field] = $product[$field];
        }
        $setStatements = implode(',', $setStatements);
        $statement = "
            UPDATE products
            SET
                $setStatements
        ";

        // try update query and return true
        try {
            $statement = $this->db->prepare($statement);
            if ($statement->execute($values)) {
                return $product;
            } else {
                return false;
            };
        } catch (\PDOException $e) {
            return ['errors' => ['db' => $e->getMessage()]];
        }
    }

    public function deleteProduct($productId) {
        $product = $this->find($productId);
        if (!$product) {
            return ['errors' => 'product does not exist'];
        }
        $statement = "
            UPDATE products
            SET is_active = 0
            WHERE product_id = :productId
        ";
        try {
            $statement = $this->db->prepare($statement);
            if ($statement->execute(['productId' => $productId])) {
                $product[self::IS_ACTIVE] = 0;
                return $product;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            return ['errors' => ['db' => $e->getMessage()]];
        }
    }

    private function _getDirtyFields($product) {
        // get existing product
        $existingProduct = $this->find($product[self::ID]);
        if (!$existingProduct) {
            return [self::ID => 'product does not exist'];
        }
        $dirtyFields = [];
        foreach($existingProduct as $field => $value) {
            if (in_array($field, self::PUBLIC_FIELDS)
                && $product[$field] != $value) {
                $dirtyFields[] = $field;
            }
        }
        return $dirtyFields;
    }

    // Validation Functions
    // Validate CREATE and UPDATE
    public function getValidationErrors($product) {
        $errors = [];

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

        // validate unique sku/alt_sku separately so the error
        // message can be specific
        if(!$this->validateUniqueSkus($product)) {
            $errors[self::SKU] = 'sku already exists';
        };
        if (isset($product[self::ALT_SKU])
            && !$this->validateUniqueSkus($product, self::ALT_SKU)) {
            $errors[self::ALT_SKU] = 'alt_sku already exists';
        }

        return $errors;
    }

    public function validateUniqueSkus($product, $field = 'sku') {
        // NOTE: I expected to be able to detect unique key constraint
        // failures, but got error codes that weren't what i expected, so
        // I moved on. I've most frequently done unique key checks in code
        // anyway since those kind of requirements could change.
        $statement = "
            SELECT * 
            FROM
                products
            WHERE 
                merchant_id = :merchantId AND $field = :value
        ";
        $values = [
            'merchantId' => $product[self::MERCHANT_ID],
            'value' => $product[$field]
        ];
        // If product id is set, we're trying an update and
        // therefore need to check that the field is not found
        // in any other records.
        if (isset($product[self::ID])) {
            $statement .= "
                AND id != :productId
            ";
            $values['productId'] = $product[self::ID];
        }
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute($values);
            return $statement->rowCount() === 0;
        } catch (\PDOException $e) {
            return ['errors' => ['db' => $e->getMessage()]];
        }
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