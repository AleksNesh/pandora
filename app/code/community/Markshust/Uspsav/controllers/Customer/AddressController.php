<?php
require_once 'Mage/Customer/controllers/AddressController.php';

class Markshust_Uspsav_Customer_AddressController
    extends Mage_Customer_AddressController
{
    public function formPostAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        
        // Save data
        if ($this->getRequest()->isPost()) {
            $customer = $this->_getSession()->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $customerAddress  = Mage::getModel('customer/address');
            $customerAddressId = $this->getRequest()->getParam('id');
            if ($customerAddressId) {
                $existsAddress = $customer->getAddressById($customerAddressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $customerAddress->setId($existsAddress->getId());
                }
            }
            
            $post = $this->getRequest()->getPost();
            
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
                    $this->_getSession()->addError($error['message']);
                    $post['uspsav_failed'] = 1;
                    $this->_getSession()->setAddressFormData($post);
                    if ($customerAddress->getId()) {
                        return $this->_redirectError(Mage::getUrl('*/*/edit', array('id' => $customerAddress->getId())));
                    } else {
                        return $this->_redirect('*/*/new/');
                    }
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
                    $this->getRequest()->setPost($post);
                }
            }
        }
        
        return parent::formPostAction();
    }
}
