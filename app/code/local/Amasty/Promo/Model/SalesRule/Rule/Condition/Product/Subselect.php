<?php
/**
 * @copyright   Copyright (c) 2009-14 Amasty
 */


class Amasty_Promo_Model_SalesRule_Rule_Condition_Product_Subselect
    extends Mage_SalesRule_Model_Rule_Condition_Product_Subselect
{
    /**
     * Validate items total amount or total qty
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getQuote()->getAllItems() as $item) { 
            // fix magento bug
            if ($item->getParentItemId()){
                continue;
            }
            
            // for bundle we need to add a loop here
            // if we treat them as set of separate items
            
            if (@Amasty_Promo_Model_SalesRule_Rule_Condition_Product_Combine::validate($item)){
                $total += $item->getData($attr);                
            }                 
        }
        
        return $this->validateAttribute($total);
    }
}
