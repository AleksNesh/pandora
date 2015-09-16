<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('giftwrap_item')} ADD `check_reorder` int(11) ;	
");

$installer->endSetup(); 
