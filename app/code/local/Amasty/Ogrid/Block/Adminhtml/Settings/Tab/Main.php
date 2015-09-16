<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Block_Adminhtml_Settings_Tab_Main extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        $this->setTemplate('amogrid/main.phtml');
    }
    
    public function getAttributes()
    {
        return Mage::getModel('amogrid/order_item')->getAttributes();
    }
    
    public function getMappedColumns()
    {
        return Mage::getModel('amogrid/order_item')->getMappedColumns();
    }
    
    public function getTabLabel()
    {
        return Mage::helper('amogrid')->__('Attributes Configuration');
    }
    
    public function getTabTitle()
    {
        return Mage::helper('amogrid')->__('Attributes Configuration');
    }
    
    public function getColumns(){
        return Mage::helper('amogrid')->getColumns();    
    }
    
    public function canShowTab()
    {
        return true;
    }
    
    public function isHidden()
    {
        return false;
    }

    public function getColumnTypeLabel($key){
        $hlr = Mage::helper('amogrid');
        
        $_columnsTypes = array(
            'configurable' => $hlr->__('New Fields'),
            'default' => $hlr->__('Default Fields'),
            'attribute' => $hlr->__('Product Attributes'),
            'static' => $hlr->__('Static Attributes'),
        );
        
        return isset($_columnsTypes[$key]) ? Mage::helper('amogrid')->__($_columnsTypes[$key]) : 'undefined';
    }

}
?>