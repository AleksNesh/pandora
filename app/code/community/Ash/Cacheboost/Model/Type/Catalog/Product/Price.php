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
 * Catalog Product Price model
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Cacheboost_Model_Type_Catalog_Product_Price
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
        $keys[] = parent::getParamKey('p') . parent::getParamKey('limit');
        $keys[] = 'product_' . $this->getProductId();
        $keys[] = 'catalog_product_price';

        return $keys;
    }
}
