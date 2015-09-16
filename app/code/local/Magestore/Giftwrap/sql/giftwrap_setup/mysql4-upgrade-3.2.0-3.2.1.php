<?php
$setup =  new Mage_Eav_Model_Entity_Setup('core_setup');
$installer = $this;
$installer->startSetup();
$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'giftwrap');
if ($attributeId) {
    $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
    $attribute->setSourceModel('eav/entity_attribute_source_boolean')->save();
}

$installer->endSetup();
