<?php
$installer = $this;
$installer->startSetup();

$installer->run("

	ALTER TABLE {$this->getTable('sales_flat_order')} ADD `giftwrap_amount` DECIMAL( 12, 4 );
	
	ALTER TABLE {$this->getTable('sales_flat_order')} ADD `giftwrap_tax` DECIMAL( 12, 4 ) NOT NULL default '0';
	
	ALTER TABLE {$this->getTable('giftwrap')}  
		ADD  `store_id` smallint(5) unsigned NOT NULL,
		ADD  `option_id` int(11) NOT NULL default '0',
		ADD  `default_title` tinyint(1) NOT NULL default '1',
		ADD  `default_price` tinyint(1) NOT NULL default '1',
		ADD  `default_image` tinyint(1) NOT NULL default '1',
		ADD  `default_sort_order` tinyint(1) NOT NULL default '1',
		ADD  `default_personal_message` tinyint(1) NOT NULL default '1',
		ADD  `default_status` tinyint(1) NOT NULL default '1',
		ADD  `default_character` tinyint(1) NOT NULL default '1',
		ADD INDEX ( `store_id` ), 
		ADD FOREIGN KEY ( `store_id` ) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE	
	;	

");
		
$installer->endSetup(); 