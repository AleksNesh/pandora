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
class Webshopapps_Endicia_Model_Carrier_Endicia
    extends Webshopapps_Wsacommon_Model_Shipping_Carrier_Baseabstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'wsaendicia';

    protected $_modName = 'Webshopapps_Endicia';

    protected $_prodTrackServiceWsdl        = '';
    protected $_prodRateLabelServiceWsdl         = '';
    protected $_devTrackServiceWsdl         = '';
    protected $_devRateLabelServiceWsdl          = '';
    const DEV_RATE_LABEL_URL              = 'https://elstestserver.endicia.com/LabelService/EwsLabelService.asmx';
    const DEV_TRACK_URL             = 'https://elstestserver.endicia.com/LabelService/EwsLabelService.asmx';
  //    const DEV_RATE_LABEL_URL              = 'https://www.envmgr.com/LabelService/EwsLabelService.asmx';
  //  const DEV_TRACK_URL             = 'https://www.endicia.com/ELS/ELSServices.cfc?wsdl';

    const PROD_RATE_LABEL_URL       = 'https://labelserver.endicia.com/LabelService/EwsLabelService.asmx';
    const PROD_TRACK_URL            = 'https://labelserver.endicia.com/LabelService/EwsLabelService.asmx';
 // const PROD_RATE_LABEL_URL             = 'https://labelserver.endicia.com/LabelService/EwsLabelService.asmx';
 // const PROD_TRACK_URL            = 'https://www.endicia.com/ELS/ELSServices.cfc?wsdl';

    const RATE = 1;
    const LABEL = 2;
    const TRACK = 3;

    const DELIVERY_DOMESTIC = 'Domestic';
    const DELIVERY_INTERNATIONAL = 'International';
    const DELIVERY_ALL          = 'All';

    const ERROR_CODE = 999;

    const DEFAULT_CONF_TYPE = 'DeliveryConfirmation';

    const INSURANCE_DISABLED = 'OFF';
    const INSURANCE_USPS    = 'UspsOnline';
    const INSURANCE_ENDICIA = 'Endicia';
    protected $_rawTrackingRequest;
    const DEFAULT_CONTAINER = 'Parcel';

    /**
     * Container types that could be customized for UPS carrier
     *
     * @var array
     */
    protected $_customizableContainerTypes = array('Parcel', 'Letter', 'Flat');


    public function __construct()
    {
        parent::__construct();
        $wsdlBasePath = Mage::getModuleDir('etc', 'Webshopapps_Endicia')  . DS . 'wsdl' . DS . 'Endicia' . DS;
        $this->_prodRateLabelServiceWsdl = $wsdlBasePath . 'EwsLabelService.wsdl';
        $this->_prodTrackServiceWsdl = $wsdlBasePath . 'ELSTrackServices.wsdl';
        $this->_devRateLabelServiceWsdl = $wsdlBasePath . 'EwsLabelService.wsdl';
        $this->_devTrackServiceWsdl = $wsdlBasePath . 'ELSTrackServices.wsdl';
    }

    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $r = $this->setBaseRequest($request);

        $this->_setAccessRequest($r);

        $this->_rawRequest = $r;

        return $this;
    }

    protected function _setAccessRequest(&$r){
        $r->setRequesterId($this->getConfigData('partner_id'));
        $r->setAccountId($this->getConfigData('account_id'));
        $r->setPassword($this->getConfigData('password'));
    }

    public function setBaseRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $r = new Varien_Object();

        $r->setAllowedMethods($this->getConfigData('allowed_methods'));

        $r->setWeight(ceil($request->getPackageWeight()*(float)$this->getConfigData('wt_units')));

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());
        $r->setDestCountryIso3(Mage::getModel('directory/country')->load($destCountry)->getIso3Code());
        $r->setDestCountryName(Mage::getModel('directory/country')->load($destCountry)->getName());
        $r->setMailClass($this->getDeliveryServiceLevel($destCountry));

        if ($request->getDestPostcode()) {
            $r->setDestPostal('US' == $r->getDestCountry() ? substr($request->getDestPostcode(), 0, 5) : $request->getDestPostcode());
        }

        return $r;
    }


    protected function _formRateRequest()
    {
        $r = $this->_rawRequest;

        $ratesOuterRequest = new stdClass();
        $ratesOuterRequest->RequesterID = $r->getRequesterId();

        $ratesOuterRequest->CertifiedIntermediary = array(
            'AccountID'            => $r->getAccountId(),
            'PassPhrase'                => $r->getPassword(),
        );
        $ratesOuterRequest->MailClass = $r->getMailClass();
        $ratesOuterRequest->WeightOz = $r->getWeight();
        $ratesOuterRequest->FromPostalCode = $r->getOrigPostal();
        $ratesOuterRequest->ToPostalCode = $r->getDestPostal();
        $ratesOuterRequest->CODAmount = '0';
        $ratesOuterRequest->InsuredValue = '0';
        $ratesOuterRequest->RegisteredMailValue = 0;
        $ratesOuterRequest->ToCountryCode = $r->getDestCountry();

        $services = new stdClass();
        $services->DeliveryConfirmation="ON";
        $ratesOuterRequest->Services = $services;

        $ratesRequest = new stdClass();
        $ratesRequest->PostageRatesRequest = $ratesOuterRequest;

        if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3NhZW5kaWNpYS9zaGlwX29uY2U=',
            'bGVnb2xpZ2h0','Y2FycmllcnMvd3NhZW5kaWNpYS9zZXJpYWw=')) {
         	Mage::helper('wsalogger/log')->postCritical('Webshopapps Endicia', base64_decode('TGljZW5zZQ=='), base64_decode('U2VyaWFsIEtleSBJbnZhbGlk'));
            return null;
        }

        if ($this->_debug) {
            Mage::helper('wsacommon/log')->postNotice('Webshopapps_Endicia','Request',$ratesRequest);
        }

        return $ratesRequest;
    }

    protected function _getQuotes()
    {
        $ratesRequest = $this->_formRateRequest();


        $requestString = serialize($ratesRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request' => $ratesRequest);
        if ($response === null) {
            try {
                $client = $this->_createRateSoapClient();
                $response = $client->CalculatePostageRates($ratesRequest);
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                Mage::logException($e);
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }
        if($this->_debug)
        {
            Mage::helper('wsalogger/log')->postInfo('Webshopapps_Endicia','Rates Response',$debugData);
        }
        return $this->_parseRateResponse($ratesRequest,$response);
    }

    protected function _parseRateResponse($ratesRequest,$response)
    {
        $costArr = array();
        $priceArr = array();

        if (is_object($response)) {
            $resp = $response->PostageRatesResponse;
            if (isset($resp)) {
                if($resp->Status == 0) {
                    $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
                    $ratesArray = $resp->PostagePrice;

                    if (is_array($ratesArray)) {
                        foreach ($ratesArray as $postageRate) {
                            $serviceName = (string)$postageRate->MailClass;
                            if (in_array($serviceName, $allowedMethods)) {

                                $amount = (string)$postageRate->TotalAmount;
                                $costArr[$serviceName]  = $amount;
                                $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                            }
                        }
                        asort($priceArr);
                    } else {
                        $singleRate = $resp->PostagePrice;
                        $serviceName = (string)$singleRate->MailClass;
                        if (in_array($serviceName, $allowedMethods)) {
                            $amount = (string)$singleRate->TotalAmount;
                            $costArr[$serviceName]  = $amount;
                            $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                        }
                    }
                } else {
                    if ($resp->Status != 0) {
                        Mage::helper('wsalogger/log')->postWarning('Webshopapps_Endicia','Error in response',$resp->ErrorMessage);
                    }
                }
            }
        }
        return $this->getResultSet($priceArr,$ratesRequest,$response,'');

    }

    protected function getResultSet($priceArr,$request,$response,$quoteId='') {

        $path = 'carriers/'.$this->_code.'/';
        $title = Mage::getStoreConfig($path.'title');
        $defaultMethodTitle = Mage::helper('usa')->__(Mage::getStoreConfig($path.'name'));

        if ($this->_debug) {
            Mage::helper('wsacommon/log')->postNotice($this->_code,'Price Arr',$priceArr);
        }

        $result = Mage::getModel('shipping/rate_result');
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($title);
            $error->setErrorMessage(Mage::getStoreConfig($path.'specificerrmsg'));
            $result->append($error);

            Mage::helper('wsalogger/log')->postWarning($this->_code,'No rates found','');
            Mage::helper('wsalogger/log')->postWarning($this->_code,'====== REQUEST:===== ',$request);
            Mage::helper('wsalogger/log')->postWarning($this->_code,'====== RESPONSE: ====== ',$response);
        } else {
            foreach ($priceArr as $method=>$price) {
                $methodTitle = $method == $this->_code ? $defaultMethodTitle : $this->getCode('method', $method);
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($title);
                $rate->setMethod($method);
                $rate->setMethodTitle($methodTitle);
                $rate->setPrice($price);
               $result->append($rate);
            }
        }
        return $result;
    }


    public function getCode($type, $code='')
    {
        $codes = array(

            'method'=>array(
                'Express' => Mage::helper('shipping')->__('Express Mail'),
                'Priority'=> Mage::helper('shipping')->__('Priority Mail'),
                'StandardPost' => Mage::helper('shipping')->__('Standard Post'),
                'PriorityExpress'=> Mage::helper('shipping')->__('Priority Mail Express'),
                'PriorityMailInternational'=> Mage::helper('shipping')->__('Priority Mail International'),
                'PriorityMailExpressInternational'=> Mage::helper('shipping')->__('Priority Mail Express International'),
                'First'=> Mage::helper('shipping')->__('First-Class Mail'),
                'MediaMail'=> Mage::helper('shipping')->__('Media Mail'),
                'ParcelSelect'=> Mage::helper('shipping')->__('Parcel Select'),
                //'CriticalMail'=> Mage::helper('shipping')->__('Critical Mail'),
                'ExpressMailInternational' => Mage::helper('shipping')->__('Express Mail International'),
                'PriorityMailInternational' => Mage::helper('shipping')->__('Priority Mail International'),
                'PriorityMailExpressInternational' => Mage::helper('shipping')->__('Priority Mail Express International'),
                'FirstClassMailInternational'=> Mage::helper('shipping')->__('First-Class Mail International'),
                'FirstClassPackageInternationalService'=> Mage::helper('shipping')->__('First-Class Package International Service'),
            ),

            'method_domestic'=>array(
                'Express' => Mage::helper('shipping')->__('Express Mail'),
                'Priority'=> Mage::helper('shipping')->__('Priority Mail'),
                'StandardPost' => Mage::helper('shipping')->__('Standard Post'),
                'PriorityExpress'=> Mage::helper('shipping')->__('Priority Mail Express'),
                'First'=> Mage::helper('shipping')->__('First-Class Mail'),
                'MediaMail'=> Mage::helper('shipping')->__('Media Mail'),
                'ParcelSelect'=> Mage::helper('shipping')->__('Parcel Select'),
            ),

            'method_international'=>array(
                'PriorityMailInternational'=> Mage::helper('shipping')->__('Priority Mail International'),
                'PriorityMailExpressInternational'=> Mage::helper('shipping')->__('Priority Mail Express International'),
                'ExpressMailInternational' => Mage::helper('shipping')->__('Express Mail International'),
                'PriorityMailInternational' => Mage::helper('shipping')->__('Priority Mail International'),
                'PriorityMailExpressInternational' => Mage::helper('shipping')->__('Priority Mail Express International'),
                'FirstClassMailInternational'=> Mage::helper('shipping')->__('First-Class Mail International'),
                'FirstClassPackageInternationalService'=> Mage::helper('shipping')->__('First-Class Package International Service'),
            ),

            'wt_units'=>array(
                '16' => Mage::helper("wsaendicia")->__('Pounds'),
                '1' => Mage::helper('wsaendicia')->__('Ounces'),
                '35.274'=>Mage::helper('wsaendicia')->__('Kilograms')
            ),

            'recredit'=>array(
                '10' => Mage::helper("wsaendicia")->__('$10.00'),
                '25' => Mage::helper('wsaendicia')->__('$25.00'),
                '50'=>Mage::helper('wsaendicia')->__('$50.00'),
                '100'=>Mage::helper('wsaendicia')->__('$100.00'),
                '250'=>Mage::helper('wsaendicia')->__('$250.00'),
                '500'=>Mage::helper('wsaendicia')->__('$500.00'),
                '1000'=>Mage::helper('wsaendicia')->__('$1,000.00'),
                '2500'=>Mage::helper('wsaendicia')->__('$2,500.00'),
                '5000'=>Mage::helper('wsaendicia')->__('$5,000.00'),
                '7500'=>Mage::helper('wsaendicia')->__('$7,500.00'),
                '10000'=>Mage::helper('wsaendicia')->__('$10,000.00'),
                '20000'=>Mage::helper('wsaendicia')->__('$20,000.00'),
            ),

            'delivery_conf_types' => array(
                'False' => Mage::helper('wsaendicia')->__('Not required'),
                'DeliveryConfirmation' => Mage::helper('wsaendicia')->__('Delivery Confirmation'),
                'SignatureConfirmation' => Mage::helper('wsaendicia')->__('Signature Confirmation'),
                'CertifiedMail' => Mage::helper('wsaendicia')->__('Certified Mail'),
            ),

            'container' => array(
                self::DELIVERY_DOMESTIC => array(
                    'First' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                        'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                    ),
                    'Priority' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                        'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                    ),
                    'Express' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                        'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                        'Letter' => Mage::helper('wsaendicia')->__('Letter'),
                    ),
                    'PriorityExpress' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                        'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                        'Letter' => Mage::helper('wsaendicia')->__('Letter'),
                    ),
                    'MediaMail' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                    ),
                    'StandardPost' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                    ),
                    'ParcelSelect' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                    ),

                ),
                self::DELIVERY_INTERNATIONAL => array(
                    'FirstClassMailInternational' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                        'Letter' => Mage::helper('wsaendicia')->__('Letter'),
                        'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                    ),
                    'FirstClassPackageInternationalService' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                    ),
                    'PriorityMailInternational' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                        'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                        'FlatRateEnvelope' => Mage::helper('wsaendicia')->__('Flat Rate Envelope'),
                    ),
                    'PriorityMailExpressInternational' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                        'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                        'FlatRateEnvelope' => Mage::helper('wsaendicia')->__('Flat Rate Envelope'),
                    ),
                    'ExpressMailInternational' => array(
                        'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                        'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                        'FlatRateEnvelope' => Mage::helper('wsaendicia')->__('Flat Rate Envelope'),
                    ),
                ),
                self::DELIVERY_ALL => array(
                    'Parcel' => Mage::helper('wsaendicia')->__('Parcel'),
                    'Flat' => Mage::helper('wsaendicia')->__('Flat'),
                )
            ),

            'error_message' => array(
                '1001'  => Mage::helper('wsaendicia')->__('Incorrect Account ID or Passphrase')

            ),

            'insurance' => array(
                self::INSURANCE_DISABLED => Mage::helper('wsaendicia')->__('Insurance Disabled'),
                'UspsOnline' => Mage::helper('wsaendicia')->__('USPS Online Insurance'),
                'Endicia' => Mage::helper('wsaendicia')->__('Endicia Parcel Insurance'),
            )
        );

        if (!isset($codes[$type])) {
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $k;
        }
        return $arr;
    }

    /**
     * Create rate soap client
     *
     * @return SoapClient
     */
    protected function _createRateSoapClient()
    {
        if($this->getConfigFlag('sandbox_mode')){
            return $this->_createSoapClient($this->_devRateLabelServiceWsdl,$this->_debug);
        }
        else{
            return $this->_createSoapClient($this->_prodRateLabelServiceWsdl,$this->_debug);
        }

    }

    /**
     * Create track soap client
     *
     * @return SoapClient
     */
    protected function _createTrackSoapClient()
    {
        if($this->getConfigFlag('sandbox_mode')){
            return $this->_createSoapClient($this->_devTrackServiceWsdl, $this->_debug, self::TRACK);
        } else {
            return $this->_createSoapClient($this->_prodTrackServiceWsdl, $this->_debug, self::TRACK);
        }
    }

    /**
     * Create shipment soap client
     *
     * @return SoapClient
     */
    protected function _createLabelSoapClient()
    {
        if($this->getConfigFlag('sandbox_mode')){
            return $this->_createSoapClient($this->_devRateLabelServiceWsdl, $this->_debug, self::LABEL);
        } else {
            return $this->_createSoapClient($this->_prodRateLabelServiceWsdl, $this->_debug, self::LABEL);
        }
    }



    protected function _createSoapClient($wsdl, $trace = false, $type=self::RATE)
    {
        $client = new SoapClient($wsdl, array('trace' => $trace));
        switch($type){
            case self::RATE:
                $client->__setLocation($this->getConfigFlag('sandbox_mode')
                        ? self::DEV_RATE_LABEL_URL
                        : self::PROD_RATE_LABEL_URL
                ); break;
            case self::TRACK:
                $client->__setLocation($this->getConfigFlag('sandbox_mode')
                        ? self::DEV_TRACK_URL
                        : self::PROD_TRACK_URL
                ); break;
            case self::LABEL:
                $client->__setLocation($this->getConfigFlag('sandbox_mode')
                        ? self::DEV_RATE_LABEL_URL
                        : self::PROD_RATE_LABEL_URL
                ); break;
            default:
                $client->__setLocation($this->getConfigFlag('sandbox_mode')
                        ? self::DEV_RATE_LABEL_URL
                        : self::PROD_RATE_LABEL_URL
                ); break;
        }
        return $client;
    }


    /**
     * Do request to shipment
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return array
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $this->_debug = Mage::helper('wsalogger')->isDebug($this->_modName);

        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('usa')->__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }
        $data = array();
        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($this->checkContainerType($package['params']['container']));
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageValue($package['params']['customs_value']);
            $request->setServices($package['params']['delivery_confirmation']);
            $request->setPackageParams(new Varien_Object($package['params']));
            $request->setPackageItems($package['items']);
            array_key_exists('add_insurance', $package['params']) ?
                $request->setInsuranceRequired($package['params']['add_insurance']) : $request->setInsuranceRequired(false);
            array_key_exists('insurance_value', $package['params']) ?
                $request->setInsuranceValue($package['params']['insurance_value']) : $request->setInsuranceValue(false);
            array_key_exists('endicia_shipmethod', $package['params']) ?
                $request->setShippingMethod($package['params']['endicia_shipmethod']) : $request->setSelectedShipMethod(false);
            $result = $this->_doShipmentRequest($request);
            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            } else {
                $data[] = array(
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content'   => $result->getShippingLabelContent()
                );
            }
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new Varien_Object(array(
            'info'   => $data
        ));
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }
        return $response;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * Return container types of carrier
     *
     * @param Varien_Object|null $params
     * @return array
     */
    public function getContainerTypes(Varien_Object $params = null)
    {
        $method             = $params->getMethod();
        $countryRecipient   = $params->getCountryRecipient();
        $serviceLevel = $this->getDeliveryServiceLevel($countryRecipient);

        if(!$this->getCode('method', $method)) {
            $method = Mage::getStoreConfig('carriers/wsaendicia/default_domestic');
        }
        $allContainers =  $this->getCode('container');
        $allowedContainers = $allContainers['All'];
        if($serviceLevel) {
            if(array_key_exists($method, $allContainers[$serviceLevel])) {
                $allowedContainers = $allContainers[$serviceLevel][$method];
            }
        }
        return $allowedContainers;
    }

    public function checkContainerType($container)
    {
        $allContainers =  $this->getCode('container');
        $containers = $allContainers['All'];
        if(array_key_exists($container, $containers)) {
            return $container;
        }
        return self::DEFAULT_CONTAINER;

    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param Varien_Object $request
     * @return Varien_Object
     */

    protected function _doShipmentRequest(Varien_Object $request){
       $this->_prepareShipmentRequest($request);
        $result = new Varien_Object();
        try {
            $client = $this->_createLabelSoapClient();
            $requestShip = $this->_formShipmentRequest($request);

            $response = $client->GetPostageLabel($requestShip);
            $debugData['request_sent'] = $requestShip;
            //response includes string of PDF label - too big to log
            $debugString =  "Status: " .$response->LabelRequestResponse->Status;
            if(isset($response->LabelRequestResponse->TrackingNumber)) {
                $debugString.= ' Tracking ID: ' .$response->LabelRequestResponse->TrackingNumber .' Final Postage: ' .$response->LabelRequestResponse->FinalPostage;
            }
            $debugData['response'] = $debugString;

        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            Mage::logException($e);
            $result->setErrors((string)'an issue: ' .$e->getMessage());
        }

        if (is_object($response)) {
            if ($response->LabelRequestResponse->Status != 0) {
                $result->setErrors((string)$response->LabelRequestResponse->ErrorMessage);
                $debugData['response'] = $response->LabelRequestResponse->ErrorMessage;
            }
            else {

               $result->setShippingLabelContent(base64_decode($response->LabelRequestResponse->Base64LabelImage));
                $result->setTrackingNumber($response->LabelRequestResponse->TrackingNumber);
                $this->_recordCostToOrder($request->getOrderShipment()->getOrder(), $response->LabelRequestResponse->FinalPostage);
            }
        }
        $result->setGatewayResponse($client->__getLastResponse());
        if($this->_debug)
        {
            Mage::helper('wsalogger/log')->postInfo('endicia','Request XML',$client->__getLastRequest());
            Mage::helper('wsalogger/log')->postInfo('endicia','Response',$debugData);
        }

        return $result;
    }

    /**
     * Prepare shipment request.
     * Validate and correct request information
     *
     * @param Varien_Object $request
     *
     */
    protected function _prepareShipmentRequest(Varien_Object $request)
    {
        $phonePattern = '/[\s\_\-\(\)]+/';
        $phoneNumber = $request->getShipperContactPhoneNumber();
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        if($request->getShipperAddressCountryCode() == self::USA_COUNTRY_ID && substr($phoneNumber, 0, 1) === '1' ) {
            $phoneNumber = substr($phoneNumber, 1);
        }
        if(strlen($phoneNumber) < 10) {
            $phoneNumber = str_pad($phoneNumber, 10, '0', STR_PAD_LEFT);
        }
        $request->setShipperContactPhoneNumber($phoneNumber);
        $phoneNumber = $request->getRecipientContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        if(strlen($phoneNumber) < 10) {
            $phoneNumber = str_pad($phoneNumber, 10, '0', STR_PAD_LEFT);
        }
        $request->setRecipientContactPhoneNumber($phoneNumber);
    }

    /**
     * Form array with appropriate structure for shipment request
     *
     * @param Varien_Object $request
     * @return array
     */
    protected function _formShipmentRequest(Varien_Object $request)
    {
        $r = new Varien_Object();
        $this->_request = $request;
        $this->_setAccessRequest($r);

        $packageItems = $request->getPackageItems();

        $shipRequest =  new StdClass();

        $shipRequest->RequesterID = $r->getRequesterId();
        //$shipRequest->LabelSize = '4x8';
        if(Mage::getStoreConfig('carriers/wsaendicia/label_pdf')) {
            $shipRequest->ImageFormat = 'PDF'; //EN-15
        }
        $shipRequest->AccountID = $r->getAccountId();
        $shipRequest->PassPhrase = $r->getPassword();
        $shipRequest->MailClass = $request->getShippingMethod();
        $shipRequest->CostCenter = 0;
        $shipRequest->Stealth = Mage::getStoreConfig('carriers/wsaendicia/stealth')? 'TRUE' :'FALSE';

        $shipRequest->Value = $request->getPackageValue();
        $shipRequest->Description = 'Goods purchased';

        $weightUnits = Zend_Measure_Weight::POUND;
        $shipDimensions  = new stdClass();
        foreach($request->getPackages() as $package) {
            $shipDimensions->Length = $this->_convertDimension($package['params']['length'], $package['params']['dimension_units']);
            $shipDimensions->Width = $this->_convertDimension($package['params']['width'],$package['params']['dimension_units']);
            $shipDimensions->Height = $this->_convertDimension($package['params']['height'],$package['params']['dimension_units']);
            $shipRequest->MailpieceShape = $this->checkContainerType($package['params']['container']);
            $weightUnits = $package['params']['weight_units'];
            break;
        }
        $shipRequest->WeightOz = $this->_convertWeight($request->getPackageWeight(), $weightUnits);

        $shipRequest->MailpieceDimensions = $shipDimensions;
        $isDomestic = $request->getRecipientAddressCountryCode() == self::USA_COUNTRY_ID;
        if(!$isDomestic) {
            $shipCustomsInfo = new stdClass();
            $shipCustomsInfo->ContentsType = 'Merchandise';
            $shipCustomsInfo->RestrictionType = 'None';
            $shipCustomsInfo->SendersCustomsReference = $request->getShipperContactCompanyName();
            $shipCustomsInfo->ImportersCustomsReference = $request->getRecipientContactPersonName();
            $shipCustomsInfo->LicenseNumber = '';
            $shipCustomsInfo->CertificateNumber = '';
            if($invoice = $request->getOrderShipment()->getOrder()->getInvoice()) {
                $invoiceNum = $invoice->getIncrementId();
            }
            else {
                $invoiceNum = '';
            }
            $shipCustomsInfo->InvoiceNumber = $invoiceNum;
            $shipCustomsInfo->EelPfc = 'NOEEI 30.37(a)';
            $customItems = new stdClass();
            foreach($packageItems as $item) {
                $oneCustomsItem = new stdClass();
                $oneCustomsItem->Description = $item['name'];
                $oneCustomsItem->Quantity = $item['qty'];
                $oneCustomsItem->Weight = $this->_convertWeight($item['weight'] * $item['qty'], $weightUnits);
                $oneCustomsItem->Value = $item['customs_value'] * $item['qty'];

                $customItems->CustomsItem[] = $oneCustomsItem;
            }
            $shipCustomsInfo->CustomsItems = $customItems;
            $shipRequest->CustomsInfo = $shipCustomsInfo;
            $shipRequest->CustomsCertify = 'FALSE';
            $shipRequest->CustomsSigner = '';
            $shipRequest->CustomsSendersCopy = 'true';

        }

        $shipRequest->SortType = 'Nonpresorted';
        $shipRequest->EntryFacility = "Other";
        $services = false;
        if($isDomestic) {
            foreach($this->getCode('delivery_conf_types') as $confCode => $description) {
                if($request->getServices() != 'False' && $this->_mapServices($request->getServices()) == $confCode) {
                    if(!$services) {
                        $services  = new stdClass();
                    }
                    $services->$confCode = "ON";
                }
            }
        }

        if($request->getInsuranceRequired()) {
            if(!$services) {
                $services  = new stdClass();
            }
            $services->InsuredMail = Mage::helper('wsaendicia')->getInsuranceProvider();
            $shipRequest->InsuredValue = $request->getInsuranceValue()? $request->getInsuranceValue() : $request->getPackageValue();
        }

        if($services) {
            $shipRequest->Services = $services;
        }

        $shipRequest->PartnerCustomerID = $request->getOrderShipment()->getOrder()->getIncrementId();
        $shipRequest->PartnerTransactionID = $request->getOrderShipment()->getOrder()->getIncrementId();

        if(Mage::getStoreConfig('carriers/wsaendicia/rubber_stamp_order')) {
            $shipRequest->RubberStamp1 = $request->getOrderShipment()->getOrder()->getIncrementId();
        }

        $rubberStamp1code = Mage::getStoreConfig('carriers/wsaendicia/rubber_stamp_1');
        $rubberStamp2code = Mage::getStoreConfig('carriers/wsaendicia/rubber_stamp_2');
        $rubberStamp3code = Mage::getStoreConfig('carriers/wsaendicia/rubber_stamp_3');

        if($rubberStamp1code != '' || $rubberStamp2code != '' || $rubberStamp3code != '') {
            $product = new Varien_Object();

            foreach ($packageItems as $packageItem) {
                $product = Mage::getModel('catalog/product')->load($packageItem['product_id']);
                break;
            }

            try {
                if ($product->getData($rubberStamp1code)) {
                    $rubberStamp1 = $product->getAttributeText($rubberStamp1code) ? $product->getAttributeText($rubberStamp1code) : $product->getData($rubberStamp1code);
                } else {
                    $rubberStamp1 = "";
                }

                if ($product->getData($rubberStamp2code)) {
                    $rubberStamp2 = $product->getAttributeText($rubberStamp2code) ? $product->getAttributeText($rubberStamp2code) : $product->getData($rubberStamp2code);
                } else {
                    $rubberStamp2 = "";
                }

                if ($product->getData($rubberStamp3code)) {
                    $rubberStamp3 = $product->getAttributeText($rubberStamp3code) ? $product->getAttributeText($rubberStamp3code) : $product->getData($rubberStamp3code);
                } else {
                    $rubberStamp3 = "";
                }

                if($rubberStamp1 != "") {
                    if(Mage::getStoreConfig('carriers/wsaendicia/rubber_stamp_order')) {
                        $shipRequest->RubberStamp1 = $request->getOrderShipment()->getOrder()->getIncrementId() . ": ". $rubberStamp1;
                    } else {
                        $shipRequest->RubberStamp1 = $rubberStamp1;
                    }
                }

                if($rubberStamp2 != "") {
                    $shipRequest->RubberStamp2 = $rubberStamp2;
                }

                if($rubberStamp3 != "") {
                    $shipRequest->RubberStamp3 = $rubberStamp3;
                }
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }

        $shipRequest->FromName = $request->getShipperContactCompanyName();
        $shipRequest->ReturnAddress1 = $request->getShipperAddressStreet();
        $shipRequest->FromCity = $request->getShipperAddressCity();
        $shipRequest->FromState = $request->getShipperAddressStateOrProvinceCode();
        $shipRequest->FromPostalCode = substr($request->getShipperAddressPostalCode(), 0, 5);
        if($isDomestic) $shipRequest->FromCountry = $request->getShipperAddressCountryCode();
        $shipRequest->FromPhone = $request->getShipperContactPhoneNumber();

        $shipRequest->ToName = $request->getRecipientContactPersonName();
        if($request->getRecipientContactCompanyName()) $shipRequest->ToCompany = $request->getRecipientContactCompanyName();
        $shipRequest->ToAddress1 = $request->getRecipientAddressStreet();
        $shipRequest->ToCity = $request->getRecipientAddressCity();
        $shipRequest->ToState = $request->getRecipientAddressStateOrProvinceCode();
        $shipRequest->ToPostalCode = $isDomestic ? substr($request->getRecipientAddressPostalCode(), 0, 5): substr($request->getRecipientAddressPostalCode(), 0, 15);
        if(!$isDomestic) $shipRequest->ToCountryCode = $request->getRecipientAddressCountryCode();
        if(!$isDomestic) $shipRequest->ToCountry = Mage::getModel('directory/country')->load($request->getRecipientAddressCountryCode())->getName();
        $shipRequest->ToPhone = $request->getRecipientContactPhoneNumber();
        $test = $this->getConfigFlag('sandbox_mode')? 'YES' : "NO";
        $shipRequest->Test = $test;
      //  $shipRequest->ImageFormat = 'PDF';

        $wholeRequest = new stdClass();
        $wholeRequest->LabelRequest = $shipRequest;

         return $wholeRequest;
    }

    protected function _recordCostToOrder($order, $cost)
    {
        $comment = Mage::helper('wsaendicia')->__("Endicia label cost is $". $cost);
        $order->addStatusHistoryComment($comment)
            ->setIsVisibleOnFront(false)
            ->setIsCustomerNotified(false);
        $order->save();
    }

    /**
     * Get tracking
     *
     * @param mixed $trackings
     * @return mixed
     */
    public function getTracking($trackings)
    {
       $this->_debug = Mage::helper('wsalogger')->isDebug($this->_modName);
        $this->_setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings=array($trackings);
        }

        foreach($trackings as $tracking){
            $this->_getXMLTracking($tracking);
        }

        return $this->_result;
    }

    /**
     * Set tracking request
     *
     * @return void
     */
    protected function _setTrackingReqeust()
    {
        $r = new Varien_Object();

        $r->setAccountId($this->getConfigData('account_id'));
        $r->setPassword($this->getConfigData('password'));

        $this->_rawTrackingRequest = $r;
    }

    /**
     * Send request for tracking
     *Different webserver to other functions, requires straight XML in request
     * @param array $tracking
     * @return void
     */
    protected function _getXMLTracking($tracking)
    {

        $trackRequest = $this->_formTrackRequest($tracking);
        $requestString = serialize($trackRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request sent' => $trackRequest);
        if ($response === null) {
            try {
                $client = $this->_createTrackSoapClient();
                $response = $client->StatusRequest($trackRequest);
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                Mage::logException($e);
                Mage::log($client->__getLastRequest());
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }
        if($this->_debug)
        {
            Mage::helper('wsalogger/log')->postInfo('endicia','Track Request XML',$client->__getLastRequest());
            Mage::helper('wsalogger/log')->postInfo('endicia','Track Response',$debugData);
        }

        $this->_parseTrackingResponse($tracking, $response);
    }

    /*
     * Create tracking request
     */
    protected function _formTrackRequest($tracking)
    {
        $test = $this->getConfigFlag('sandbox_mode')? 'YES' : "NO";
        $xml = "";
        $xml = $xml."<StatusRequest>";
        $xml = $xml."<AccountID>" .$this->_rawTrackingRequest->getAccountId() ."</AccountID>";
        $xml = $xml."<Test>" .$test ."</Test>";
        $xml = $xml."<FullStatus>YES</FullStatus>";
        $xml = $xml."<PassPhrase>" .$this->_rawTrackingRequest->getPassword() ."</PassPhrase>";
        $xml = $xml."<StatusList>";
        $xml = $xml."<PICNumber>" .$tracking ."</PICNumber>";
        $xml = $xml."</StatusList>";
        $xml = $xml."</StatusRequest>";

        return $xml;
    }

    /**
     * Parse tracking response
     *
     * @param array $trackingValue
     * @param stdClass $response
     */
    protected function _parseTrackingResponse($trackingValue, $response)
    {
        $errorTitle=null;

        if (is_object($response)) {
            if ($response->StatusResponse->ErrorMsg) {
                $errorTitle = (string)$response->StatusResponse->ErrorMsg;
            } elseif ($response->StatusResponse->StatusList) {
                $resultArray = array();
                $packageProgress = array();
                $trackingResponse = $response->StatusResponse->StatusList->PICNumber;
                $resultArray['status'] = $trackingResponse->Status;
                $i = 1;
                if($trackingResponse->StatusBreakdown) {
                    foreach ($trackingResponse->StatusBreakdown as $eventKey => $event) {
                        $tempArray = array();
                        $tempArray['activity'] = (string)$event;
                        $tempArray['status'] = (string)$event;
                        $packageProgress[] = $tempArray;
                    }
                }
                $resultArray['progressdetail'] = $packageProgress;
            }
        }

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }

        if (isset($resultArray)) {
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier('endicia');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingValue);
            $tracking->addData($resultArray);
            $this->_result->append($tracking);
        } else {
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('endicia');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingValue);
            $error->setErrorMessage($errorTitle ? $errorTitle : Mage::helper('usa')->__('Unable to retrieve tracking'));
            $this->_result->append($error);
        }
    }

    /**
     * Return delivery confirmation types of carrier
     *
     * @param Varien_Object|null $params
     * @return array|bool
     */
    public function getDeliveryConfirmationTypes(Varien_Object $params = null)
    {
        if ($params == null) {
            return array();
        }
         $countryRecipient = $params->getCountryRecipient();
        $serviceLevel = $this->getDeliveryServiceLevel($countryRecipient);
        if($serviceLevel == self::DELIVERY_DOMESTIC) {
                return $this->getCode('delivery_conf_types');
        }
         else {
             return array();
        }
        return array();
    }

    /**
     * Get delivery confirmation level based on origin/destination
     * Return null if delivery confirmation is not acceptable
     *
     * @var string $countyDest
     * @return int|null
     */
    public function getDeliveryServiceLevel($countyDest = null) {
       if (is_null($countyDest)) {
            return null;
        }

        if ($countyDest == self::USA_COUNTRY_ID) {
            return self::DELIVERY_DOMESTIC;
        }

        return self::DELIVERY_INTERNATIONAL;
    }

    /**
     * Get balance of Endicia account
     *
     * @return array
     */
    public function getPostageBalance()
    {
        $this->_debug = Mage::helper('wsalogger')->isDebug($this->_modName);
        $balanceRequest = $this->_formBalanceRequest();

        $requestString = serialize($balanceRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request' => $balanceRequest);
        if ($response === null) {
            try {
                $client = $this->_createRateSoapClient();
                $response = $client->GetAccountStatus($balanceRequest);
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                Mage::logException($e);
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }

        Mage::helper('wsalogger/log')->postInfo('Webshopapps_Endicia','Balance Enquiry Response',$debugData, $this->_debug);
        return $this->_parseBalanceResponse($response);

    }

    protected function _formBalanceRequest()
    {
        $r = new Varien_Object();
        $this->_setAccessRequest($r);
        $balanceRequest = new stdClass();
        $balanceRequest->RequesterID = $r->getRequesterId();
        $balanceRequest->RequestID = $r->getRequesterId() .'_' .time();

        $balanceRequest->CertifiedIntermediary = array(
            'AccountID'            => $r->getAccountId(),
            'PassPhrase'                => $r->getPassword(),
        );
        $wholeRequest = new stdClass();
        $wholeRequest->AccountStatusRequest = $balanceRequest;
        return $wholeRequest;
    }

    protected function  _parseBalanceResponse($response)
    {
        $result = array();
        if (is_object($response)) {
            $resp = $response->AccountStatusResponse;
            if (isset($resp)) {
                if($resp->Status == 0) {
                        $result[$resp->CertifiedIntermediary->AccountID] = $resp->CertifiedIntermediary->PostageBalance;
                } else {
                    if ($resp->Status != 0) {
                        Mage::helper('wsalogger/log')->postWarning('Webshopapps_Endicia','Error in balance response',$resp->ErrorMessage);
                        $errorMessage = $this->getCode('error_message', $resp->Status);
                        if(!$errorMessage) {
                            $errorMessage =  $resp->ErrorMessage;
                        }
                        $result[self::ERROR_CODE] =$errorMessage;
                    }

                }
            }

        }
        return $result;
    }

    /*
     * Purchase postage for Endicia account using recreditRequest web method
     */

    public function purchasePostage($amount = null)
    {
        $this->_debug = Mage::helper('wsalogger')->isDebug($this->_modName);

        if (!$amount) {
            Mage::helper('wsalogger/log')->postWarning('Webshopapps_Endicia','Buy Postage ','Postage amount is empty', $this->_debug);
        }
        $recreditRequest = $this->_formRecreditRequest($amount);

        $requestString = serialize($recreditRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request' => $recreditRequest);
        if ($response === null) {
            try {
                $client = $this->_createRateSoapClient();
                $response = $client->BuyPostage($recreditRequest);
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                Mage::logException($e);
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }

        Mage::helper('wsalogger/log')->postInfo('Webshopapps_Endicia','Buy Postage Response',$debugData, $this->_debug);
        return $this->_parseRecreditResponse($response);

    }

    protected function _formRecreditRequest($amount)
    {
        $r = new Varien_Object();
        $this->_setAccessRequest($r);
        $recreditRequest = new stdClass();
        $recreditRequest->RequesterID = $r->getRequesterId();
        $recreditRequest->RequestID = $r->getRequesterId() .'_' .time();

        $recreditRequest->CertifiedIntermediary = array(
            'AccountID'            => $r->getAccountId(),
            'PassPhrase'                => $r->getPassword(),
        );
        $recreditRequest->RecreditAmount = $amount;
        $wholeRequest = new stdClass();
        $wholeRequest->RecreditRequest = $recreditRequest;
        return $wholeRequest;

    }

    protected function _parseRecreditResponse($response)
    {
        $result = array();
        if (is_object($response)) {
            $resp = $response->RecreditRequestResponse;
            if (isset($resp)) {
                if($resp->Status == 0) {
                    $result[$resp->CertifiedIntermediary->AccountID] = $resp->CertifiedIntermediary->PostageBalance;
                } else {
                    if ($resp->Status != 0) {
                        Mage::helper('wsalogger/log')->postWarning('Webshopapps_Endicia','Error in purchase postage response',$resp->ErrorMessage);
                        $errorMessage = $this->getCode('error_message', $resp->Status);
                        if(!$errorMessage) {
                            $errorMessage =  $resp->ErrorMessage;
                        }
                        $result[self::ERROR_CODE] = $errorMessage;
                    }

                }
            }

        }
        return $result;

    }


    public function changePassPhrase($newPassphrase)
    {
        $this->_debug = Mage::helper('wsalogger')->isDebug($this->_modName);
        if (!$newPassphrase) {
            Mage::helper('wsalogger/log')->postWarning('Webshopapps_Endicia','Change pass phrase ','New pass phrase is empty', $this->_debug);
        }
        $passphraseRequest = $this->_formPassphraseRequest($newPassphrase);

        $requestString = serialize($passphraseRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request' => $passphraseRequest);
        if ($response === null) {
            try {
                $client = $this->_createRateSoapClient();
                $response = $client->ChangePassPhrase($passphraseRequest);
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                Mage::logException($e);
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }

        Mage::helper('wsalogger/log')->postInfo('Webshopapps_Endicia','Change Pass Phrase Response',$debugData, $this->_debug);
        return $this->_parsePassphraseResponse($response);

    }

    protected function _formPassphraseRequest($newPassphrase)
    {
        $r = new Varien_Object();
        $this->_setAccessRequest($r);
        $passphraseRequest = new stdClass();
        $passphraseRequest->RequesterID = $r->getRequesterId();
        $passphraseRequest->RequestID = $r->getRequesterId() .'_' .time();

        $passphraseRequest->CertifiedIntermediary = array(
            'AccountID'            => $r->getAccountId(),
            'PassPhrase'                => $r->getPassword(),
        );
        $passphraseRequest->NewPassPhrase = $newPassphrase;
        $wholeRequest = new stdClass();
        $wholeRequest->ChangePassPhraseRequest = $passphraseRequest;
        return $wholeRequest;

    }

    protected function _parsePassphraseResponse($response)
    {
        $result = array();
        if (is_object($response)) {
            $resp = $response->ChangePassPhraseRequestResponse;
            if (isset($resp)) {
                if($resp->Status == 0) {
                    $result[1] = $resp->RequestID;
                } else {
                    if ($resp->Status != 0) {
                        Mage::helper('wsalogger/log')->postWarning('Webshopapps_Endicia','Error in changing pass phrase',$resp->ErrorMessage);
                        $errorMessage = $this->getCode('error_message', $resp->Status);
                        if(!$errorMessage) {
                            $errorMessage =  $resp->ErrorMessage;
                        }
                        $result[self::ERROR_CODE] = $errorMessage;
                    }

                }
            }

        }
        return $result;

    }

    public function rollBack($data)
    {
        return true;
    }

    protected function _convertDimension($dimension, $unit)
    {
        if($unit != Zend_Measure_Length::INCH) {
            return round(Mage::helper('usa')->convertMeasureDimension(
                $dimension,
                $unit,
                Zend_Measure_Length::INCH
            ));
        }
        else {
            return $dimension;
        }
    }

    protected function _convertWeight($weight, $unit)
    {
        $weight = round(
            Mage::helper('usa')->convertMeasureWeight(
                $weight, $unit, Zend_Measure_Weight::OUNCE));

        return $weight;
    }

    protected function _mapServices($service)
    {
        foreach($this->getCode('delivery_conf_types') as $confCode => $description) {
            if($service != 'False' && $service == $confCode) {
                return $confCode;
            }
        }
        if($service != 'False') {
            return $this::DEFAULT_CONF_TYPE;
        }

        return false;
    }
}