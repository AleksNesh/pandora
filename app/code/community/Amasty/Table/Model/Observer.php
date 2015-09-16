<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Table_Model_Observer
{
    /**
     * Append rule product attributes to select by quote item collection
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_SalesRule_Model_Observer
     */
    public function addProductAttributes(Varien_Event_Observer $observer)
    {
        // @var Varien_Object
        $attributesTransfer = $observer->getEvent()->getAttributes();
        
        $result = array();
        $result['am_shipping_type'] = true;
        $attributesTransfer->addData($result);
        
        return $this;
    }       
    
}