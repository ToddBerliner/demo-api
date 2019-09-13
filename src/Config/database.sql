# Required database and user
CREATE DATABASE shipwire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tberliner'@'localhost' identified by 'tberliner';
GRANT ALL on shipwire.* to 'tberliner'@'localhost';

# Required table
CREATE TABLE `products` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `sku` varchar(16) NOT NULL,
    `alt_sku` varchar(16) DEFAULT NULL,
    `merchant_id` int(11) unsigned NOT NULL,
    `description` varchar(250) NOT NULL,
    `unit_price` decimal(10,2) NOT NULL,
    `weight` decimal(10,4) NOT NULL,
    `length` decimal(10,4) NOT NULL,
    `height` decimal(10,4) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `quantity` int(11) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE `merchant_sku` (`merchant_id`, `sku`), # enforce uniqueness across merchant
    UNIQUE `merchant_altsku` (`merchant_id`, `alt_sku`) # enforce uniqueness across merchant
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;