<?php
namespace Api\Test\Models;

use Api\Config\Database;
use Api\Models\ProductsTable;
use PHPUnit\Framework\TestCase;

class ProductsTableTest extends TestCase
{
    private $db;
    private $ProductsTable;

    public function setUp() {

        parent::setUp();
        $database = new Database(true);
        $this->db = $database->getDbConnection();

        // create clean test data
        $statement = <<<ENDQUERY
INSERT INTO products
    (id, sku, alt_sku, merchant_id, description, unit_price, weight, length, height, is_active, quantity)
VALUES
    (1000, '1234567890abcdef', null, 99, 'Test product for unit tests.', 1.99, 0.1234, 1.1111, 1.1111, 1, 10),
    (1001, '2234567890abcdef', null, 99, 'Test product for unit tests.', 1.99, 0.1234, 1.1111, 1.1111, 1, 10),
    (1002, '3234567890abcdef', '4234567890abcdef', 99, 'Test product for unit tests.', 1.99, 0.1234, 1.1111, 1.1111, 1, 10),
    (1003, 'inactive', 'altinactive', 99, 'Test product for unit tests.', 1.99, 0.1234, 1.1111, 1.1111, 0, 10)
ENDQUERY;
        $statement = $this->db->prepare($statement);
        $statement->execute();

        $this->ProductsTable = new ProductsTable(true);
    }

    public function testFindAll() {
        $products = $this->ProductsTable->findAll();
        $this->assertEquals(3, count($products));
    }

    public function testFind() {
        $product = $this->ProductsTable->find(1000);
        $this->assertEquals('1000', $product['id']);
    }

    public function testCreateProductSkuEqualsAltSku() {
        $product = [
            ProductsTable::SKU => 'abc',
            ProductsTable::ALT_SKU => 'abc',
            ProductsTable::MERCHANT_ID => 123,
            ProductsTable::DESCRIPTION => 'abc',
            ProductsTable::UNIT_PRICE => 123,
            ProductsTable::WEIGHT => 123,
            ProductsTable::LENGTH => 123,
            ProductsTable::HEIGHT => 123
        ];
        $result = $this->ProductsTable->createProduct($product);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(1, count($result['errors']));
        $this->assertEquals([
            ProductsTable::ALT_SKU => 'sku and alt sku must be different'
        ], $result['errors']);
    }

    public function testCreateProductNonUniqueSku() {
        $product = [
            ProductsTable::SKU => '1234567890abcdef',
            ProductsTable::MERCHANT_ID => 99,
            ProductsTable::DESCRIPTION => 'abc',
            ProductsTable::UNIT_PRICE => 123,
            ProductsTable::WEIGHT => 123,
            ProductsTable::LENGTH => 123,
            ProductsTable::HEIGHT => 123
        ];
        $result = $this->ProductsTable->createProduct($product);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(1, count($result['errors']));
        $this->assertEquals([
            ProductsTable::SKU => 'sku already exists'
        ], $result['errors']);
    }

    public function testCreateProductExistingSkuButInactiveProduct() {
        $product = [
            ProductsTable::SKU => 'inactive',
            ProductsTable::MERCHANT_ID => 99,
            ProductsTable::DESCRIPTION => 'abc',
            ProductsTable::UNIT_PRICE => 123,
            ProductsTable::WEIGHT => 123,
            ProductsTable::LENGTH => 123,
            ProductsTable::HEIGHT => 123
        ];
        $result = $this->ProductsTable->createProduct($product);
        $this->assertNotEmpty($result[ProductsTable::ID]);
    }

    public function testCreateProductNonUniqueAltSku() {
        $product = [
            ProductsTable::SKU => 'abc',
            ProductsTable::ALT_SKU => '4234567890abcdef',
            ProductsTable::MERCHANT_ID => 99,
            ProductsTable::DESCRIPTION => 'abc',
            ProductsTable::UNIT_PRICE => 123,
            ProductsTable::WEIGHT => 123,
            ProductsTable::LENGTH => 123,
            ProductsTable::HEIGHT => 123
        ];
        $result = $this->ProductsTable->createProduct($product);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(1, count($result['errors']));
        $this->assertEquals([
            ProductsTable::ALT_SKU => 'alt_sku already exists'
        ], $result['errors']);
    }

    public function testCreateProduct() {
        $product = [
            ProductsTable::SKU => 'xyz',
            ProductsTable::ALT_SKU => '123',
            ProductsTable::MERCHANT_ID => 99,
            ProductsTable::DESCRIPTION => 'abc',
            ProductsTable::UNIT_PRICE => 123,
            ProductsTable::WEIGHT => 123,
            ProductsTable::LENGTH => 123,
            ProductsTable::HEIGHT => 123
        ];
        $product = $this->ProductsTable->createProduct($product);
        $this->assertNotEmpty($product[ProductsTable::ID]);
        // NOTE: this is inconsistent with the update which only
        // returns true/false.
    }

    public function testUpdateProductNonUniqueSkus() {
        $product = [
            ProductsTable::ID => 1001,
            ProductsTable::SKU => '3234567890abcdef',
            ProductsTable::ALT_SKU => '4234567890abcdef',
            ProductsTable::MERCHANT_ID => 99,
            ProductsTable::DESCRIPTION => 'abc',
            ProductsTable::UNIT_PRICE => 123,
            ProductsTable::WEIGHT => 123,
            ProductsTable::LENGTH => 123,
            ProductsTable::HEIGHT => 123
        ];
        $product = $this->ProductsTable->updateProduct($product);
        $this->assertEquals([
            'errors' => [
                'sku' => 'sku already exists',
                'alt_sku' => 'alt_sku already exists'
            ]
        ], $product);
    }

    public function testUpdateProduct() {
        // 1002, '3234567890abcdef', '4234567890abcdef', 99, 'Test product for unit tests.', 1.99, 0.1234, 1.1111, 1.1111, 1, 10)
        $product = [
            ProductsTable::ID => 1002,
            ProductsTable::SKU => '3234567890abcdef',
            ProductsTable::ALT_SKU => '4234567890abcdef',
            ProductsTable::MERCHANT_ID => 99,
            ProductsTable::DESCRIPTION => 'Test product for unit tests.',
            ProductsTable::UNIT_PRICE => 1.99,
            ProductsTable::WEIGHT => 0.1234,
            ProductsTable::LENGTH => 1.1111,
            ProductsTable::HEIGHT => 1.1112,
            ProductsTable::QUANTITY => 10
        ];
        $updatedProduct = $this->ProductsTable->updateProduct($product);
        $this->assertEquals(1.1112, $updatedProduct[ProductsTable::HEIGHT]);
    }

    public function testDeleteNonExistantProduct() {
        $result = $this->ProductsTable->deleteProduct(1001111);
        $this->assertEquals([
            'errors' => 'product does not exist'
        ], $result);
    }

    public function testDeleteProduct() {
        $result = $this->ProductsTable->deleteProduct(1001);
        $this->assertEquals(0, $result[ProductsTable::IS_ACTIVE]);
    }

    public function testValidateProductRequiredField() {
        $product = [
            ProductsTable::SKU => 'abc',
            ProductsTable::MERCHANT_ID => 123,
            ProductsTable::DESCRIPTION => 'abc',
            ProductsTable::UNIT_PRICE => 123,
            ProductsTable::WEIGHT => 123,
            ProductsTable::LENGTH => 123
        ];
        $expectedErrors = [
            ProductsTable::HEIGHT => 'required field'
        ];
        $this->assertEquals($expectedErrors,
            $this->ProductsTable->getValidationErrors($product));
    }

    public function testIsAlphNumMaxChars() {
        // validate valid case
        $this->assertTrue(ProductsTable::isAlphaNumMaxChars('abc123', 6));
        // validate non alpha num - special char
        $this->assertFalse( ProductsTable::isAlphaNumMaxChars( 'a!c', 3));
        // validate non alpha num - space
        $this->assertFalse(ProductsTable::isAlphaNumMaxChars('a c', 3));
        // validate too many chars
        $this->assertFalse(ProductsTable::isAlphaNumMaxChars('abc', 1));
    }

    public function testIsPositiveMaxPrecision() {
        $this->assertTrue(
            ProductsTable::isPositiveMaxPrecision(1.000, 1));
        $this->assertFalse(
            ProductsTable::isPositiveMaxPrecision(1.12, 1));
        $this->assertTrue(
            ProductsTable::isPositiveMaxPrecision(.1, 1));
        $this->assertFalse(
            ProductsTable::isPositiveMaxPrecision(-1.0, 1));
    }

    public function tearDown() {
        parent::tearDown();
        // TODO: clean up test products
        // normally, the test run against a diff database
        $statement = "
            DELETE FROM products WHERE merchant_id = 99
        ";
        $statement = $this->db->prepare($statement);
        $statement->execute();
    }
}