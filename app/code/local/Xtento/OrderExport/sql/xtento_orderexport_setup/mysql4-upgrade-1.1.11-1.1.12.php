<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `" . $this->getTable('xtento_orderexport_profile') . "`
ADD `output_type` VARCHAR(255) NOT NULL DEFAULT 'xsl' AFTER `cronjob_custom_frequency`,
ADD `filename` VARCHAR(255) NOT NULL AFTER `output_type`,
ADD `encoding` VARCHAR(255) NOT NULL AFTER `filename`;

ALTER TABLE `" . $this->getTable('xtento_orderexport_destination') . "`
ADD `email_attach_files` INT(1) NOT NULL DEFAULT 1 AFTER `email_body`;
");

$installer->endSetup();