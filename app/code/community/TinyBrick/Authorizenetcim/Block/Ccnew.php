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
class TinyBrick_Authorizenetcim_Block_Ccnew extends Mage_Directory_Block_Data
{
	
    protected $_address;
    protected $_countryCollection;
    protected $_regionCollection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_address = Mage::getModel('customer/address');
        
        $customer = Mage::getModel('customer/customer');
        $savedCreditCard = Mage::getModel('authorizenetcim/authorizenetcim');

        // Init address object
        if ($id = $this->getRequest()->getParam('id')) {
        	$savedCreditCard->load($id);
        	$customer->load($savedCreditCard->getCustomerId());
        	$defaultBilling = $customer->getDefaultBillingAddress();
        	$billingId = $defaultBilling->getId();
        	
            $this->_address->load($billingId);
            if ($this->_address->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
                $this->_address->setData(array());
            }
        }

        if (!$this->_address->getId()) {
            $this->_address->setPrefix($this->getCustomer()->getPrefix())
                ->setFirstname($this->getCustomer()->getFirstname())
                ->setMiddlename($this->getCustomer()->getMiddlename())
                ->setLastname($this->getCustomer()->getLastname())
                ->setSuffix($this->getCustomer()->getSuffix());
        }

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->getTitle());
        }

        if ($postedData = Mage::getSingleton('customer/session')->getAddressFormData(true)) {
            $this->_address->addData($postedData);
        }
    }

    /**
     * Generate name block html
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock('customer/widget_name')
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getAddress()->getId()) {
            $title = Mage::helper('customer')->__('Edit Address');
        }
        else {
            $title = Mage::helper('customer')->__('Add New Address');
        }
        return $title;
    }
    /**
     * gets the previous page the user was on
     * @return Ambigous <mixed, NULL, unknown, multitype:, Varien_Object>|Ambigous <string, mixed>
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        if ($this->getCustomerAddressCount()) {
            return $this->getUrl('customer/address');
        } else {
            return $this->getUrl('customer/account/');
        }
    }
    /**
     * Gets the save url to save the edited cc
     */
    public function getSaveUrl()
    {      
    	return Mage::getUrl('authorizenetcim/index/submit', array('_secure'=>true, 'id'=>$this->getRequest()->getParam('id')));
    }
    /**
     * Get the address from the system
     * @return Mage_Customer_Model_Address
     */
    public function getAddress()
    {
        return $this->_address;
    }
    /**
     * Gets the country ID from Magento
     * @see Mage_Directory_Block_Data::getCountryId()
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }
    /**
     * Gets the region ID from Magento
     * @return number
     */
    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }
    /**
     * Counts how many addresses the user has
     * @return number
     */
    public function getCustomerAddressCount()
    {
        return count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses());
    }
    /**
     * Checks to see if you can set the address as default
     * @return Ambigous <number, number>|boolean
     */
    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultBilling();
    }
    /**
     * Checks to see if you can set the address as a default
     * @return Ambigous <number, number>|boolean
     */
    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultShipping();;
    }
    /**
     * Checks if the address is already default
     * @return boolean
     */
    public function isDefaultBilling()
    {
        $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultBilling;
    }
    /**
     * Checks if the address is already default
     * @return boolean
     */
    public function isDefaultShipping()
    {
        $defaultShipping = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultShipping;
    }
    /**
     * Gets customer info from Magento
     * @return Mage_Customer_Model_Session
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
    /**
     * Gets the back button and puts in the customer/address into it
     * @return Ambigous <string, mixed>
     */
    public function getBackButtonUrl()
    {
        if ($this->getCustomerAddressCount()) {
            return $this->getUrl('customer/address');
        } else {
            return $this->getUrl('customer/account/');
        }
    }
    /**
     * gets the available credit card types
     * @return multitype:unknown
     */
    public function getCcAvailableTypes()
    {
		$ccTypes = str_getcsv(Mage::getStoreConfig('payment/authorizenetcim/cctypes'));
		
    	$types =  array();
    	
       	foreach (Mage::getSingleton('payment/config')->getCcTypes() as $code => $name) {
       		if(in_array($code,$ccTypes)){
       			$types[$code] = $name;            
       		}
        }
    	return $types;
    }
    
    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
    	$months = array(
    			'1' => '01 - January',
    			'2' => '02 - February',
    			'3' => '03 - March',
    			'4' => '04 - April',
    			'5' => '05 - May',
    			'6' => '06 - June',
    			'7' => '07 - July',
    			'8' => '08 - August',
    			'9' => '09 - September',
    			'10' => '10 - October',
    			'11' => '11 - November',
    			'12' => '12 - December',
    			
			);
    	return $months;
    }
    
    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
    	$years = array_combine(range(date("Y"), date("Y")+10), range(date("Y"), date("Y")+10));
    	return $years;
    }
    
    /**
     * Retrive has verification configuration
     *
     * @return boolean
     */
    public function hasVerification()
    {
    	if ($this->getMethod()) {
    		$configData = $this->getMethod()->getConfigData('useccv');
    		if(is_null($configData)){
    			return true;
    		}
    		return (bool) $configData;
    	}
    	return true;
    }
    
    /**
     * Whether switch/solo card type available
     */
    public function hasSsCardType()
    {
    	$availableTypes = explode(',', $this->getMethod()->getConfigData('cctypes'));
    	$ssPresenations = array_intersect(array('SS', 'SM', 'SO'), $availableTypes);
    	if ($availableTypes && count($ssPresenations) > 0) {
    		return true;
    	}
    	return false;
    }
    
    /**
     * solo/switch card start year
     * @return array
     */
    public function getSsStartYears()
    {
    	$years = array();
    	$first = date("Y");
    
    	for ($index=5; $index>=0; $index--) {
    		$year = $first - $index;
    		$years[$year] = $year;
    	}
    	$years = array(0=>$this->__('Year'))+$years;
    	return $years;
    }
    /**
     * Gets the method type
     * @return Ambigous <Mage_Payment_Model_Method_Abstract, string>
     */
    public function getMethod()
    {
    	$method = 'authorizenetcim';
    
    	if (!($method instanceof Mage_Payment_Model_Method_Abstract)) {
    		Mage::throwException($this->__('Cannot retrieve the payment method model object.'));
    	}
    	return $method;
    }
    
    /**
     * Retrieve payment method code
     *
     * @return string
     */
    public function getMethodCode()
    {
    	return $this->getMethod()->getCode();
    }
    
    /**
     * Retrieve field value data from payment info object
     *
     * @param   string $field
     * @return  mixed
     */
    public function getInfoData($field)
    {
    	return $this->htmlEscape($this->getMethod()->getInfoInstance()->getData($field));
    }
    
    /**
     * Check whether current payment method can create billing agreement
     *
     * @return bool
     */
    public function canCreateBillingAgreement()
    {
    	return $this->getMethod()->canCreateBillingAgreement();
    }
	
}