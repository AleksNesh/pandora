<?php

$installer = $this;

$installer->startSetup();

$installer->run("
		
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `guest_id` int( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `guest_id` int( 11 ) NOT NULL DEFAULT '0';

-- DROP TABLE IF EXISTS `{$this->getTable('oc_authorizenetcim_guest')}`;

CREATE TABLE `{$this->getTable('oc_authorizenetcim_guest')}` (
	`guest_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(255) DEFAULT NULL,
	PRIMARY KEY (  `guest_id` )
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		
ALTER TABLE oc_authorizenetcim_guest AUTO_INCREMENT = 152952; 
		
				");
		
$installer->endSetup();
