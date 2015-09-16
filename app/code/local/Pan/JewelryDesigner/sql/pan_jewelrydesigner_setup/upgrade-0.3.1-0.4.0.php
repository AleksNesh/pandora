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

/**
 * Is Dangle Charm (is_dangle_charm)
 *
 * Helps identify beads that are "danglers" b/c their
 * registration point should be closer to the top than
 * the center of the image
 */
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'is_dangle_charm',
    array(
        'group'                     => 'Jewelry Designer',
        'input'                     => 'boolean',
        'label'                     => 'Is Dangle Charm?',
        'visible'                   => true,
        'required'                  => false,
        'visible_on_front'          => false,
        'user_defined'              => true,
        'default_value'             => false,
        'used_in_product_listing'   => true,
        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
);

/**
 * Bracelet has spots for clips? (bracelet_has_clip_spots)
 *
 * Helps identify bracelets that may not have spots for clips
 * and therefore do not have exclusion zones
 */
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'bracelet_has_clip_spots',
    array(
        'group'                     => 'Jewelry Designer',
        'input'                     => 'boolean',
        'label'                     => 'Bracelet has spots for clips?',
        'visible'                   => true,
        'required'                  => false,
        'visible_on_front'          => false,
        'user_defined'              => true,
        'default_value'             => true,
        'used_in_product_listing'   => true,
        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
);

// end transaction
$installer->endSetup();
