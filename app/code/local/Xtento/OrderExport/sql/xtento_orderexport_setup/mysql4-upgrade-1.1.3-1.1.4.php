<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  `" . $this->getTable('xtento_orderexport_profile') . "` ADD  `export_filter_product_type` VARCHAR( 255 ) NOT NULL AFTER  `customer_groups`;
");

$installer->endSetup();