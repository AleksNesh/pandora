<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */


/**
 * FraudDetection data helper
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 */
 
class Directshop_FraudDetection_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_requiredMaxmindFields = array("i", "license_key", "city", "postal", "country");
	
	/*
	 * Normalise the score so it is always out of 100
	 */
	function normaliseMaxMindResponse($res)
	{
		if (isset($res['riskScore']))
		{
			$res['ourscore'] = floatval($res['riskScore']);
		}else
		{
			$res['err'] = isset($res['err']) && !empty($res['err']) ? $res['err'] : "FATAL_ERROR";
                        $res['errmsg'] = isset($res['errmsg']) ? $res['errmsg'] : "No Maxmind Response or No riskScore Found";
		}
		return $res;
		
	}
	
	function queryMaxMind($params)
	{
		$servers = array("http://minfraud1.maxmind.com","http://minfraud2.maxmind.com");
		$return = array();
		$error = false;
		
		
		
		
		
		
		
		
		foreach ($servers as $server)
		{
			$http = new Varien_Http_Client($server."/app/ccv2r");
			
			$http->setParameterPost($params);
			
			try
			{
				$response = $http->request('POST');
			}
			catch(Exception $e)
			{
				continue;
			}
			
			if ($response->getStatus() != '200')
			{
				continue;
			}
			
			foreach (explode(";", $response->getBody()) as $keyval)
			{
				$bits = explode("=",$keyval);
				if (count($bits) > 1)
				{
					list($key,$value) = $bits;
					$return[$key] = $value;
				}
			}
			if (!empty($return))
			{
				break;
			}
		}
		if (empty($return))
		{
			$return['errmsg'] = "Could not contact MaxMind servers.";
			$return['err'] = "FATAL_ERROR";
		}		
		return $return;
		
	}
	
	function getMaxMindResponse($payment)
	{
		$order = $payment->getOrder();
		$address = $order->getBillingAddress();
		$shipAddress = $order->getShippingAddress();
		
		if (!($shipAddress && $shipAddress->getCountry()) || !($address && $address->getCountry()))
		{
			$shipAddress = ($shipAddress && $shipAddress->getCountry()) ? $shipAddress : $address;
			$address = ($address && $address->getCountry()) ? $address : $shipAddress;
		}
		if (!$shipAddress && !$address)
		{
			return array("errmsg" => "No billing or shipping address found.", "err" => "FATAL_ERROR");
		}
		
		$licenseKey = Mage::getStoreConfig('frauddetection/maxmind/licensekey');
		
		$reqType = Mage::getStoreConfig('frauddetection/maxmind/request_type');
		if (!empty($reqType))
		{
				$h["requested_type"] = $reqType;
		}
		
		// Enter your license key here (Required)
		$h["license_key"] = $licenseKey;

		// Required fields
		// find first IP address in string in case there is more than one
		$h["i"] = preg_replace("/^\s*((?:[0-9]{1,3}\.){3}[0-9]{1,3}).*$/", "$1", $order->getRemoteIp());             // set the client ip address
		
		if ($forceIP = Mage::getStoreConfig('frauddetection/debug/force_ip'))
		{
			$h["i"] = $forceIP;
		}
				
		$h["city"] = $address->getCity();             // set the billing city
		$h["region"] = $address->getRegion();                 // set the billing state
		$h["postal"] = $address->getPostcode();              // set the billing zip code
		$h["country"] = $address->getCountry();                // set the billing country
		
		if($h["country"] == "US" && (strlen($h["postal"]) > 5) && (strpos($h["postal"], '-') !== false)){
		
		  $h["postal"] = substr($address->getPostcode(),0,5);
		
		}
		
		
		$billingRegion = Mage::getModel('directory/region')->load($address->getRegionId());
		if ($billingRegionCode = $billingRegion->getCode())
		{
			$h["region"] = $billingRegionCode;
		}
		
		// Recommended fields
		if (preg_match("/[^@]+@(.*)/", $order->getCustomerEmail(), $matches))
		{
			$h["domain"] = $matches[1];		// Email domain
		}
		if ($payment->getCcNumber())
		{
			$h["bin"] = substr($payment->getCcNumber(),0,6);			// bank identification number
		}
		
		$h["usernameMD5"] = md5(strtolower($order->getCustomerEmail()));
		$h["passwordMD5"] = $order->getPasswordHash();
		$h["emailMD5"] = md5(strtolower($order->getCustomerEmail()));
		
		// Optional fields
		//$h["binName"] = "MBNA America Bank";	// bank name
		//$h["binPhone"] = "800-421-2110";	// bank customer service phone number on back of credit card
		//$h["custPhone"] = "212-242";		// Area-code and local prefix of customer phone number
					
		
		$h["custPhone"] = $address->getTelephone();		// Area-code and local prefix of customer phone number
		$h["shipAddr"] = $shipAddress->getStreetFull();	// Shipping Address
		$h["shipCity"] = $shipAddress->getCity();	// the City to Ship to
		$h["shipRegion"] = $shipAddress->getRegion();	// the Region to Ship to
		$h["shipPostal"] = $shipAddress->getPostcode();	// the Postal Code to Ship to
		$h["shipCountry"] = $shipAddress->getCountry();	// the country to Ship to
		
		
		if($h["shipCountry"] == "US" && (strlen($h["shipPostal"]) > 5) && (strpos($h["shipPostal"], '-') !== false)){
		
		  $h["shipPostal"] = substr($shipAddress->getPostcode(),0,5);
		
		}
		
		
		$shippingRegion = Mage::getModel('directory/region')->load($shipAddress->getRegionId());
		if ($shippingRegionCode = $shippingRegion->getCode())
		{
			$h["shipRegion"] = $shippingRegionCode;
		}
		
		$h["txnID"] = $order->getIncrementId();			// Transaction ID
		//$h["sessionID"] = "abcd9876";		// Session ID
		$h["accept_language"] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);
		$h["user_agent"] = $_SERVER['HTTP_USER_AGENT'];	
		//$h["accept_language"] = $headers['accept-language'];
		//$h["user_agent"] = $headers['user-agent'];
		
		// Check for error conditions
		if (empty($licenseKey))
		{
			return array("errmsg" => "No license key set.", "err" => "FATAL_ERROR");	
		}
		
		if (!isset($h['i']) || empty($h['i']))
		{
			return array("err" => "FATAL_ERROR", "errmsg" => "No IP Address found for this order.<br/><small>(Orders created through the admin section may not have IP addresses stored).</small>");
		}
		
		foreach ($this->_requiredMaxmindFields as $field)
		{
			if (!isset($h[$field]) || empty($h[$field]))
			{
				return array("errmsg" => "Required field $field is missing.", "err" => "FATAL_ERROR");	
			}
		}
		
		// check for IP exceptions
		$exceptions = @unserialize(Mage::getStoreConfig('frauddetection/general/ipexceptions'));
		if ($exceptions)
		{
			foreach($exceptions as $key => $value)
			{
				if ($h['i'] == trim($value['ipaddress']))
				{
					return array("errmsg" => "IP address " . $h['i'] . " was found in exceptions list.", "err" => "FATAL_ERROR");
				}
			}
		}
		
		// save the data we're sending for later reference
		if ($order->getId())
		{
			$result = Mage::getModel('frauddetection/result')->loadByOrderId($order->getId())->addData(array(
				'order_id' => $order->getId(),
				'sent_data' => utf8_encode(serialize($h))
			))
			->save();
		}
		else
		{
			$order->setTempFraudSentData(utf8_encode(serialize($h)));
		}
		
		// save against the order as well in case we
	
		//Mage::Log(serialize($h));
			
		// then we get the result from the server
		$res = $this->queryMaxMind($h);
		
	   //Mage::Log(utf8_encode(serialize($res)));
		
		// set the remaining queries in our table
		if (!empty($res['queriesRemaining']))
		{
			Mage::getResourceModel('frauddetection/stats')->setValue("remaining_maxmind_credits", $res['queriesRemaining']);
		}
		
		return $res;
	}
	
	function saveFraudData($data, $order)
	{
			
			if($order->getTempFraudSentData()){					
				Mage::getModel('frauddetection/result')
					->loadByOrderId($order->getId())
					->addData(array(
						'order_id' => $order->getId(),
						'fraud_score' => $data['ourscore'],
						'fraud_data' => utf8_encode(serialize($data)),
						'sent_data' => $order->getTempFraudSentData(),
					))
					->save();
			}else{				    
			   Mage::getModel('frauddetection/result')
				->loadByOrderId($order->getId())
				->addData(array(
					'order_id' => $order->getId(),
					'fraud_score' => $data['ourscore'],
					'fraud_data' => utf8_encode(serialize($data)),					
				))
				->save();
			}
	}

	function sendHoldEmail($order, $fraudResult)
	{
		$mailTemplate = Mage::getModel('core/email_template');
		/* @var $mailTemplate Mage_Core_Model_Email_Template */

		$template = Mage::getStoreConfig('frauddetection/general/email_when_holded_template');

		$copyTo = explode(",", Mage::getStoreConfig('frauddetection/general/email_when_holded_copy_to'));
		if ($copyTo) {
			$mailTemplate->addBcc($copyTo);
		}

		$_reciever = Mage::getStoreConfig('frauddetection/general/email_when_holded_reciever');
		$sendTo = array(
			array(
				'email' => Mage::getStoreConfig('trans_email/ident_'.$_reciever.'/email'),
				'name'  => Mage::getStoreConfig('trans_email/ident_'.$_reciever.'/name')
			)
		);

		foreach ($sendTo as $recipient) {
			$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>Mage::app()->getStore()->getId()))
				->sendTransactional(
					$template,
					'sales',
					$recipient['email'],
					$recipient['name'],
					array(
						'increment_id' => $order->getIncrementId(),
						'fraudScore' => $fraudResult['riskScore'],
						'orderLink'     => Mage_Adminhtml_Helper_Data::getUrl('dsadmin/sales_order/view', array('order_id' => $order->getId()))
					)
				);
		}
	}
}
