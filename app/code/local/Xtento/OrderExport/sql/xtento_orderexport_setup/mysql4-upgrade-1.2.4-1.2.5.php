<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `" . $this->getTable('xtento_orderexport_profile') . "`
ADD `manual_export_enabled` INT(1) NOT NULL DEFAULT '1' AFTER `save_files_manual_export`;
");

$installer->endSetup();