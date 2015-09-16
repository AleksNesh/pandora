<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `" . $this->getTable('xtento_orderexport_profile') . "`
ADD `export_action_invoice_order` INT(1) NOT NULL DEFAULT '0' AFTER `export_action_change_status`,
ADD `export_action_invoice_notify` INT(1) NOT NULL DEFAULT '0' AFTER `export_action_invoice_order`,
ADD `export_action_ship_order` INT(1) NOT NULL DEFAULT '0' AFTER `export_action_invoice_notify`,
ADD `export_action_ship_notify` INT(1) NOT NULL DEFAULT '0' AFTER `export_action_ship_order`;
");

$installer->endSetup();