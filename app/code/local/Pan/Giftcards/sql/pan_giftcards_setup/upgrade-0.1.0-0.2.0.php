<?php
/**
 * Simple module for updating system configuration data.
 *
 * @category    Pan
 * @package     Pan_Updater
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * -----------------------------------
 * Add default attribute options for Gift Card Type (wts_gc_type)
 * -----------------------------------
 */
// Get the eav attribute model
$attrModel     = Mage::getModel('catalog/resource_eav_attribute');

// Load the particular attribute by attribute_code
$attrCode   = 'wts_gc_type';
$attr       = $attrModel->loadByCode('catalog_product', $attrCode);
$attrId     = $attr->getAttributeId();
$storeId    = 0; // default admin store

$optionsToAdd = array('email', 'print', 'offline');

// syntax to add attribute options to existing attribute
$option = array('attribute_id' => $attrId, 'value' => array());

foreach ($optionsToAdd as $optionVal) {
   $option['value'][$optionVal] = array($optionVal);
}

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttributeOption($option);

$installer->endSetup();
