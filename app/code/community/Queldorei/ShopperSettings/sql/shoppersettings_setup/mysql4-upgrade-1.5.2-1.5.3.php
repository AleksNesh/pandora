<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();
$installer->setConfigData('shopperbrands/main/status', '1');
$installer->setConfigData('shopperbrands/main/pages', '1');
$installer->setConfigData('shopperbrands/main/brands', '0');
$installer->setConfigData('shopperbrands/main/attribute', 'manufacturer');
$installer->setConfigData('shopperbrands/main/image', 'png');
$installer->setConfigData('shopperbrands/main/image_width', '96');
$installer->endSetup();