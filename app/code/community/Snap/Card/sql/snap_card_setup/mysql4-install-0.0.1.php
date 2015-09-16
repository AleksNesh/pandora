<?php
/**
 * Install data tables
 *
 * @category    Snap
 * @package     Snap_Card
 * @author      alex
 */

/** @var $installer Mage_Sales_Model_Resource_Setup */
$installer = new Mage_Sales_Model_Resource_Setup();

$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS `{$installer->getTable('snap_card/entity')}`(
    `entity_id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(32) NOT NULL,
    `pin` INT(8) DEFAULT NULL,
    `status` VARCHAR(64) NOT NULL DEFAULT 'Activated',
    `total` DECIMAL(12,4) NOT NULL DEFAULT '0.0000',
    `balance` DECIMAL(12,4) NOT NULL DEFAULT '0.0000',
    `customer_id` INT(6) DEFAULT NULL,
    `created_at` DATETIME DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`entity_id`),
    UNIQUE KEY `IDX_CODE` (`code`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;");

$installer->run("CREATE TABLE IF NOT EXISTS `{$installer->getTable('snap_card/usage')}`(
  `entity_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `card_id` int(6) NOT NULL,
  `website_id` smallint(5) unsigned NOT NULL,
  `amount` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `customer_id` int(6) DEFAULT NULL,
  `order_id` int(6) DEFAULT NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");


$installer->endSetup();