<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Helper_Data extends Mage_Core_Helper_Abstract
{
    static protected $_STATIC_COLUMNS_COUNT = 5;
    protected $_configurableColumns = array(
    );
    
    protected $_defaultColumns = array(
    );
    
    public function __construct(){
        $columns = Mage::helper("amogrid/columns");
        $configurableColumns = $columns->getConfigurableFields();
        $defaultColumns = $columns->getDefaultFields();
        
        
        foreach($configurableColumns as $key => $column){
            $this->_configurableColumns[$key] = $column["header"];
        }
        
        foreach($defaultColumns as $key => $column){
            $this->_defaultColumns[$key] = $column["header"];
        }
    }


    public function hasProcessingErrors(){
        
        $collection = Mage::getModel("amogrid/order_item")->getUnmappedOrders();
        return count($collection->getItems()) > 0;
    }
    
    
    protected function _getColumn($key, $name, $available, $position, $type){
        return array(
                'key' => $key,
                'name' => $this->__($name),
                'available' => $available,
                'position' => $position,
                'type' => $type,
                'relation' => '',
                'width' => ''
                    
            );
    }
    
    protected function _getAttributes(){
        $ret = array();
        
        $collection = Mage::getModel('amogrid/order_item')->getAttributes();
        
        foreach($collection as $item){
            $ret[$item->getAttributeCode()] = $item;
        }
        
        return $ret;
        
    }
    
    protected function getSerializedColumns(){
        $columns = array();
        
        $columns_serialized = Mage::getStoreConfig('amogrid/general/columns');
        
        if ($columns_serialized){
            $columns = unserialize($columns_serialized);
        } else {
            $columns = array(
                'am_product_images' => array(
                    'available' => Mage::getStoreConfig('amogrid/general/images'),
                ),
                'am_coupon_code' => array(
                    'available' => Mage::getStoreConfig('amogrid/general/coupon')
                ),
                'am_shipping_description' => array(
                    'available' => Mage::getStoreConfig('amogrid/general/shipping')
                ),
                'am_method' => array(
                    'available' => Mage::getStoreConfig('amogrid/general/payment')
                ),
                'am_shipping_address' => array(
                    'available' => Mage::getStoreConfig('amogrid/general/shipping_address')
                ),
                'am_billing_address' => array(
                    'available' => Mage::getStoreConfig('amogrid/general/billing_address')
                ),
                'am_customer_email' => array(
                    'available' => Mage::getStoreConfig('amogrid/general/customer_email')
                ),
            );
            
            $mappedColumns = Mage::getModel('amogrid/order_item')->getMappedColumns();
            
            foreach($mappedColumns as $code){
                $columns[$code] = array(
                    'available' => 1
                );
            }
        }
        
        return $columns;
    }
    
    function getColumns(){
        $step = 10;       
        $ret = array();
        
        $position = 1;
        
        foreach($this->_configurableColumns as $key => $name){
            $ret[$key] = $this->_getColumn(
                                        $key, 
                                        $name, 
                                        0, 
                                        $position,
                                        'configurable'
                                    );
            
            $position+=$step;
        }
        
        foreach($this->_defaultColumns as $key => $name){
            $ret[$key] = $this->_getColumn(
                                        $key, 
                                        $name, 
                                        1, 
                                        $position,
                                        'default'
                                    );
            
            $position+=$step;
        }
        
        
//        $keys = Mage::getModel('amogrid/order_item')->getMappedColumns();
        $attributes = $this->_getAttributes();
        
        foreach($attributes as $key => $attribute){
            $ret[$key] = $this->_getColumn(
                                        $key, 
                                        $attribute->getFrontendLabel(), 
                                        0, 
                                        $position,
                                        'attribute'
                                    );
            
            $position+=$step;
        }
        
        for($ind = 1; $ind <= self::$_STATIC_COLUMNS_COUNT; $ind++){
            $key = 'static_'.$ind;
            $ret[$key] = $this->_getColumn(
                                        $key, 
                                        $key, 
                                        1, 
                                        $position,
                                        'static'
                                    );
            
            $position+=$step;
        }
        
        $storedColumns = $this->getSerializedColumns();
        
        foreach($storedColumns as $key => $config){
            $config['available'] = isset($config['available']) && $config['available'] == 1 ? 1 : 0;
            
            if (isset($ret[$key]))
            $ret[$key] = array_merge($ret[$key], $config);
        }
        
        return $ret;
    }
    
    function saveColumns($columns){
        Mage::getConfig()->saveConfig('amogrid/general/columns', serialize($columns));
        
        Mage::getConfig()->cleanCache();
    }
    
    
    
}
?>