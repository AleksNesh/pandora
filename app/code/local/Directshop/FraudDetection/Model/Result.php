<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */

class Directshop_FraudDetection_Model_Result extends Mage_Core_Model_Abstract
{
	static $fatalErrors = array('INVALID_LICENSE_KEY', 'MAX_REQUESTS_PER_LICENSE','IP_REQUIRED','LICENSE_REQUIRED','COUNTRY_REQUIRED','MAX_REQUESTS_REACHED','SYSTEM_ERROR','IP_NOT_FOUND', 'FATAL_ERROR');
	
    function _construct()
    {
        $this->_init('frauddetection/result');
    }
    
    function loadByOrderId($orderId)
    {
    	$this->load($orderId, 'order_id');
    	return $this;
    }

}