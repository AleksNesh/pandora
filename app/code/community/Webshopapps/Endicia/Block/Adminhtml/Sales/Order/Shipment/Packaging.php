<?php
/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Endicia
 * User         Genevieve Eddison
 * Date         13 November 2013
 * Time         11:00 AM
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */

class Webshopapps_Endicia_Block_Adminhtml_Sales_Order_Shipment_Packaging extends Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging
{
    protected $__defaultDimensions;

    /**
     * Verify Endicia is enabled and this order is not already Endicia
     *
     * @return array
     */
    public function showEndiciaShipping()
    {
        if(!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Endicia', 'carriers/wsaendicia/labels')) {
            return false;
        }

        if($this->carrierIsEndicia()) {
            return false;
        }

        return true;
    }

    public function defaultSelectEndicia()
    {
        return Mage::getStoreConfig('carriers/wsaendicia/default_to_endicia');
    }

    public function carrierIsEndicia()
    {
        $order = $this->getShipment()->getOrder();
        $carrier = $order->getShippingCarrier();
        if($carrier->getCarrierCode() == Mage::getModel('wsaendicia/carrier_endicia')->getCarrierCode()) {
            return true;
        }
        return false;
    }

    /**
     * Return shipping methods available for Endicia
     *
     * @return array
     */
    public function getEndiciaShippingMethods()
    {
        $methodModel = Mage::getModel('wsaendicia/carrier_endicia_source_method');
        $shippingAddress = $this->getShipment()->getShippingAddress();
        $isDomestic = Mage::helper('wsaendicia')->isDomestic($shippingAddress->getCountryId());
        if($isDomestic){
            $endiciaMethods = $methodModel->getDomesticMethods();
        }
        else {
            $endiciaMethods = $methodModel->getInternationalMethods();
        }
        $endiciaMethods = array_merge(array(''=>Mage::helper('wsaendicia')->__('Choose shipping method')), $endiciaMethods);
        return $endiciaMethods;
    }

    public function getDefaultShipMethod()
    {
        $countryId = $this->getShipment()->getOrder()->getShippingAddress()->getCountryId();
        if(Mage::helper('wsaendicia')->isDomestic($countryId)) {
            return Mage::getStoreConfig('carriers/wsaendicia/default_domestic');
        }
        return Mage::getStoreConfig('carriers/wsaendicia/default_international');

    }

    public function getDefaultContainer()
    {
        $default =  'wsaendicia#' .Mage::getStoreConfig('carriers/wsaendicia/container');
        return $default;
    }

    /**
     * Return container types of carrier
     *
     * @return array
     */
    public function getContainers()
    {
        if(!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Endicia', 'carriers/wsaendicia/labels')) {
            return parent::getContainers();
        }
        $order = $this->getShipment()->getOrder();
        $storeId = $this->getShipment()->getStoreId();
        $address = $order->getShippingAddress();
        $carrier = $order->getShippingCarrier();
        if($carrier->getCarrierCode() == Mage::getModel('wsaendicia/carrier_endicia')->getCarrierCode()) {
            return parent::getContainers();
        }
        $countryShipper = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $storeId);
        if ($carrier) {
            $params = new Varien_Object(array(
                'method' => $order->getShippingMethod(true)->getMethod(),
                'country_shipper' => $countryShipper,
                'country_recipient' => $address->getCountryId(),
            ));
            $endiciaCarrier = Mage::getModel('wsaendicia/carrier_endicia');
            $containerTypes[$endiciaCarrier->getCarrierCode()] = $endiciaCarrier->getContainerTypes($params);
            $containerTypes[$carrier->getCarrierCode()] =  $carrier->getContainerTypes($params);
            $containers = array();
            foreach ($containerTypes as $carrierCode=>$containerArray) {
                $carrierTitle = Mage::getStoreConfig('carriers/' . $carrierCode . '/title', $storeId);
                foreach($containerArray as $containerCode=>$containerName) {
                    $containers[$carrierCode.'#'.$containerCode] =  $carrierTitle .' : ' .$containerName;
                }
            }
            $arr = array('none' => Mage::helper('shipping')->__('Choose container')) + $containers;
            return $arr;
        }
        return array();
    }

    /**
     * Get codes of customizable container types of carrier
     *
     * @return array
     */
    protected function _getCustomizableContainers()
    {
        $carrier = $this->getShipment()->getOrder()->getShippingCarrier();
        $endiciaCarrier = Mage::getModel('wsaendicia/carrier_endicia');
        if(!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Endicia', 'carriers/wsaendicia/labels') ||
            $carrier->getCarrierCode() == $endiciaCarrier->getCarrierCode())  {
            return parent::_getCustomizableContainers();
        }
        if ($carrier) {
            $endiciaContainerTypes = Mage::getModel('wsaendicia/carrier_endicia')->getCustomizableContainerTypes();
            foreach($endiciaContainerTypes as $key => $code) {
                $endiciaContainerTypes[$key] = $endiciaCarrier->getCarrierCode().'#'.$code;
            }
            $customContainerTypes = $carrier->getCustomizableContainerTypes();
            foreach($customContainerTypes as $key => $code) {
                $customContainerTypes[$key] = $carrier->getCarrierCode().'#'.$code;
            }
            return $endiciaContainerTypes+ $customContainerTypes;
        }
        return array();
    }

    /**
     * Return delivery confirmation types of current carrier including Endicia
     *
     * @return array
     */
    public function getDeliveryConfirmationTypes()
    {
       $carrier = $this->getShipment()->getOrder()->getShippingCarrier();
        $endiciaCarrier = Mage::getModel('wsaendicia/carrier_endicia');
        if(!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Endicia', 'carriers/wsaendicia/labels') ||
            $carrier->getCarrierCode() == $endiciaCarrier->getCarrierCode())  {
            return parent::getDeliveryConfirmationTypes();
        }

        $countryId = $this->getShipment()->getOrder()->getShippingAddress()->getCountryId();
        $deliveryConfTypes = array();
        $params = new Varien_Object(array('country_recipient' => $countryId));
        if ($carrier && is_array($carrier->getDeliveryConfirmationTypes($params))) {
            $deliveryConfTypes =  $carrier->getDeliveryConfirmationTypes($params);
        }
        if (count($deliveryConfTypes) < 1) {
            $deliveryConfTypes =  $endiciaCarrier->getDeliveryConfirmationTypes($params);
        }
        return $deliveryConfTypes;
    }

    /*
     *Validate configuration and delivery address for insurance options
     */
    public function insuranceAvailable()
    {
        $available = false;

        if(Mage::helper('wsaendicia')->getInsuranceEnabled()) {
            $countryId = $this->getShipment()->getOrder()->getShippingAddress()->getCountryId();
            $insuranceType = Mage::helper('wsaendicia')->getInsuranceProvider();
            //restrictions on insurance enforced
            switch ($insuranceType) {
                case Webshopapps_Endicia_Model_Carrier_Endicia::INSURANCE_USPS:
                    if(Mage::helper('wsaendicia')->isDomestic($countryId) &&
                       !Mage::getStoreConfig('carriers/wsaendicia/stealth'))
                        $available = true;
                    break;
                case Webshopapps_Endicia_Model_Carrier_Endicia::INSURANCE_ENDICIA:
                    //TODO check against account that they have endicia insurance enabled on their account
                        $available = true;
                    break;
                default:
                    break;
            }

        }

        return $available;
    }

    public function getDefaultShipmentValue()
    {
        return $this->_calculateShipmentValue($this->getShipment());
    }
    /*
     * Retrieve default length
     */
    public function getDefaultLength()
    {
        return $this->_getDefaultDimensions(0);

    }

    /*
     * Retrieve default width
     */
    public function getDefaultWidth()
    {
        return $this->_getDefaultDimensions(1);

    }

    /*
    * Retrieve default height
    */
    public function getDefaultHeight()
    {
        return $this->_getDefaultDimensions(2);

    }


    protected function _getDefaultDimensions($key)
    {
        if(!$this->_defaultDimensions) {
            $this->_defaultDimensions = Mage::getStoreConfig('carriers/wsaendicia/default_dimensions');
        }

        if(!is_null($this->_defaultDimensions) && $this->_defaultDimensions != '') {
            $defaultsArray = explode(',', $this->_defaultDimensions);
            if(is_array($defaultsArray) && array_key_exists($key, $defaultsArray) && is_numeric($defaultsArray[$key])) {
                return $defaultsArray[$key];
            }
        }
        return false;
    }

    protected function _calculateShipmentValue($shipment)
    {
        $total = 0;
        foreach($shipment->getAllItems() as $item)
        {
            $total += $item->getPrice()* $item->getQty();
        }

        return $total;
    }
}