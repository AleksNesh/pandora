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

if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_grid'), 'shipping_description')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD `shipping_description` varchar(255) NOT NULL DEFAULT '';");
    $installer->run("UPDATE `{$this->getTable('sales/order_grid')}` AS sog, `{$this->getTable('sales/order')}` AS so
        SET sog.`shipping_description` = IFNULL(so.`shipping_description`, '')
        WHERE sog.`entity_id` = so.`entity_id`");        
}

$installer->endSetup();