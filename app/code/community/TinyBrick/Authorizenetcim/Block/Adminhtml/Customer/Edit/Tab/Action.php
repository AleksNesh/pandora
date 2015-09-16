<?php 

/**
 * Adminhtml customer action tab
 *
 */
class TinyBrick_Authorizenetcim_Block_Adminhtml_Customer_Edit_Tab_Action 
 extends Mage_Adminhtml_Block_Template 
  implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        $this->setTemplate('authorizenetcim/action.phtml');

    }
    
    //down here are the mandatory methods you have to include
    public function getTabLabel()
    {
    	return Mage::helper('authorizenetcim')->__('Authorize.net CIM');
    }
    
    public function getTabTitle()
    {
    	return Mage::helper('authorizenetcim')->__('Authorize.net CIM');
    }
    
    public function canShowTab()
    {
    	if (Mage::registry('current_customer')->getId()) {
    		return true;
    	}
    	return false;
    }
    
    public function isHidden()
    {
    	if (Mage::registry('current_customer')->getId()) {
    		return false;
    	}
    	return true;
    }
    
    public function getCustomerId()
    {
    	$customerId = $this->getCustomerInfo()->getEntityId();
    	return $customerId;
    }
    
    public function getCustomerPaymentProfiles()
    {
    	$collection = $this->getCollection();
    	$collection->addFilter('customer_id', $this->getCustomerId());
    	$payment = array();
    	foreach($collection as $c)
    	{
    		$payment[] = $c->getData();
    	}
    	return $payment;
    }
    
    public function getCollection()
    {
    	return Mage::getModel('authorizenetcim/authorizenetcim')->getCollection();
    }
    
    public function getCustomerInfo()
    {
    	return Mage::registry('current_customer');
    }
    
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
    
    public function initForm()
    {
    	/* @var $customer Mage_Customer_Model_Customer */
    	$customer = Mage::registry('current_customer');
    
    	$form = new Varien_Data_Form();
    	$fieldset = $form->addFieldset('address_fieldset', array(
    			'legend'    => Mage::helper('customer')->__("Edit Customer's CIM Profiles"))
    	);
    
    	$cimModel = Mage::getModel('authorizenetcim/authorizenetcim');
    	$addressForm = Mage::getModel('customer/form');
    	$addressForm->setFormCode('adminhtml_customer_address')
    	->setEntity($addressModel)
    	->initDefaultValues();
    
    	$attributes = $addressForm->getAttributes();
    	if(isset($attributes['street'])) {
    		Mage::helper('adminhtml/addresses')
    		->processStreetAttribute($attributes['street']);
    	}
    	foreach ($attributes as $attribute) {
    		/* @var $attribute Mage_Eav_Model_Entity_Attribute */
    		$attribute->setFrontendLabel(Mage::helper('customer')->__($attribute->getFrontend()->getLabel()));
    		$attribute->unsIsVisible();
    	}
    	$this->_setFieldset($attributes, $fieldset);
    
    	$regionElement = $form->getElement('region');
    	$regionElement->setRequired(true);
    	if ($regionElement) {
    		$regionElement->setRenderer(Mage::getModel('adminhtml/customer_renderer_region'));
    	}
    
    	$regionElement = $form->getElement('region_id');
    	if ($regionElement) {
    		$regionElement->setNoDisplay(true);
    	}
    
    	$country = $form->getElement('country_id');
    	if ($country) {
    		$country->addClass('countries');
    	}
    
    	if ($this->isReadonly()) {
    		foreach ($addressModel->getAttributes() as $attribute) {
    			$element = $form->getElement($attribute->getAttributeCode());
    			if ($element) {
    				$element->setReadonly(true, true);
    			}
    		}
    	}
    
    	$customerStoreId = null;
    	if ($customer->getId()) {
    		$customerStoreId = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
    	}
    
    	$prefixElement = $form->getElement('prefix');
    	if ($prefixElement) {
    		$prefixOptions = $this->helper('customer')->getNamePrefixOptions($customerStoreId);
    		if (!empty($prefixOptions)) {
    			$fieldset->removeField($prefixElement->getId());
    			$prefixField = $fieldset->addField($prefixElement->getId(),
    					'select',
    					$prefixElement->getData(),
    					'^'
    			);
    			$prefixField->setValues($prefixOptions);
    		}
    	}
    
    	$suffixElement = $form->getElement('suffix');
    	if ($suffixElement) {
    		$suffixOptions = $this->helper('customer')->getNameSuffixOptions($customerStoreId);
    		if (!empty($suffixOptions)) {
    			$fieldset->removeField($suffixElement->getId());
    			$suffixField = $fieldset->addField($suffixElement->getId(),
    					'select',
    					$suffixElement->getData(),
    					$form->getElement('lastname')->getId()
    			);
    			$suffixField->setValues($suffixOptions);
    		}
    	}
    
    	$addressCollection = $customer->getAddresses();
    	$this->assign('customer', $customer);
    	$this->assign('addressCollection', $addressCollection);
    	$form->setValues($addressModel->getData());
    	$this->setForm($form);
    
    	return $this;
    }
}