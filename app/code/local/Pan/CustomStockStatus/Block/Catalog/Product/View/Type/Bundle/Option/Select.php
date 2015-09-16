<?php

/**
 * Extend/Override CJM_CustomStockStatus module
 *
 * @category    Pan
 * @package     Pan_CustomStockStatus
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_CustomStockStatus_Block_Catalog_Product_View_Type_Bundle_Option_Select extends CJM_CustomStockStatus_Block_Catalog_Product_View_Type_Bundle_Option_Select
{

    /**
     * Retrieve default values for template
     *
     * =========================================================================
     * AAI OVERRIDE:
     *
     * Overrides Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option
     * method to give better logic for determining if the user can change
     * the Qty by looking at the 'User Defined Qty'
     *
     * @see  Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option
     * =========================================================================
     *
     * @return array
     */
    protected function _getDefaultValues()
    {
        $_option            = $this->getOption();
        $_default           = $_option->getDefaultSelection();
        $_selections        = $_option->getSelections();
        $selectedOptions    = $this->_getSelectedOptions();

        $inPreConfigured    = $this->getProduct()->hasPreconfiguredValues()
            && $this->getProduct()->getPreconfiguredValues()
                    ->getData('bundle_option_qty/' . $_option->getId());

        if (empty($selectedOptions) && $_default) {
            $_defaultQty = $_default->getSelectionQty() * 1;
            $_canChangeQty = $_default->getSelectionCanChangeQty();
        } elseif (!$inPreConfigured && $selectedOptions && is_numeric($selectedOptions)) {
            $selectedSelection = $_option->getSelectionById($selectedOptions);
            $_defaultQty = $selectedSelection->getSelectionQty() * 1;
            $_canChangeQty = $selectedSelection->getSelectionCanChangeQty();
        } elseif (!$this->_showSingle() || $inPreConfigured) {
            /**
             * BEGIN AAI HACK
             *
             * + default the $_defaultQty to 1 if it comes back as 0,
             *     otherwise use the value from the
             *     Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option::_getSelectedQty()
             *
             * + defaults the $_canChangeQty to whatever the first option
             *     has for 'User Defined Qty' because the $_defaultQty was 0
             *     and for some reason (bool)0 ended up being empty, which was
             *     pretty pointless b/c it wasn't a meaningful return value
             */

            // $_defaultQty = $this->_getSelectedQty();
            $_defaultQty = ($this->_getSelectedQty() < 1) ? 1 : $this->_getSelectedQty() * 1;

            // $_canChangeQty = (bool)$_defaultQty;
            $_canChangeQty = $_selections[0]->getSelectionCanChangeQty();

            /**
             * END AAI HACK
             */

        } else {
            $_defaultQty = $_selections[0]->getSelectionQty() * 1;
            $_canChangeQty = $_selections[0]->getSelectionCanChangeQty();
        }

        return array($_defaultQty, $_canChangeQty);
    }

}
