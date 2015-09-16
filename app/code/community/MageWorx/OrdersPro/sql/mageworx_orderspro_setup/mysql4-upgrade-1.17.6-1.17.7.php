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
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
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
$installer->startSetup();

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


$installer->endSetup();