<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('sales_flat_quote')} ADD `admin_giftwrap_amount` DECIMAL( 12, 4 ) ;

	ALTER TABLE {$this->getTable('sales_flat_quote')} ADD `admin_giftwrap_tax` DECIMAL( 12, 4 ) NOT NULL default '0';
");

$installer->endSetup(); 
