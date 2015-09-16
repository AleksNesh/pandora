<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 10.01.12
 * Time: 13:35
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Block_Refund_Customerrefund extends Mage_Core_Block_Template
{
    public $order;
    protected function _construct()
    {
        parent::_construct();
    }
    public function getOrder(){
        $this->order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('id'));
        return $this->order;
    }
}
