<?php

$installer = $this;

$installer->startSetup();

$installer->run("
		
-- DROP TABLE IF EXISTS `{$this->getTable('oc_teo_authorizations')}`;

CREATE TABLE `{$this->getTable('oc_teo_authorizations')}` (
	`authorization_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`order_id` int(11) DEFAULT NULL,
 	`authorization_number` VARCHAR( 100 ) DEFAULT NULL ,
 	`type` VARCHAR( 100 ) DEFAULT NULL ,
 	`authorization_amount` DECIMAL( 12, 4 ) DEFAULT NULL ,
 	`amount_paid` DECIMAL( 12, 4 ) DEFAULT NULL ,
 	`amount_refunded` DECIMAL( 12, 4 ) DEFAULT NULL ,
	PRIMARY KEY (  `authorization_id` )
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		
				");
		
$installer->endSetup();