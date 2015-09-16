<?php
/**
 * Pan_JewelryDesigner Extension
 *
 * @category  Pan
 * @package   Pan_JewelryDesigner
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

$installer = $this;

$installer->startSetup();

$createDesignsTableSql = <<<DESIGNS_TABLE_SQL
DROP TABLE IF EXISTS {$this->getTable('pan_jewelrydesigner/design')};
CREATE TABLE IF NOT EXISTS {$this->getTable('pan_jewelrydesigner/design')} (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`customer_id` INT(11) UNSIGNED DEFAULT NULL COMMENT "Frontend Customer ID",
`admin_user_id` INT(11) UNSIGNED DEFAULT NULL COMMENT "Admin User ID typically for Inspiration Bracelets",
`jewelery_type` VARCHAR(20) NOT NULL COMMENT "Example: 'bracelet', 'watch', 'necklace', 'earring'",
`name` VARCHAR(255) NOT NULL COMMENT "User-defined common name for the design",
`configuration` TEXT DEFAULT NULL COMMENT "Serialized or JSON encoded string of design's configuration (order of items, etc.)",
`price`  DECIMAL(10,4) DEFAULT 0 COMMENT "Cached price of the design based off all the items prices and quantities",
`is_inspiration_design` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Identifies 'Inspiration' items (e.g., bracelets). Only available to admin users",
`is_available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT "Small workflow to control whether this item is viewable to the public or not",
`cloned_from_design_id` INT(11) UNSIGNED DEFAULT NULL COMMENT "Self reference to this table's :id column",
`times_cloned` INT(11) UNSIGNED DEFAULT 0 COMMENT "Counter cache for the number of times this design has been cloned",
`created_at` DATETIME NULL COMMENT "Time of creation",
`updated_at` DATETIME NULL COMMENT "Time of last update",
PRIMARY KEY (`id`)
) engine=InnoDB default charset=utf8;
DESIGNS_TABLE_SQL;

$createDesignItemsTableSql = <<<DESIGN_ITEMS_TABLE_SQL
DROP TABLE IF EXISTS {$this->getTable('pan_jewelrydesigner/design_item')};
CREATE TABLE IF NOT EXISTS {$this->getTable('pan_jewelrydesigner/design_item')} (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`jewelery_design_id` INT(11) UNSIGNED NOT NULL COMMENT "Foreign Key reference to the pan_jewelery_designs.id column",
`product_id` INT(11) UNSIGNED NOT NULL COMMENT "Magento Product ID",
`instances` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT "Number of instances of product that is part of the design",
`quantity_owned` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT "Quantity of product that is already owned by the customer and should be excluded from price totals",
`quantity` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT "Quantity of items to order (instances - quantity_owned)",
`unit_price` DECIMAL(10,4) NOT NULL COMMENT "Product's price for individual unit",
`total_price` DECIMAL(10,4) NOT NULL DEFAULT 0 COMMENT "Calculates unit_price x quantity",
`is_bracelet` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Helps identify if the product is a bracelet item",
`is_charm` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Helps identify if the product is a charm item",
`is_clip` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Helps identify if the product is a clip item",
`is_already_owned` TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Helps customer identify items and qty they already own",
`created_at` DATETIME NULL COMMENT "Time of creation",
`updated_at` DATETIME NULL COMMENT "Time of last update",
PRIMARY KEY (`id`),
INDEX (jewelery_design_id, product_id),
FOREIGN KEY (jewelery_design_id)
    REFERENCES {$this->getTable('pan_jewelrydesigner/design')}(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
FOREIGN KEY (product_id)
    REFERENCES {$this->getTable('catalog/product')}(entity_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) engine=InnoDB default charset=utf8;
DESIGN_ITEMS_TABLE_SQL;


$installer->run($createDesignsTableSql);
$installer->run($createDesignItemsTableSql);

// end transaction
$installer->endSetup();
