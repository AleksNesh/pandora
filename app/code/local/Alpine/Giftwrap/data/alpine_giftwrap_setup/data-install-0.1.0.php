<?php
/**
 * Installer for Giftwrap module
 *
 * @category    Alpine
 * @package     Alpine_Giftwrap
 * @copyright   Copyright (c) 2015 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Enabling module
 */
$config = Mage::getModel('core/config');
$config->saveConfig('giftwrap/general/active', 1);

/**
 * Adding attribute to all attribute sets
 */
$setup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$attribute = $setup->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'giftwrap');

$entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
    ->setEntityTypeFilter($entityType->getId());

foreach ($collection as $attributeSet) {
    $attributeGroupId = $setup->getDefaultAttributeGroupId(
        Mage_Catalog_Model_Product::ENTITY,
        $attributeSet->getId()
    );
    $setup->addAttributeToSet(
        Mage_Catalog_Model_Product::ENTITY,
        $attributeSet->getId(),
        $attributeGroupId,
        $attribute['attribute_id']
    );
}

$installer->endSetup();