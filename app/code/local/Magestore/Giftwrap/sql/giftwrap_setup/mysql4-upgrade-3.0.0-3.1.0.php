<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('giftwrap_item')};
	CREATE TABLE {$this->getTable('giftwrap_item')} (
		`selection_item_id` int(11) unsigned NOT NULL auto_increment,
	  	`selection_id` int(11) unsigned NOT NULL,
	  	`item_id` int(11) NOT NULL,
	  	`qty` int(11) NOT NULL default '1',
	  	INDEX(`selection_id`),
	  	FOREIGN KEY (`selection_id`) REFERENCES `{$this->getTable('giftwrap_selection')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	  	PRIMARY KEY (`selection_item_id`)
	)ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	
	ALTER TABLE  `giftwrap_selection` DROP COLUMN  `item_id`;
	
	ALTER TABLE  `giftwrap_selection` ADD  `qty` int(11) NOT NULL default '1';
	ALTER TABLE  `giftwrap_selection` ADD 	`type` smallint(6) NOT NULL default '1';
");
$installer->endSetup(); 