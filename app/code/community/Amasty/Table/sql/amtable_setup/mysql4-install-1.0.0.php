<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amtable/method')}` (
  `method_id`  mediumint(8) unsigned NOT NULL auto_increment,
  `is_active`   tinyint(1) unsigned NOT NULL default '0',
  `pos`         mediumint  unsigned NOT NULL default '0',
  `name`        varchar(255) default '', 
  `stores`      varchar(255) NOT NULL default '', 
  `cust_groups` varchar(255) NOT NULL default '', 
  PRIMARY KEY  (`method_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amtable/rate')}` (
  `rate_id`     int(10) unsigned NOT NULL auto_increment,
  `method_id`   mediumint(8) unsigned NOT NULL,
 
  `country`     varchar(4)  NOT NULL default '',
  `state`       int(10)     NOT NULL default '0',  
  `city`        varchar(12) NOT NULL default '',  
  
  `zip_from`    varchar(10) NOT NULL default '',
  `zip_to`      varchar(10) NOT NULL default '', 
  
  `price_from`  decimal(12,2) unsigned NOT NULL default '0',
  `price_to`    decimal(12,2) unsigned NOT NULL default '0',

  `weight_from` decimal(12,4) unsigned NOT NULL default '0',
  `weight_to`   decimal(12,4) unsigned NOT NULL default '0',

  `qty_from`    int(10) unsigned NOT NULL default '0',
  `qty_to`      int(10) unsigned NOT NULL default '0', 
  
  `cost_base`      decimal(12,2) unsigned NOT NULL default '0',
  `cost_percent`   decimal(5,2)  unsigned NOT NULL default '0',
  `cost_product`   decimal(12,2) unsigned NOT NULL default '0',
  
  PRIMARY KEY  (`rate_id`),
  UNIQUE KEY(`method_id`, `country`, `state` , `city`, `zip_from`, `zip_to`,  `price_from`, `price_to`, `weight_from`, `weight_to`, `qty_from`, `qty_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup();