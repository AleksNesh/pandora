<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('giftwrap_selection')} ADD `addressgift_id` int(11) NULL;
	ALTER TABLE {$this->getTable('giftwrap_selection')} ADD `addresscustomer_id` int(11) NULL;
");

$installer->endSetup(); 
