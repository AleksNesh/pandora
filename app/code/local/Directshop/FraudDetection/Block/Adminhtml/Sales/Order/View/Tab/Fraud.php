<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */
class Directshop_Frauddetection_Block_Adminhtml_Sales_Order_View_Tab_Fraud
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('fraud/tab.phtml');
	}
		
    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
    
    public function getFraudResult()
    {
    	$order = $this->getOrder();
		$result = Mage::getModel('frauddetection/result')->loadByOrderId($order->getId());
		$res = @unserialize(utf8_decode($result->getFraudData()));
		return $res;
    }
    
    public function getCustomer()
    {
    	$customer = Mage::getModel('customer/customer')->load($this->getOrder()->getCustomerId());
    	return $customer;
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('sales')->__('Fraud Detection');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Fraud Detection');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}