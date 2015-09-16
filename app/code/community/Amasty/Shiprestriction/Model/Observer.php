<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Shiprestriction_Model_Observer
{
    protected $_allRules = null;
    
    public function restrictRates($observer) 
    {
        $request = $observer->getRequest();
        $result  = $observer->getResult();

        $rates = $result->getAllRates();
        if (!count($rates)){
            return $this;
        }
            
        $rules = $this->_getRestrictionRules($request);
        if (!count($rules)){
             return $this;
        }
        
        $result->reset();
        
        $isEmptyResult = true;
        $lastError     = Mage::helper('amshiprestriction')->__('Sorry, no shipping quotes are available for the selected products and destination');
        $lastRate      = null;
        
        foreach ($rates as $rate){
            $isValid = true;
            foreach ($rules as $rule){
                if ($rule->restrict($rate)){
                    $lastRate  = $rate;
                    $lastError = $rule->getMessage();
                    $isValid   = false;
                    break;
                }
            }
            if ($isValid){
                $result->append($rate);
                $isEmptyResult = false;                    
            }
        }
        
        if ($isEmptyResult){
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($lastRate->getCarrier());
            $error->setCarrierTitle($lastRate->getMethodTitle());
            $error->setErrorMessage($lastError); 
            
            $result->append($error);           
        }
        
        return $this;
    }
   
    protected function _getRestrictionRules($request)
    {
        $all = $request->getAllItems();
        if (!$all){
            return array();
        }
        $firstItem = current($all);
        $address = $firstItem->getAddress();
        if (!$address){
            $quote = $firstItem->getQuote();     
            if (!$quote) { return array(); } // we need it for true order editor

            $address = $quote->getShippingAddress(); 
        }
        $address->setItemsToValidateRestrictions($request->getAllItems());
        
       
        //multishipping optimization
        if (is_null($this->_allRules)){
            $this->_allRules = Mage::getModel('amshiprestriction/rule')
                ->getCollection()
                ->addAddressFilter($address)
            ;
            if ($this->_isAdmin()){
                $this->_allRules->addFieldToFilter('for_admin', 1);
            }                
            
            $this->_allRules->load();
            foreach ($this->_allRules as $rule){
                $rule->afterLoad(); 
            }                
        }
        
        $hasBackOrders = false;
        foreach ($request->getAllItems() as $item){
            if ($item->getBackorders() > 0 ){
                $hasBackOrders = true;
                break;
            }
        }

	// remember old                 
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();
        // set new
        $this->_modifySubtotal($address);

            
        $validRules = array();
        foreach ($this->_allRules as $rule){
            $valid = $rule->getOutOfStock() ? $hasBackOrders : true;
            if ($valid && $rule->validate($address)){
                $validRules[] = $rule;
            }
        }

        // restore
        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);
        
        return $validRules;                
    } 


    protected function _modifySubtotal($address)
    {
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();

        $includeTax = Mage::getStoreConfig('amshiprestriction/general/tax');
        if ($includeTax){
           $subtotal += $address->getTaxAmount();
           $baseSubtotal += $address->getBaseTaxAmount(); 
        }
        
        $includeDiscount = Mage::getStoreConfig('amshiprestriction/general/discount');
        if ($includeDiscount){
           $subtotal += $address->getDiscountAmount();
           $baseSubtotal += $address->getBaseDiscountAmount(); 
        } 
                 
        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);

	return true;
    }
 
    
    protected function _isAdmin()
    {
        if (Mage::app()->getStore()->isAdmin())
            return true;
        // for some reason isAdmin does not work here
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order_create')
            return true;
            
        return false;
    }        

    
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

        $attributes = Mage::getResourceModel('amshiprestriction/rule')->getAttributes();
        
        $result = array();
        foreach ($attributes as $code) {
            $result[$code] = true;
        }
        $attributesTransfer->addData($result);
        
        return $this;
    }       
    
}