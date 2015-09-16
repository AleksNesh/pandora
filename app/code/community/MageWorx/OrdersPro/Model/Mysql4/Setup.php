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

class MageWorx_OrdersPro_Model_Mysql4_Setup extends Mage_Core_Model_Resource_Setup
{
    // $params - ADD CONSTRAINT `FK_SALES_FLAT_ORDER_ADDRESS_PARENT` FOREIGN KEY (`parent_id`) REFERENCES `sales_flat_order` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    public function addForeinKey($table, $params) {
        $this->run('CREATE TABLE `'.$table.'_old` LIKE `'.$table.'`;
            INSERT IGNORE `'.$table.'_old` SELECT * FROM `'.$table.'`;
            SET FOREIGN_KEY_CHECKS = 0;    
            DROP TABLE IF EXISTS `'.$table.'`;
            CREATE TABLE `'.$table.'` LIKE `'.$table.'_old`;
            SET FOREIGN_KEY_CHECKS = 1;
            ALTER TABLE `'.$table.'` '.$params.';
            INSERT IGNORE `'.$table.'` SELECT * FROM `'.$table.'_old`;
            DROP TABLE IF EXISTS `'.$table.'_old`;');
    }

}