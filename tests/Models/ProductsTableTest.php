<?php
namespace Api\Test\Models;

use Api\Models\ProductsModel;
use PHPUnit\Framework\TestCase;

class ProductsTableTest extends TestCase
{
    public function testSanity() {
        $foo = 'baz';
        $this->assertEquals('baz', $foo);
    }
}