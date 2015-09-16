<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Markshust_Uspsav_Checkout_OnepageController
    extends Mage_Checkout_OnepageController
{
    /**
     * Perform address validation on checkout billing address
     */
    public function saveBillingAction()
    {
        // Check if module is enabled and if this is a post
        if (!Mage::getStoreConfig('checkout/markshust_uspsav/enabled')
            || !$this->getRequest()->isPost()
        ) {
            // Continue processing with core method
            return parent::saveBillingAction();
        }
        
        // Get posted info
        $post = $this->getRequest()->getPost('billing', array());
        
        // If posting record from address book set postdata to existing address
        if ($addressId = $this->getRequest()->getPost('billing_address_id')) {
            $customer = Mage::getModel('customer/session')->getCustomer();
            $existsAddress = $customer->getAddressById($addressId);
            
            // Check if this is an existing address of customer
            if ($existsAddress->getId()
                && $existsAddress->getCustomerId() == $customer->getId()) {
                // Set post data to existing address
                // Needed otherwise it always sends default address
                $post['street']     = $existsAddress->getStreet();
                $post['city']       = $existsAddress->getCity();
                $post['region_id']  = $existsAddress->getRegionId();
                $post['postcode']   = $existsAddress->getPostcode();
                $post['country_id'] = $existsAddress->getCountryId();
            }
        }
        
        // Only make this work if USPS Address Verification isn't bypassed
        // and only for addresses within the United States
        if (!isset($post['uspsav_bypass'])
            && isset($post['country_id'])
            && $post['country_id'] == 'US'
        ) {
            // Get region name from id
            $regionModel = Mage::getModel('directory/region')->load($post['region_id']);
            $regionCode = $regionModel->getCode();
            
            // Store address object to pass to USPS
            $address = (object) array();
            $address->address1  = isset($post['street'][1]) ? $post['street'][1]    : '';
            $address->address2  = isset($post['street'][0]) ? $post['street'][0]    : '';
            $address->city      = isset($post['city'])      ? $post['city']         : '';
            $address->state     = isset($regionCode)        ? $regionCode           : '';
            $address->zip       = isset($post['postcode'])  ? $post['postcode']     : '';
            
            // Pass the address to USPS to verify and store the XML response
            if ($result = Mage::helper('markshust_uspsav')->uspsSubmitRequest($address)) {
                $xml = new SimpleXMLElement($result);
                $error = Mage::helper('markshust_uspsav')->checkXmlForErrors($xml);
            }
            
            // Exit this method, and pass back (alert) error to user
            if (isset($error['error'])) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($error));
                return;
            }
            
            // Set post to the USPS XML response
            if (isset($xml)) {
                $correctedRegionModel = Mage::getModel('directory/region')->loadByCode($xml->Address[0]->State, $post['country_id']);
                $regionId = $correctedRegionModel->getId();
                
                $post['street'][0]  = $xml->Address[0]->Address2;
                $post['street'][1]  = $xml->Address[0]->Address1;
                $post['city']       = $xml->Address[0]->City;
                $post['region_id']  = $regionId;
                $post['postcode']   = $xml->Address[0]->Zip5 . '-' . $xml->Address[0]->Zip4;
                
                // Set post to the corrected response from USPS
                $this->getRequest()->setPost('billing', $post);
                
                // Check if this is an existing address
                if ($addressId
                    && $existsAddress->getId()
                    && $existsAddress->getCustomerId() == $customer->getId()
                ) {
                    // Set existing address to cleansed data from USPS and save
                    $existsAddress->setId($existsAddress->getId());
                    $existsAddress->setStreet($post['street']);
                    $existsAddress->setCity($post['city']);
                    $existsAddress->setRegionId($post['region_id']);
                    $existsAddress->setPostcode($post['postcode']);
                    
                    $existsAddress->save();
                }
            }
        }
        
        // Continue processing with core method
        parent::saveBillingAction();
    }
    
    /**
     * Perform address validation on checkout shipping address
     */
    public function saveShippingAction()
    {

        // Check if module is enabled and if this is a post
        if (!Mage::getStoreConfig('checkout/markshust_uspsav/enabled')
            || !$this->getRequest()->isPost()
        ) {
            // Continue processing with core method
            return parent::saveShippingAction();
        }
        
        // Get posted info
        $post = $this->getRequest()->getPost('shipping', array());
        
        // Only make this work if USPS Address Verification isn't bypassed
        // and only for addresses within the United States
        if (!isset($post['uspsav_bypass'])
            && isset($post['country_id'])
            && $post['country_id'] == 'US'
        ) {
            // Get region name from id
            $regionModel = Mage::getModel('directory/region')->load($post['region_id']);
            $regionCode = $regionModel->getCode();
            
            // Store address object to pass to USPS
            $address = (object) array();
            $address->address1  = isset($post['street'][1]) ? $post['street'][1]    : '';
            $address->address2  = isset($post['street'][0]) ? $post['street'][0]    : '';
            $address->city      = isset($post['city'])      ? $post['city']         : '';
            $address->state     = isset($regionCode)        ? $regionCode           : '';
            $address->zip       = isset($post['postcode'])  ? $post['postcode']     : '';
            
            // Pass the address to USPS to verify and store the XML response
            if ($result = Mage::helper('markshust_uspsav')->uspsSubmitRequest($address)) {
                $xml = new SimpleXMLElement($result);
                $error = Mage::helper('markshust_uspsav')->checkXmlForErrors($xml);
            }
            
            // Exit this method, and pass back (alert) error to user
            if (isset($error['error'])) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($error));
                return;
            }
            
            // Set post to the USPS XML response
            if (isset($xml)) {
                $correctedRegionModel = Mage::getModel('directory/region')->loadByCode($xml->Address[0]->State, $post['country_id']);
                $regionId = $correctedRegionModel->getId();
                
                $post['street'][0]  = $xml->Address[0]->Address2;
                $post['street'][1]  = $xml->Address[0]->Address1;
                $post['city']       = $xml->Address[0]->City;
                $post['region_id']  = $regionId;
                $post['postcode']   = $xml->Address[0]->Zip5 . '-' . $xml->Address[0]->Zip4;
                
                // Set post to the corrected response from USPS
                $this->getRequest()->setPost('shipping', $post);
            }
        }
        
        // Continue processing with core method
        parent::saveShippingAction();
    }
}
