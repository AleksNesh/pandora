<?php

/**
 * Extend/Override Queldorei_ShopperSettings module
 *
 * @category    Pan_Queldorei
 * @package     Pan_Queldorei_ShopperSettings
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pan_Queldorei_ShopperSettings_Helper_Data extends Queldorei_ShopperSettings_Helper_Data
{
    /**
     * AAI OVERRIDE
     *
     * Add logic to check is_retired product attribute
     * and return appropriate HTML
     *
     * @var     Mage_Catalog_Model_Product         $product
     * @return  string
     * @author  Josh Johnson (August Ash, Inc.)
     */
    public function getLabel(Mage_Catalog_Model_Product $product)
    {
        if ('Mage_Catalog_Model_Product' != get_class($product))
            return;

        $html = '';

        // added `&& !$this->getCfg('labels/retired_label')`
        if ( !$this->getCfg('labels/new_label') && !$this->getCfg('labels/sale_label') && !$this->getCfg('labels/retired_label') ) {
            return $html;
        }
        if ($this->getCfg('labels/new_label') && $this->_isNew($product)) {
            $html .= '<div class="new-label new-' . $this->getCfg('labels/new_label_position') . '"></div>';
        }
        if ($this->getCfg("labels/sale_label") && $this->_isOnSale($product)) {
            $html .= '<div class="sale-label sale-' . $this->getCfg('labels/sale_label_position') . '"></div>';
        }
        // added this block of logic
        if ($this->getCfg('labels/retired_label') && $this->_isRetired($product)) {
            $html .= '<div class="retired-label retired-' . $this->getCfg('labels/retired_label_position') . '"></div>';
        }

        return $html;
    }

    /**
     * return a boolean value based off the product's is_retired attribute
     *
     * @var     Mage_Catalog_Model_Product  $product
     * @return  bool
     * @author  Josh Johnson (August Ash, Inc.)
     */
    protected function _isRetired(Mage_Catalog_Model_Product $product)
    {
        $retired = false;
        try {
            $retired = $product->getData('is_retired');
        } catch (Exception $e) {
            Mage::log('FROM CLASS ' . __CLASS__ . ' IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
            Mage::log($e->getMessage());
        }

        return $retired;
    }
}
