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
Get all products: /api/products
Get a single product: /api/product/{$productId}
```
LEFT OFF HERE...
