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

$installer->endSetup();