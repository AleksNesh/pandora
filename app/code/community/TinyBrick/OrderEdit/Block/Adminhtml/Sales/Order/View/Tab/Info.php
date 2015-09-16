<?php
/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future.
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_View_Tab_Info extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    /**
     * Converts the template to HTML view
     * @return object $html 
     */
    protected function _toHtml()
    {
    	$str = Mage::app()->getFrontController()->getRequest()->getPathInfo();
        //echo "!!".$str;
    	if(strpos($str, '/sales_order/view/')) {
    		$this->setTemplate('orderedit/sales/order/view/tab/info-edit.phtml');
    	}

    	if($str == '/admin/sales_order/view/') {
    		$this->setTemplate('orderedit/sales/order/view/tab/info-edit.phtml');
    	}
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }
    /**
     * Checks to see if you have capabilities of editing the order
     * @param string $status Order Status
     * @return boolean
     */
    public function canEditOrder($status)
    {
    	if(!Mage::getStoreConfig('toe/orderedit/active')) {
    		return false;
    	}
    	$configStatus = Mage::getStoreConfig('toe/orderedit/statuses');
    	$arrStatus = explode(",", $configStatus);
    	if(in_array($status, $arrStatus)) {
    		return true;
    	}
    	return false;
    }
    /**
     * Gets active payment methods from the payment section
     * @return array
     */
    public function getActivPaymentMethods()
    {
       $payments = Mage::getSingleton('payment/config')->getActiveMethods();
       $paymentmethods = array();
       foreach ($payments as $paymentCode=>$paymentModel) {
       		if($paymentModel->canUseCheckout()==1){
	            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
	            $paymentmethods[$paymentCode] = array(
	                'label'   => $paymentTitle,
	                'value' => $paymentCode,
	            );
       		}
        }
        return $paymentmethods;
    }
}