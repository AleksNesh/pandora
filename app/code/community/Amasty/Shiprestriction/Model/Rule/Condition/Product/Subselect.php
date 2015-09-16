<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Shiprestriction_Model_Rule_Condition_Product_Subselect extends Mage_SalesRule_Model_Rule_Condition_Product_Subselect
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('amshiprestriction/rule_condition_product_subselect')
            ->setValue(null);
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(array(
            'qty'             => $hlp->__('total quantity'),
            'base_row_total'  => $hlp->__('total amount'),
            'row_weight'      => $hlp->__('total weight'),
        ));
        return $this;
    }

    /**
     * validate
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
        
        if ($object->getItemsToValidateRestrictions()){
            foreach ($object->getItemsToValidateRestrictions() as $item) {
                //can't use parent here
                if (Mage_SalesRule_Model_Rule_Condition_Product_Combine::validate($item)) {
                    $total += $item->getData($attr);
                }
            }
        }

        return $this->validateAttribute($total);
    }
}