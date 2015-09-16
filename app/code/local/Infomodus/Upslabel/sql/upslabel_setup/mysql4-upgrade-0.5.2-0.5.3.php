<?php
$installer = $this;

/* @var $installer Mage_Sales_Model_Entity_Setup */

$installer->startSetup();
/*DROP TABLE IF EXISTS {$this->getTable('upslabelprice')};*/
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('upslabelprice')} (
  `upslabelprice_id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) NOT NULL default 0,
  `shipment_id` int(11) NOT NULL default 0,
  `price` varchar(50) NOT NULL default '',
  PRIMARY KEY (`upslabelprice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

