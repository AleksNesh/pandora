<?php
/**
 * Boost cachability by enabling block-level cache on strategic core Magneto
 * blocks
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @copyright   Copyright (c) 2015 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Product List model
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Cacheboost_Model_Type_Catalog_Product_List
    extends Ash_Cacheboost_Model_Type_Catalog_Product_Abstract
{
    /**
     * Get cache key informative items
     *
     * @return  array
     */
    public function getCacheKeyInfo()
    {
        $keys   = $this->getBlock()->getCacheKeyInfo() + parent::getDefaultKeys();
        $keys[] = (int)Mage::app()->getStore()->isCurrentlySecure();
        $keys[] = Mage::app()->getStore()->getCurrentCurrencyCode();
        $keys[] = (Mage::getSingleton('customer/session')->isLoggedIn() ? 'loggedin' : 'loggedout');
        $keys[] = $this->getBlock()->getNameInLayout();
        $keys[] = 'category_' . Mage::app()->getRequest()->getParam('id');
        $keys[] = $this->_generateCategoryKey('catalog_category_list');

        return $keys;
    }

    /**
     * Get tags array for saving cache
     *
     * @return  array
     */
    public function getCacheTags()
    {
        $tags   = parent::getDefaultTags();
        $tags[] = Mage_Catalog_Model_Product::CACHE_TAG;
        $tags[] = Mage_Catalog_Model_Category::CACHE_TAG . '_' . Mage::app()->getRequest()->getParam('id');

        return $tags;
    }
}
