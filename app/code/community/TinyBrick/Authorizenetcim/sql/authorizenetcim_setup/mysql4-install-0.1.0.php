<?php

$installer = $this;

$installer->startSetup();

$installer->run("
		
CREATE TABLE {$this->getTable('tinybrick_authorizenetcim_ccsave')} (
	`tinybrick_authorizenetcim_ccsave_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`customer_id` int(11) NOT NULL DEFAULT '0',
	`cc_type` varchar(30) NOT NULL DEFAULT '',
	`cc_last4` varchar(50) NOT NULL DEFAULT '',
	`cc_exp_month` varchar(20) NOT NULL DEFAULT '',
	`cc_exp_year` varchar(30) NOT NULL DEFAULT '',
	`token_profile_id` int(11) NOT NULL DEFAULT '0',
	`token_payment_profile_id` int(11) NOT NULL DEFAULT '0',
	`token_shipping_address_id` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`tinybrick_authorizenetcim_ccsave_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `token_profile_id` int( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `token_payment_profile_id` int( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `token_shipping_address_id` int( 11 ) NOT NULL DEFAULT '0';
 
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `token_profile_id` int( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `token_payment_profile_id` int( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `token_shipping_address_id` int( 11 ) NOT NULL DEFAULT '0';	
				");
		
$installer->endSetup();