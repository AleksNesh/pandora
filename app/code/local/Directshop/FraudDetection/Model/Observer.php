<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */
 
class Directshop_FraudDetection_Model_Observer
{
	    
    public function salesOrderAfterSave(Varien_Event_Observer $observer)
    {
    	$order = $observer->getOrder();
    	if ($res = $order->getFraudDataTemp())
    	{
    		if ($order->getId())
    		{
    			Mage::helper('frauddetection')->saveFraudData($res, $order);
    			$order->unsFraudDataTemp();	
    		}
    	}
    }
}