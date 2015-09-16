<?php
$installer = $this;
$installer->startSetup();
$installer->run("

	DROP TABLE IF EXISTS {$this->getTable('giftwrap_giftcard')};
	CREATE TABLE {$this->getTable('giftwrap_giftcard')} (
		  `giftcard_id` int(11) unsigned NOT NULL auto_increment,
		  `status` smallint(6) NOT NULL default '0',
		  `name` varchar(255) NOT NULL default '',
		  `image` varchar(255) NULL default '',
		  `price` DECIMAL(12,4) NOT NULL default '0',
		  `store_id` smallint(5) unsigned NOT NULL,
		  `message` text NULL,
		  `character` int(10) NOT NULL default '0',
	  	  `option_id` int(11) NOT NULL default '0',
		  `default_name` tinyint(1) NOT NULL default '1',
		  `default_price` tinyint(1) NOT NULL default '1',
		  `default_image` tinyint(1) NOT NULL default '1',
		  `default_sort_order` tinyint(1) NOT NULL default '1',
		  `default_message` tinyint(1) NOT NULL default '1',
		  `default_status` tinyint(1) NOT NULL default '1',
		  `default_character` tinyint(1) NOT NULL default '1',
	  INDEX(`store_id`),
	  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	  PRIMARY KEY (`giftcard_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	ALTER TABLE {$this->getTable('giftwrap_selection')}  
		ADD COLUMN `giftcard_id` int(11) unsigned NULL,
		ADD INDEX ( `giftcard_id` ), 
		ADD FOREIGN KEY ( `giftcard_id` ) REFERENCES {$this->getTable('giftwrap_giftcard')} (`giftcard_id`) ON DELETE CASCADE ON UPDATE CASCADE	
	;
	
	ALTER TABLE  `giftwrap_selection` CHANGE  `item_id`  `item_id` VARCHAR( 255 ) NOT NULL DEFAULT  '';
	
");
$installer->endSetup(); 