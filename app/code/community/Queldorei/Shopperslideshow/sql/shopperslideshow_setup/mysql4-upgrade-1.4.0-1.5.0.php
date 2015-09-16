<?php
/**
 * @version   1.0 12.0.2012
 * @author    queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 queldorei
 */

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('shopperslideshow/revolution_slides'),
	'link_target', 'varchar(8) DEFAULT "_self" AFTER link');

$installer->endSetup();
