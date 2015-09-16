<?php
/**
 * OpenCommerce Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OpenCommerce Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@OpenCommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future.
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_CIMple
 * @copyright  Copyright (c) 2013 OpenCommerce Inc. LLC
 * @license    http://store.opencommercellc.com/commercial-license
 */
class TinyBrick_Authorizenetcim_Model_Authorizenetcim_Authnetxml extends Varien_Object
{
	const USE_PRODUCTION_SERVER = 0;
	const USE_DEVELOPMENT_SERVER = 1;
	
	const EXCEPTION_CURL = 10;
	
	private $ch;
	private $login;
	private $response;
	private $response_xml;
	private $results;
	private $transkey;
	private $url;
	private $xml;
	
	public function __construct($storeId)
	{
		$testMode = Mage::getStoreConfig('payment/authorizenetcim/test_mode', $storeId['store']);
		if($testMode){
			$login = Mage::getStoreConfig('payment/authorizenetcim/test_username',$storeId['store']); //trim($login);
			$transkey = Mage::getStoreConfig('payment/authorizenetcim/test_password',$storeId['store']); //trim($transkey);
			$test = self::USE_DEVELOPMENT_SERVER;
		}
		else{
			$login = Mage::getStoreConfig('payment/authorizenetcim/username',$storeId['store']); //trim($login);
			$transkey = Mage::getStoreConfig('payment/authorizenetcim/password',$storeId['store']); //trim($transkey);
			$test = self::USE_PRODUCTION_SERVER;			
		}
		
		if (empty($login) || empty($transkey))
		{
			trigger_error('You have not configured your ' . __CLASS__ . '() login credentials properly.', E_USER_WARNING);
		}
	
		$this->login = trim($login);
		$this->transkey = trim($transkey);
		$test = (bool) $test;
	
		$subdomain = ($test) ? 'apitest' : 'api';
		$this->url = 'https://' . $subdomain . '.authorize.net/xml/v1/request.api';
	}
	
	public function getLogin(){
		return $this->login;
	}
	
	/**
	 * remove XML response namespaces
	 * without this php will spit out warinings
	 * @see http://community.developer.authorize.net/t5/Integration-and-Testing/ARB-with-SimpleXML-PHP-Issue/m-p/7128#M5139
	 */
	private function removeResponseXMLNS($input)
	{
		// why remove them one at a time? all three aren't consistantly used in the response
		$input = str_replace(' xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"','',$input);
		$input = str_replace(' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"','',$input);
		return str_replace(' xmlns:xsd="http://www.w3.org/2001/XMLSchema"','',$input);
	}
	
	public function __toString()
	{
		$output = '';
		$output .= '<table summary="Authorize.Net Results" id="authnet">' . "\n";
		$output .= '<tr>' . "\n\t\t" . '<th colspan="2"><b>Class Parameters</b></th>' . "\n" . '</tr>' . "\n";
		$output .= '<tr>' . "\n\t\t" . '<td><b>API Login ID</b></td><td>' . $this->login . '</td>' . "\n" . '</tr>' . "\n";
		$output .= '<tr>' . "\n\t\t" . '<td><b>Transaction Key</b></td><td>' . $this->transkey . '</td>' . "\n" . '</tr>' . "\n";
		$output .= '<tr>' . "\n\t\t" . '<td><b>Authnet Server URL</b></td><td>' . $this->url . '</td>' . "\n" . '</tr>' . "\n";
		$output .= '<tr>' . "\n\t\t" . '<th colspan="2"><b>Request XML</b></th>' . "\n" . '</tr>' . "\n";
		if (isset($this->xml) && !empty($this->xml))
		{
			$dom = new DOMDocument('1.0');
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->loadXML(self::removeResponseXMLNS($this->xml));
			$outgoing_xml = $dom->saveXML();
	
			$output .= '<tr><td>' . "\n";
			$output .= 'XML:</td><td><pre>' . "\n";
			$output .= htmlentities($outgoing_xml) . "\n";
			$output .= '</pre></td></tr>' . "\n";
		}
		if (!empty($this->response))
		{
			$dom = new DOMDocument('1.0');
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->loadXML(self::removeResponseXMLNS($this->response));
			$response_xml = $dom->saveXML();
	
			$output .= '<tr>' . "\n\t\t" . '<th colspan="2"><b>Response XML</b></th>' . "\n" . '</tr>' . "\n";
			$output .= '<tr><td>' . "\n";
			$output .= 'XML:</td><td><pre>' . "\n";
			$output .= htmlentities($response_xml) . "\n";
			$output .= '</pre></td></tr>' . "\n";
		}
		$output .= '</table>';
	
		return $output;
	}
	
	public function __destruct()
	{
		if (isset($this->ch))
		{
			curl_close($this->ch);
		}
	}
	
	public function __get($var)
	{
		return $this->response_xml->$var;
	}
	
	public function __set($key, $value)
	{
		trigger_error('You cannot set parameters directly in ' . __CLASS__ . '.', E_USER_WARNING);
		return false;
	}
	
	public function __call($api_call, $args)
	{
		$this->xml = new SimpleXMLElement('<' . $api_call . '></' . $api_call . '>');
		$this->xml->addAttribute('xmlns', 'AnetApi/xml/v1/schema/AnetApiSchema.xsd');
		$merch_auth = $this->xml->addChild('merchantAuthentication');
		$merch_auth->addChild('name', $this->login);
		$merch_auth->addChild('transactionKey', $this->transkey);
	
		$this->setParameters($this->xml, $args[0]);
		$this->process();
	}
	
	private function setParameters($xml, $array)
	{
		if (is_array($array))
		{
			$first = true;
			foreach ($array as $key => $value)
			{
				if (is_array($value))
				{
					if(is_numeric($key))
					{
						if($first === true)
						{
							$xmlx = $xml;
							$first = false;
						}
						else
						{
							$parent = $xml->xpath('parent::*');
							$xmlx = $parent[0]->addChild($xml->getName());
						}
					}
					else
					{
						$xmlx = $xml->addChild($key);
					}
					$this->setParameters($xmlx, $value);
				}
				else
				{
					$xml->$key = $value;
				}
			}
		}
	}
	
	private function process()
	{
		$this->xml = $this->xml->asXML();
	
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->xml);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		//curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($this->ch, CURLOPT_CAINFO, dirname(__FILE__) . '/ssl/cert.pem');
	
		if(($this->response = curl_exec($this->ch)) !== false)
		{
			$this->response_xml = @new SimpleXMLElement($this->response);	
			//$response = curl_exec($this->ch);
			//$response = $this->response;
			//Mage::log($response);
			
			curl_close($this->ch);
			unset($this->ch);
			return;
		}
		Mage::log('Connection error: ' . curl_error($this->ch) . ' (' . curl_errno($this->ch) . ')');
	}
	
	public function isSuccessful()
	{
		return $this->response_xml->messages->resultCode == 'Ok';
	}
	
	public function isError()
	{
		return $this->response_xml->messages->resultCode != 'Ok';
	}
	
	public function getUsername()
	{
		return $this->login;
	}
	
	public function getTransKey()
	{
		return $this->transkey;
	}
	
}