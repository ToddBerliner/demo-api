<?php
namespace Api\Test\Models;

use Api\Models\ProductsTable;
use PHPUnit\Framework\TestCase;

class ProductsTableTest extends TestCase
{
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
            ProductsTable::getValidationErrors($product));
    }

    public function testInvalidProduct() {
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
        $ProductsTable = new ProductsTable();
        $result = $ProductsTable->createProduct($product);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(1, count($result['errors']));
    }

    public function testInvalidProductNonUniqueSku() {
        // Need additional test to ensure database enforcement
        // of unique merchant_id_sku (assume merchant_id_alt_sku
        // also works)
        $this->markTestSkipped('TODO');
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

    public function __destruct() {
        // TODO: clean up test products
        // normally, the test run against a diff database
    }
}