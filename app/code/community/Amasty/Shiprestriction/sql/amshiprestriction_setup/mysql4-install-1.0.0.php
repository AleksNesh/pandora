<?php
/**
* @copyright Amasty.
*/
$this->startSetup();
$this->run("
CREATE TABLE `{$this->getTable('amshiprestriction/rule')}` (
  `rule_id`     mediumint(8) unsigned NOT NULL auto_increment,
  `is_active`   tinyint(1) unsigned NOT NULL default '0',
  `all_stores`  tinyint(1) unsigned NOT NULL default '0',
  `all_groups`  tinyint(1) unsigned NOT NULL default '0',
  `name`        varchar(255) default '', 
  `stores`      varchar(255) NOT NULL default '', 
  `cust_groups` varchar(255) NOT NULL default '', 
  `message`     varchar(255) default '', 
  `carriers`    text, 
  `methods`     text, 
  `conditions_serialized`   text, 
  
  PRIMARY KEY  (`rule_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$this->endSetup();