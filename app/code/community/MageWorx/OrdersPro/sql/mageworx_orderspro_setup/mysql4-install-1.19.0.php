<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2011 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
@ini_set('max_execution_time', 1800);
$installer = $this;

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// 1.19.0
if ($installer->tableExists('orderspro_order_group') && !$installer->tableExists($this->getTable('mageworx_orderspro_order_group'))) {
    $installer->run("RENAME TABLE orderspro_order_group TO {$this->getTable('mageworx_orderspro_order_group')};");
}
if ($installer->tableExists('orderspro_upload_files') && !$installer->tableExists($this->getTable('mageworx_orderspro_upload_files'))) {
    $installer->run("RENAME TABLE orderspro_upload_files TO {$this->getTable('mageworx_orderspro_upload_files')};");
}

// 1.0.0
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('mageworx_orderspro_order_group')};
CREATE TABLE IF NOT EXISTS {$this->getTable('mageworx_orderspro_order_group')} ( 
  `order_group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  PRIMARY KEY (`order_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OrdersPro Group';


INSERT IGNORE INTO {$this->getTable('mageworx_orderspro_order_group')} (`order_group_id`, `title`) VALUES
(1, 'Archived'),
(2, 'Deleted');
  
-- DROP TABLE IF EXISTS {$this->getTable('mageworx_orderspro_upload_files')};
CREATE TABLE IF NOT EXISTS {$this->getTable('mageworx_orderspro_upload_files')} (
  `entity_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `history_id` int(10) unsigned NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY `IDX_HISTORY` (`history_id`),  
  CONSTRAINT `FK_ORDERSPRO_HISTORY_ID` FOREIGN KEY (`history_id`) REFERENCES `{$this->getTable('sales_flat_order_status_history')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OrdersPro Upload Files';
");

// 1.6.4 > 1.7.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'customer_email')) {

    $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` ADD `is_edited` tinyint(1) NOT NULL DEFAULT 0;");

    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}`
        ADD `customer_email` varchar(255) DEFAULT NULL,
        ADD `customer_group_id` smallint(5) DEFAULT NULL,
        ADD `tax_amount` decimal(12,4) NOT NULL DEFAULT 0,
        ADD `total_qty_ordered` decimal(12,4) NOT NULL DEFAULT 0,
        ADD `discount_amount` decimal(12,4) NOT NULL DEFAULT 0,
        ADD `coupon_code` varchar(255) DEFAULT NULL,
        ADD `total_refunded` decimal(12,4) NOT NULL DEFAULT 0,
        ADD `shipping_method` varchar(255) NOT NULL DEFAULT '',
        ADD `is_edited` tinyint(1) NOT NULL DEFAULT 0;");

    $installer->run("UPDATE `{$this->getTable('sales/order_grid')}` AS sog, `{$this->getTable('sales/order')}` AS so
        SET
            sog.`customer_email` = so.`customer_email`,
            sog.`customer_group_id` = so.`customer_group_id`,
            sog.`tax_amount` = IFNULL(so.`tax_amount`, 0),
            sog.`total_qty_ordered` = IFNULL(so.`total_qty_ordered`, 0),
            sog.`discount_amount` = IFNULL(so.`discount_amount`, 0),
            sog.`coupon_code` = so.`coupon_code`,
            sog.`total_refunded` = IFNULL(so.`total_refunded`, 0),
            sog.`shipping_method` = IFNULL(so.`shipping_method`, '')
        WHERE sog.`entity_id` = so.`entity_id`");
}

// 1.11.3 > 1.12.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'base_total_refunded')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}`
        ADD `base_tax_amount` decimal(12,4) NOT NULL DEFAULT 0,
        ADD `base_discount_amount` decimal(12,4) NOT NULL DEFAULT 0,
        ADD `base_total_refunded` decimal(12,4) NOT NULL DEFAULT 0;");

    $installer->run("UPDATE `{$this->getTable('sales/order_grid')}` AS sog, `{$this->getTable('sales/order')}` AS so
        SET
            sog.`base_tax_amount` = IFNULL(so.`base_tax_amount`, 0),
            sog.`base_discount_amount` = IFNULL(so.`base_discount_amount`, 0),
            sog.`base_total_refunded` = IFNULL(so.`base_total_refunded`, 0)
        WHERE sog.`entity_id` = so.`entity_id`");
}

if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'base_customer_credit_amount')) {
    if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'base_customer_credit_amount')) {
        $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD `base_customer_credit_amount` decimal(12,4) NOT NULL DEFAULT 0;");
    }
    $installer->run("UPDATE `{$this->getTable('sales/order_grid')}` AS sog, `{$this->getTable('sales/order')}` AS so
        SET sog.`base_customer_credit_amount` = IFNULL(so.`base_customer_credit_amount`, 0)
        WHERE sog.`entity_id` = so.`entity_id`");
}

if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'customer_credit_amount')) {
    if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'customer_credit_amount')) {
        $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD `customer_credit_amount` decimal(12,4) NOT NULL DEFAULT 0;");
    }
    $installer->run("UPDATE `{$this->getTable('sales/order_grid')}` AS sog, `{$this->getTable('sales/order')}` AS so
        SET sog.`customer_credit_amount` = IFNULL(so.`customer_credit_amount`, 0)
        WHERE sog.`entity_id` = so.`entity_id`");
}

// 1.12.0 > 1.12.1
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'shipping_description')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD `shipping_description` varchar(255) NOT NULL DEFAULT '';");
    $installer->run("UPDATE `{$this->getTable('sales/order_grid')}` AS sog, `{$this->getTable('sales/order')}` AS so
        SET sog.`shipping_description` = IFNULL(so.`shipping_description`, '')
        WHERE sog.`entity_id` = so.`entity_id`");
}

// 1.12.3 > 1.13.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'weight')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD `weight` decimal(12,4) DEFAULT NULL;");
    $installer->run("UPDATE `{$this->getTable('sales/order_grid')}` AS sog, `{$this->getTable('sales/order')}` AS so
        SET sog.`weight` = so.`weight` WHERE sog.`entity_id` = so.`entity_id`");
}

// 1.17.6 > 1.17.7
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'order_group_id')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('sales/order_grid'),
        'order_group_id',
        'tinyint(3) UNSIGNED NOT NULL DEFAULT 0'
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'order_group_id')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('sales/order'),
        'order_group_id',
        'tinyint(3) UNSIGNED NOT NULL DEFAULT 0'
    );

    if ($installer->getConnection()->showTableStatus($installer->getTable('mageworx_orderspro_order_item_group'))) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $select = $connection->select()->from($installer->getTable('mageworx_orderspro_order_item_group'), array('order_id', 'order_group_id'));
        $allOrderGroups = $connection->fetchAll($select);

        if ($allOrderGroups) {
            $installer->run('LOCK TABLES '. $connection->quoteIdentifier($installer->getTable('sales/order_grid'), true) .' WRITE;');
            foreach($allOrderGroups as $value) {
                $connection->update($installer->getTable('sales/order_grid'), array('order_group_id' => intval($value['order_group_id'])), 'entity_id = '. intval($value['order_id']));
            }
            $installer->run('UNLOCK TABLES;');


            $installer->run('LOCK TABLES '. $connection->quoteIdentifier($installer->getTable('sales/order'), true) .' WRITE;');
            foreach($allOrderGroups as $value) {
                $connection->update($installer->getTable('sales/order'), array('order_group_id' => intval($value['order_group_id'])), 'entity_id = '. intval($value['order_id']));
            }
            $installer->run('UNLOCK TABLES;
                DROP TABLE IF EXISTS `'. $installer->getTable('mageworx_orderspro_order_item_group') .'`;
                ALTER TABLE `'. $installer->getTable('mageworx_orderspro_order_group') .'` CHANGE `order_group_id` `order_group_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT;');
        }
    }
}

// 1.18.9 > 1.19.0
$pathLike = 'mageworx_sales/orderspro/%';
$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->getSelect()->where('path like ?', $pathLike);

foreach ($configCollection as $conf) {
    $path = $conf->getPath();
    $path = str_replace('orderspro', 'general', $path);
    $path = str_replace('mageworx_sales', 'mageworx_orderspro', $path);
    $conf->setPath($path)->save();
}

$salesInstaller = new Mage_Sales_Model_Resource_Setup('core_setup');
$salesInstaller->addAttribute(
    'quote_item',
    'orderspro_is_temporary',
    array(
        'type' => 'int',
        'nullable' => true,
        'grid' => false,
    )
);
$salesInstaller->endSetup();

$installer->endSetup();
