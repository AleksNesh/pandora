<?php

/**
 * Core module for providing common functionality between BraceletBuilder and other related submodules
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_JewelryDesigner_Model_Api_Bracelets extends Pan_JewelryDesigner_Model_Api_Abstract
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setItemType('bracelet');


        $catIds     = array();
        $excluded   = array(
            'Essence Bracelets',
            // 'Leather Bracelets',
            // 'Color Cord Bracelets',
            'Clip Station Bracelets',
            'Inspirational Bracelets',
        );
        foreach ($excluded as $catName) {
            $cat = Mage::getModel('catalog/category')->loadByAttribute('name', $catName);
            if ($cat) {
                $catIds[] = $cat->getId();
            }
        }

        // update the $_excludedCategoryIds with the category ids we need to ignore
        $this->setExcludedCategoryIds($catIds);
    }
}
