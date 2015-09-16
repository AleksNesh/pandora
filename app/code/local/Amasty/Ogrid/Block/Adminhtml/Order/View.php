<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
    class Amasty_Ogrid_Block_Adminhtml_Order_View extends Mage_Adminhtml_Block_Template{
        
        
        protected function _construct()
        {
            $this->setTemplate('amogrid/order_view.phtml');
        }
        
        protected function getMappedColumns(){
            return Mage::getModel("amogrid/order_item")->getMappedColumns();
        }
        
        protected function getAttributes(){
            return Mage::getModel("amogrid/order_item")->getAttributes();
        }
        
        protected function getViewData(){
            $isOnlyConfigurable =  intval(Mage::getStoreConfig('amogrid/general/configurable')) == 1;
            
            $orderItem = Mage::getModel("amogrid/order_item");
            
            $collection = $orderItem->getCollection();
            $collection->getSelect()->join(
                array(
                    'order_item' => $collection->getTable('sales/order_item')
                ),
                'main_table.item_id = order_item.item_id', 
                array('order_item.product_id')
            );
            
            $collection->getSelect()->where(
                $collection->getConnection()->quoteInto('order_item.order_id = ?', $this->getOrderId()) 
            );
            
            if ($isOnlyConfigurable){
                $collection->addFieldToFilter("order_item.parent_item_id", array('null' => true));
            }

            $ret = $collection->getData();
            $showImages = Mage::getStoreConfig('amogrid/general/images');
            
            if (intval($showImages) > 0) {
                foreach($ret as &$el){
                    $product = Mage::getModel('catalog/product')->load($el['product_id']);
                    
                    if ($product->getThumbnail() !== NULL && $product->getThumbnail() != 'no_selection' ){
                        $el["thumbnail_url"] = $product->getThumbnailUrl() ;
                    }
                }
            }
            
            return $ret;
        }
    }
?>