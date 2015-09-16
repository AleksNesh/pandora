<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("

CREATE TABLE `{$this->getTable('amshiprestriction/attribute')}` (
  `attr_id` mediumint(8) unsigned NOT NULL auto_increment,
  `rule_id` mediumint(8) unsigned NOT NULL,
  `code`    varchar(255) NOT NULL default '',
 
  PRIMARY KEY  (`attr_id`),
  CONSTRAINT `FK_SHIPRESTRICTION_RULE` FOREIGN KEY (`rule_id`) REFERENCES {$this->getTable('amshiprestriction/rule')} (`rule_id`) ON DELETE CASCADE ON UPDATE CASCADE 
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 

ALTER TABLE `{$this->getTable('amshiprestriction/rule')}`  ADD `out_of_stock` TINYINT NOT NULL AFTER `is_active`;
 
"); 

$this->endSetup();