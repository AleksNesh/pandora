<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
$this->startSetup();

$this->run("
  ALTER TABLE  `{$this->getTable('amtable/rate')}`  ADD  `shipping_type` INT( 10 ) NOT NULL DEFAULT '0' AFTER  `qty_to`

");

$this->run("
ALTER TABLE  `{$this->getTable('amtable/rate')}` DROP INDEX  `method_id` ,
ADD UNIQUE  `method_id` (  `method_id` ,  `country` ,  `state` ,  `city` ,  `zip_from` ,  `zip_to` ,  `price_from` ,  `price_to` ,  `weight_from` ,  `weight_to` ,  `qty_from` ,  `qty_to` ,  `shipping_type` )
   ");

$this->endSetup();


$installer = $this;

$installer->startSetup();

/**
* ADDING FIRST ATTRIBUTE
*/       
$installer->addAttribute('catalog_product', 'am_shipping_type', array(
    'type'              => 'varchar',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Shipping Type',
    'input'             => 'select',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '0',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => '',
    'is_configurable'   => false
));
$attributeId = $installer->getAttributeId('catalog_product', 'am_shipping_type');

foreach ($installer->getAllAttributeSetIds('catalog_product') as $attributeSetId) 
{
    try {
        $attributeGroupId = $installer->getAttributeGroupId('catalog_product', $attributeSetId, 'General');
    } catch (Exception $e) {
        $attributeGroupId = $installer->getDefaultAttributeGroupId('catalog_product', $attributeSetId);
    }
    $installer->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
}

$installer->endSetup(); 
