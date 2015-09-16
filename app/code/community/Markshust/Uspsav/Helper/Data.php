<?php
class Markshust_Uspsav_Helper_Data
{
    public function uspsSubmitRequest($address)
    {
        $xml = Mage::helper('markshust_uspsav')->uspsToXml($address);
        
        $ch = curl_init(Mage::getStoreConfig('checkout/markshust_uspsav/usps_url'));
        curl_setopt($ch, CURLOPT_POST, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, "API=Verify&XML=$xml");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        
        if ($error) {
            $result = "<AddressValidateRequest><Error><![CDATA[$error]]></Error></AddressValidateRequest>";
        }
        
        return $result;
    }
    
    public function uspsToXml($address)
    {
        $account = Mage::getStoreConfig('checkout/markshust_uspsav/usps_account_number');
        
        $xml  = "<AddressValidateRequest USERID=\"$account\">"
            . "<Address ID=\"1\">"
            . "<Address1>{$address->address1}</Address1>"
            . "<Address2>{$address->address2}</Address2>"
            . "<City>{$address->city}</City>"
            . "<State>{$address->state}</State>"
            . "<Zip5>{$address->zip}</Zip5>"
            . "<Zip4></Zip4>"
            . "</Address>";
        
        if (isset($address->ship_address2)) {
            $xml .= "<Address ID=\"2\">"
                . "<Address1>{$address->ship_address1}</Address1>"
                . "<Address2>{$address->ship_address2}</Address2>"
                . "<City>{$address->ship_city}</City>"
                . "<State>{$address->ship_state}</State>"
                . "<Zip5>{$address->ship_zip}</Zip5>"
                . "<Zip4></Zip4>"
                . "</Address>";
        }
        
        $xml .= "</AddressValidateRequest>";
        
        return $xml;
    }
    
    public function checkXmlForErrors($xml)
    {
        $error = array();
        
        // Check XML response for general error
        if (isset($xml->Error)) {
            $error = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('USPS Error: ' . $xml->Error),
                'error_uspsav' => 1
            );
        }
        
        // Check XML response for errors and store as error message
        if (isset($xml->Address[0]->Error)) {
            $error = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Line 1 of your street address did not pass validation. Please check your address and try again.'),
                'error_uspsav' => 1
            );
        }
        
        if (isset($xml->Address[1]->Error)) {
            $error = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Line 2 of your street address did not pass validation. Please check your address and try again.'),
                'error_uspsav' => 1
            );
        }
        
        return $error;
    }
}
