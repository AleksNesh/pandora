<?php

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Changed_Order_Abstract extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
    protected function _beforeToHtml()
    {
        return $this;
    }
}