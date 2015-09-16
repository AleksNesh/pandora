<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 10.01.12
 * Time: 13:35
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Block_Refund_Print extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('upslabel/sales/order/refund/refund.phtml');
    }
}
