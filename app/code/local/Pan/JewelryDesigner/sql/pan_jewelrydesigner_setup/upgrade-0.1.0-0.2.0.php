<?php
/**
 * Pan_JewelryDesigner Extension
 *
 * @category  Pan
 * @package   Pan_JewelryDesigner
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

$installer = new Mage_Eav_Model_Entity_Setup();

// start transaction
$installer->startSetup();

// get the type ID of the product - you will need it later
$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();

// get all attribute sets (filter only sets for products)
$sets = Mage::getModel('eav/entity_attribute_set')
    ->getResourceCollection()
    ->addFilter('entity_type_id', $entityTypeId);

//loop through all the sets
foreach ($sets as $set) {
    // create an attribute group instance
    $modelGroup = Mage::getModel('eav/entity_attribute_group');

    // set the group name
    $modelGroup->setAttributeGroupName('Jewelry Designer')
        ->setAttributeSetId($set->getId())
        ->setSortOrder(25);

    // save the new group
    $modelGroup->save();
}

/**
 * Create Jewelry Designer specific attributes and
 * assign to our 'Jewelry Designer' attribute group
 */

// Media Image attributes
$imgAttrs = array('designer_canvas', 'designer_grid_thumb');

foreach ($imgAttrs as $attrCode) {
    $data = array(
        'group'                     => 'Images',
        'input'                     => 'media_image',
        'label'                     => ucwords(str_replace('_', ' ', $attrCode)),
        'visible'                   => true,
        'required'                  => false,
        'visible_on_front'          => false,
        'user_defined'              => true,
        'used_in_product_listing'   => true,
        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    );

    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, $data);
}


/**
 * Product Type (i.e., 'bracelet', 'charm', 'clip', 'spacer', etc.)
 */
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'item_type',
    array(
        'group'                     => 'Jewelry Designer',
        'type'                      => 'varchar',
        'input'                     => 'select',
        'label'                     => 'Item Type',
        'visible'                   => true,
        'required'                  => false,
        'visible_on_front'          => false,
        'user_defined'              => true,
        'used_in_product_listing'   => true,
        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'source'                    => 'eav/entity_attribute_source_table',
        'option'                    => array(
            'values' => array(
                'bracelet'  => 'bracelet',
                'charm'     => 'charm',
                'clip'      => 'clip',
                'spacer'    => 'spacer'
            )
        ),

    )
);


/**
 * Bead Width (bead_width)
 */
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'bead_width',
    array(
        'group'                     => 'Jewelry Designer',
        'type'                      => 'varchar',
        'input'                     => 'select',
        'label'                     => 'Bead/Charm Size (Width)',
        'visible'                   => true,
        'required'                  => false,
        'visible_on_front'          => false,
        'user_defined'              => true,
        'used_in_product_listing'   => true,
        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'source'                    => 'eav/entity_attribute_source_table',
        'option'                    => array(
            'values' => array(
                'tiny'      => 'tiny',
                'small'     => 'small',
                'medium'    => 'medium',
                'large'     => 'large'
            )
        ),
    )
);

/**
 * Exclude From Designer (exclude_from_designer)
 */
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'exclude_from_designer',
    array(
        'group'                     => 'Jewelry Designer',
        'input'                     => 'boolean',
        'label'                     => 'Exclude from Designer App',
        'visible'                   => true,
        'required'                  => false,
        'visible_on_front'          => false,
        'user_defined'              => true,
        'default_value'             => false,
        'used_in_product_listing'   => true,
        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
);



// end transaction
$installer->endSetup();
