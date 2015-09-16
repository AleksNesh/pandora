<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Block_Adminhtml_Sales_Order_Grid_Renderer_Address_Shipping extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $entityId = $row->getData('entity_id');
        $shippingAddress = Mage::getModel("sales/order")->load($entityId)->getShippingAddress();
        return $shippingAddress ? $shippingAddress->getFormated(true) : '';   
    }
}
?>