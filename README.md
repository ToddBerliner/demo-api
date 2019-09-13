# Demo CRUD API for Products #
Demo API for take home coding challenge.

> Installation

This demo requires Composer, PHP 5.3+ and a MySQL instance as well as an apache compatible vhost with the DocumentRoot set to the "public" directory. I've used a .htaccess file with a rewrite rule to enable the pretty URLs such as /api/products/10. 

Please run```composer install``` to install the dependancies.

Please execute the SQL commands in /src/config/database.sql to create the database, api user and the required table and seed data. Please update /src/config/Database.php as necessary if you need to change the port or other details.

> Tests

Tests can be run from the project root with the command:  
```./vendor/bin/phpunit --bootstrap vendor/autoload.php tests```

> Usage

The API expects calls to be made as follows:
```
Get all products(GET): /api/products
Get a single product (GET): /api/products/<product_id>
Create a product (POST): /api/products
Update a product (PUT): /api/products/<product_id>
Delete a product (DELETE): /api/products/<product_id>
```

> Expected Payload for a Product
```
For a PUT request, add the "id" property ("id": 1)
{
	"product": {
		"sku": "helloworld",
		"alt_sku": null,
		"merchant_id": 1,
		"description": "hello world!",
		"unit_price": 1.255555555,
		"weight": 25.0001,
		"length": 12.2323,
		"height": 23.1212,
		"quantity": 1000
	}
}
```

> CURL Sample
```
curl -X POST \
  http://localhost:2323/api/products \
  -d '{
	"product": {
		"sku": "helloworld",
		"alt_sku": null,
		"merchant_id": 1,
		"description": "hello world!",
		"unit_price": 1.25,
		"weight": 25.0001,
		"length": 12.2323,
		"height": 23.1212,
		"quantity": 1000
	}
}'
