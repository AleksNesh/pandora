<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Model_Observer 
{
    protected static $_grid;

    public function onSalesOrderItemSaveAfter($observer){
        
        $orderItem = $observer->getEvent()->getItem();
        
        $amOrderItem = Mage::getModel("amogrid/order_item");
                
        $amOrderItem->mapOrder($orderItem);
        
        return true;  
    } 
    
    public function modifyOrderCollection($observer)
    {
        
        $collection = $observer->getCollection();
        
        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Grid_Collection
                || $collection instanceof Mage_Sales_Model_Resource_Order_Grid_Collection
                ){
            
            $permissibleActions = array('index', 'grid', 'exportCsv', 'exportExcel');
            
            if ( false === strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order') || 
                 !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) ){

                return;
            }

            $layout = Mage::getSingleton('core/layout');
            $grid = $layout->getBlock('sales_order.grid');

            if (!$grid) {
                foreach($layout->getAllBlocks() as $block){
                    if ($block instanceof  Mage_Adminhtml_Block_Sales_Order_Grid ){
                        $grid = $block;
                        break;
                    }
                }
            }
            
            if ($grid){
                
                $columns = Mage::helper("amogrid/columns");
                
                $columns->prepareOrderCollectionJoins($collection); 
                
                $columns->removeColumns($grid);
                
                $columns->reorder($grid);
                
                $columns->restyle($grid);
                
            }
        }
    }
    
    
    
    public function modifyOrderGridAfterBlockGenerate($observer){    
        $permissibleActions = array('index', 'grid', 'exportCsv', 'exportExcel');
        $exportActions = array('exportCsv', 'exportExcel');
        
        if ( !(Mage::app()->getRequest()->getControllerName() == 'sales_order' ||
                Mage::app()->getRequest()->getControllerName() == 'adminhtml_sales_order') ||
                
             !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) ){
             
            return;
        }
        
        $export = in_array(
                    Mage::app()->getRequest()->getActionName(), $exportActions);
        
        $block = $observer->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid){
            
            self::$_grid = $block;
            $columns = Mage::helper("amogrid/columns");
            $columns->prepareGrid($block, $export);
        }
        
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Column && $export){
            if (self::$_grid){
                $columns = Mage::helper("amogrid/columns");
                $columns->removeColumns(self::$_grid);
            }
        }
    }
    
    
    
}
?>