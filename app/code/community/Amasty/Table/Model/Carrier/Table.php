<?php
/**
 * @copyright Amasty 2012
 */

class Amasty_Table_Model_Carrier_Table extends Mage_Shipping_Model_Carrier_Abstract
{
    protected $_code = 'amtable';

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) 
    {
        if (!$this->getConfigData('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $collection = Mage::getResourceModel('amtable/method_collection')
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($request->getStoreId())
            ->addCustomerGroupFilter($this->getCustomerGroupId($request))
            ->setOrder('pos'); 
                            
        $rates = Mage::getModel('amtable/rate')->findBy($request, $collection);     
        foreach ($collection as $customMethod){
            
            // create new instance of method rate
            $method = Mage::getModel('shipping/rate_result_method');
    
            // record carrier information
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
    
            // record method information
            $method->setMethod($this->_code . $customMethod->getId());
            $method->setMethodTitle(Mage::helper('amtable')->__($customMethod->getName()));
    
            if (isset($rates[$customMethod->getId()]))
            {
                    $method->setCost($rates[$customMethod->getId()]);
                    $method->setPrice($rates[$customMethod->getId()]);

                    // add this rate to the result
                    $result->append($method);        
            }
        }
        return $result;
    } 


    public function getAllowedMethods()
    {
        $collection = Mage::getResourceModel('amtable/method_collection')
                ->addFieldToFilter('is_active', 1)
                ->setOrder('pos');
        $arr = array();
        foreach ($collection as $method){
            $methodCode = 'amtable'.$method->getMethodId();
            $arr[$methodCode] = $method->getName();    
        }  
                
        return $arr;
    }
    
    public function getCustomerGroupId($request)
    {
        $allItems = $request->getAllItems();
        if (!$allItems){
            return 0;
        }
        foreach ($allItems as $item)
        {
            return $item->getProduct()->getCustomerGroupId();             
        }

    }
}
