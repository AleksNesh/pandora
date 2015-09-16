<?php
$installer = $this;

/* @var $installer Mage_Sales_Model_Entity_Setup */

$installer->startSetup();
/*DROP TABLE IF EXISTS {$this->getTable('upslabelprice')};*/
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('upslabelconformity')} (
  `upslabelconformity_id` int(11) unsigned NOT NULL auto_increment,
  `method_id` varchar(50) NOT NULL default '',
  `upsmethod_id` varchar(50) NOT NULL default '',
  `store_id` int(11) NOT NULL default 1,
  PRIMARY KEY (`upslabelconformity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

