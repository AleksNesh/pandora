<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  `" . $this->getTable('xtento_orderexport_profile') . "` ADD  `export_one_file_per_object` INT(1) NOT NULL DEFAULT  '0' AFTER `customer_groups`;
");

$installer->endSetup();