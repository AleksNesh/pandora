<?php
/**
 * Created by JetBrains PhpStorm.
 * User: genevieveeddison
 * Date: 13/11/13
 * Time: 10:45 AM
 * To change this template use File | Settings | File Templates.
 */ 
class Webshopapps_Endicia_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $_insuranceEnabled = null;

    public function isEndiciaLabelPossible($shippingMethod) {
        if(Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Endicia', 'carriers/wsaendicia/active') && strpos($shippingMethod, 'wsaendicia') === false) {
            return true;
        }
        return false;
    }

    public function isDomestic($destinationCountry) {
        $endicia =  Mage::getModel('wsaendicia/carrier_endicia');
        if($endicia->getDeliveryServiceLevel($destinationCountry)  == Webshopapps_Endicia_Model_Carrier_Endicia::DELIVERY_DOMESTIC)
        {
            return true;
        }
        return false;
    }

    public function getInsuranceEnabled()
    {
        if(!$this->_insuranceEnabled) {
            $this->_insuranceEnabled = true;
            if($this->getInsuranceProvider() == Webshopapps_Endicia_Model_Carrier_Endicia::INSURANCE_DISABLED) {
                $this->_insuranceEnabled = false;
            }

        }
        return $this->_insuranceEnabled;
    }

    public function getInsuranceProvider()
    {
        return Mage::getStoreConfig('carriers/wsaendicia/insurance_provider');
    }

}