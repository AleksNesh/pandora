<?php


/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS `{$installer->getTable("snap_card/charge")}`(
    `charge_id` VARCHAR(64) NOT NULL,
    `card_code` VARCHAR(64) NOT NULL,
    `card_pin` VARCHAR(255) DEFAULT NULL,
    `is_holding` INT(6) NOT NULL DEFAULT 0,
    `is_charged` INT(6) NOT NULL DEFAULT 0,
    `is_returned` INT(6) NOT NULL DEFAULT 0,
    `is_error` INT(6) NOT NULL DEFAULT 0,
    `hold_transaction_id` VARCHAR(64) DEFAULT NULL,
    `amount` DECIMAL(12,4) NOT NULL,
    `value_code` VARCHAR(64) NOT NULL,
    `customer_id` INT(6) DEFAULT NULL,
    `order_id` INT(6) DEFAULT NULL,
    `quote_id` VARCHAR(64) NOT NULL,
    `client_addr` VARCHAR(64) NOT NULL,
    `created_at` DATETIME DEFAULT '0000-00-00 00:00:00',
    `last_modified_at` DATETIME DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`charge_id`),
    INDEX (`card_code`),
    INDEX (`customer_id`),
    INDEX (`order_id`),
    INDEX (`quote_id`),
    INDEX (`client_addr`),
    INDEX (`created_at`),
    INDEX (`last_modified_at`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;");

$installer->endSetup();
