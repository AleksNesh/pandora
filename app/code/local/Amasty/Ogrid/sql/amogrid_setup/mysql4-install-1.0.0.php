<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amogrid/order_item')}` (
  `ogrid_item_id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT(8) UNSIGNED NOT NULL UNIQUE,
  PRIMARY KEY  (`ogrid_item_id`),
  KEY `IND_AM_OGRID_ORDER_ITEM_ID` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amogrid/order_item_product')}` (
    `item_id` INT(10) UNSIGNED NOT NULL,
    `product_id` INT(10) UNSIGNED DEFAULT NULL,
    `store_id` SMALLINT(5) UNSIGNED DEFAULT NULL,
    KEY `item_id` (`item_id`),
    KEY `product_id` (`product_id`),
    KEY `store_id` (`store_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;
");

$this->endSetup(); 