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
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

$installer = $this;
$installer->startSetup();

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

$installer->endSetup();