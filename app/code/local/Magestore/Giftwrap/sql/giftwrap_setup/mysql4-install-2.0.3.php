<?php
$setup =  new Mage_Eav_Model_Entity_Setup('core_setup');
$installer = $this;
$installer->startSetup();

//Giftwrap attribute
$entity_type = Mage::getSingleton("eav/entity_type")->loadByCode("catalog_product");
$entity_type_id = $entity_type->getId();
$collection = Mage::getModel("eav/entity_attribute")
			->getCollection()
			->addFieldToFilter("entity_type_id",$entity_type_id)
			->addFieldToFilter("attribute_code","giftwrap");
			
if(!count($collection))
{
	$data = array(
		'group'	=>	'General',
		'type'	=>	'int',
		'input'	=>	'select',
		'label'	=>	'Wrappable',
                'apply_to'      => 'simple,bundle,configurable',
		'backend'	=>	'',
		'frontend'	=>	'',
		'source'	=> 'giftwrap/attribute_wrappable',
		'visible'	=>	1,
		'required'	=>	1,
		'user_defined'	=>	1,
		'is_searchable'	=>	1,
		'is_filterable'	=>	0,
		'is_comparable'	=>	1,
		'is_visible_on_front'	=>	0,
		'is_visible_in_advanced_search'	=> 1,
		'used_for_sort_by'	=> 0,
		'used_in_product_listing'	=> 1,
		'used_for_price_rules'	=> 1,
		'is_used_for_promo_rules'	=> 1,
		'position'	=> 2,
		'unique'	=>	0,
		'is_configurable'	=>	1,
		'default'	=> 0,
		'is_global'	=>	Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
	);

	$setup->addAttribute('catalog_product','giftwrap',$data);

	$entity_type_id = $setup->getEntityTypeId('catalog_product');
	$data['entity_type_id'] = $entity_type_id;
	$attribute = Mage::getModel("eav/entity_attribute")
		->setData($data)
		->setId($setup->getAttributeId('catalog_product','giftwrap'));
	$attribute->save();
}

$installer->run("

	ALTER TABLE {$this->getTable('sales_flat_order')} ADD `giftwrap_amount` DECIMAL( 12, 4 ) ;

	ALTER TABLE {$this->getTable('sales_flat_order')} ADD `giftwrap_tax` DECIMAL( 12, 4 ) NOT NULL default '0';
	
	DROP TABLE IF EXISTS {$this->getTable('giftwrap')};
	CREATE TABLE {$this->getTable('giftwrap')} (
	  `giftwrap_id` int(11) unsigned NOT NULL auto_increment,
	  `title` varchar(255) NOT NULL default '',
	  `price` DECIMAL(12,4) NOT NULL default '0',
	  `image` varchar(255) NULL default '',
	  `sort_order` int(10) NOT NULL default '0',  
	  `personal_message` smallint(6) NOT NULL default '0',
	  `status` smallint(6) NOT NULL default '0',
	  `character` int(10) NOT NULL default '0',
	  `store_id` smallint(5) unsigned NOT NULL,
	  `option_id` int(11) NOT NULL default '0',
	  `default_title` tinyint(1) NOT NULL default '1',
	  `default_price` tinyint(1) NOT NULL default '1',
	  `default_image` tinyint(1) NOT NULL default '1',
	  `default_sort_order` tinyint(1) NOT NULL default '1',
	  `default_personal_message` tinyint(1) NOT NULL default '1',
	  `default_status` tinyint(1) NOT NULL default '1',
	  `default_character` tinyint(1) NOT NULL default '1',
	  INDEX(`store_id`),
	  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
	  PRIMARY KEY (`giftwrap_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    
	DROP TABLE IF EXISTS {$this->getTable('giftwrap_selection')};
	CREATE TABLE {$this->getTable('giftwrap_selection')} (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `quote_id` int(10) unsigned NOT NULL,
	  `item_id` int(11) NOT NULL,
	  `style_id` int(11) unsigned NOT NULL,
	  `message` text NULL,
	  INDEX(`style_id`),
	  FOREIGN KEY (`style_id`) REFERENCES `{$this->getTable('giftwrap')}` (`giftwrap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
		
$installer->endSetup(); 