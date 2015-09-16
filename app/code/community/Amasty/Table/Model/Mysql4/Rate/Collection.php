<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Table_Model_Mysql4_Rate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amtable/rate');
    }
    
    public function addAddressFilters($request)
    {
        $this->addFieldToFilter('country', array(
            array(
                'like'  => $request->getDestCountryId(),
            ),
            array(
                'eq'    => '0',
            ),
            array(
                'eq'    => '',
            ),                                                                  
        ));
        
        $this->addFieldToFilter('state', array(
                                array(
                                'like'  => $request->getDestRegionId(),
                                 ),
                                array(
                                'eq'    => '0',
                                 ),
                                array(
                                'eq'    => '',
                                 ),                                                                  
        ));
        
        $this->addFieldToFilter('city', array(
                                array(
                                'like'  => $request->getDestCity(),
                                 ),
                                array(
                                'eq'    => '',
                                 ),                                                                  
        ));
        
        if (Mage::getStoreConfig('carriers/amtable/numeric_zip')) {
            $this->addFieldToFilter('zip_from', array(
                                    array(
                                    'lteq'  => $request->getDestPostcode(),
                                     ),
                                    array(
                                    'eq'    => '',
                                     ),                                                                  
            ));
            $this->addFieldToFilter('zip_to', array(
                                    array(
                                    'gteq'  => $request->getDestPostcode(),
                                     ),
                                    array(
                                    'eq'    => '',
                                     ),                                                                  
            ));                          
        }         else {

            //$this->addFieldToFilter('zip_from', array(
            //                        array(
            //                        'like'   => $request->getDestPostcode(),
            //                         ),
            //                        array(
            //                        'eq'    => '',
            //                         ),                                                                  
            //));            
        }                          

        
                     
        return $this;        
    }    
    
    public function addMethodFilters($methodIds)
    {
        $this->addFieldToFilter('method_id', array('in'=>$methodIds));  
                                         
        return $this;    
    } 
       
    public function addTotalsFilters($totals,$shippingType)
    {
        $this->addFieldToFilter('price_from', array('lteq'=>$totals['not_free_price']));
        $this->addFieldToFilter('price_to', array('gteq'=>$totals['not_free_price']));
        $this->addFieldToFilter('weight_from', array('lteq'=>$totals['not_free_weight']));
        $this->addFieldToFilter('weight_to', array('gteq'=>$totals['not_free_weight']));
        $this->addFieldToFilter('qty_from', array('lteq'=>$totals['not_free_qty']));
        $this->addFieldToFilter('qty_to', array('gteq'=>$totals['not_free_qty']));
        $this->addFieldToFilter('shipping_type', array(
                                    array(
                                    'eq'  => $shippingType,
                                     ),
                                    array(
                                    'eq'    => '',
                                     ),
                                    array(
                                    'eq'    => '0',
                                     ),                                                                                                             
            ));                         
        return $this;
        
    }
}