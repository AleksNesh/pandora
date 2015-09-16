<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Adminhtml_UpslabelController extends Mage_Adminhtml_Controller_Action
{

    public $defConfParams;
    public $defParams;
    public $upsl;
    public $upsl2;
    
    public $paymentmethod;

    public function __construct($op1 = NULL, $op2 = NULL, $op3 = array())
    {
        if ($op1 != NULL) {
            return parent::__construct($op1, $op2, $op3);
        } else {
            return $this;
        }
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('upslabel/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function showlabelAction()
    {
        /*if (strlen(Mage::getStoreConfig('upslabel/additional_settings/order_number')) > 1) {*/
        $order_id = $this->getRequest()->getParam('order_id');
        $type = $this->getRequest()->getParam('type');
        $shipment_id = $this->getRequest()->getParam('shipment_id');
        $params = $this->getRequest()->getParams();
        $this->imOrder = Mage::getModel('sales/order')->load($order_id);

        

        $this->loadLayout();
        $AccessLicenseNumber = Mage::getStoreConfig('upslabel/credentials/accesslicensenumber');
        $UserId = Mage::getStoreConfig('upslabel/credentials/userid');
        $Password = Mage::getStoreConfig('upslabel/credentials/password');
        $shipperNumber = Mage::getStoreConfig('upslabel/credentials/shippernumber');

        $lbl = Mage::getModel('upslabel/ups');
        $lbl->setCredentials($AccessLicenseNumber, $UserId, $Password, $shipperNumber);
        $collections = Mage::getModel('upslabel/upslabel');
        $collection = $collections->getCollection()->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 0);
        $firstItem = $collection->getFirstItem();
        if ($type == 'shipment') {
            $backLink = $this->getUrl('adminhtml/sales_order_shipment/view/shipment_id/' . $shipment_id);

        } else {
            $backLink = $this->getUrl('adminhtml/sales_order_creditmemo/view/creditmemo_id/' . $shipment_id);
        }
        if ($firstItem->getShipmentId() != $shipment_id) {
            $arrPackagesOld = $this->getRequest()->getParam('package');
            if (count($arrPackagesOld) > 0) {
                foreach ($arrPackagesOld AS $k => $v) {
                    $i = 0;
                    foreach ($v AS $d => $f) {
                        $arrPackages[$i][$k] = $f;
                        $i += 1;
                    }
                }
                unset($v, $k, $i, $d, $f);
            }
            $lbl = $this->setParams($lbl, $params, $arrPackages );
            $upsl2 = NULL;
            if ($type == 'shipment') {
                $upsl = $lbl->getShip();
                if ($params['default_return'] == 1) {
                    $lbl->serviceCode = array_key_exists('default_return_servicecode', $params) ? $params['default_return_servicecode'] : '';
                    $upsl2 = $lbl->getShipFrom();
                }
            } else if ($type == 'refund') {
                $upsl = $lbl->getShipFrom();
            } else if ($type == 'ajaxprice_shipment') {
                $upsl = $lbl->getShipPrice();
                echo $upsl;
                exit;
            } else if ($type == 'ajaxprice_refund') {
                $upsl = $lbl->getShipPriceFrom();
                echo $upsl;
                exit;
            }

            $this->saveDB($upsl, $upsl2, $params, $order_id, $shipment_id, $type);
            if (!array_key_exists('error', $upsl) || !$upsl['error']) {
                Mage::register('order_id', $order_id);
                Mage::register('shipment_id', $shipment_id);
                Mage::register('upsl', $upsl);
                if ($params['default_return'] == 1) {
                    Mage::register('upsl2', $upsl2);
                }
                Mage::register('error', array());
            } else {
                Mage::register('error', $upsl);
            }
        } else {
            Mage::register('order_id', $order_id);
            Mage::register('shipment_id', $shipment_id);
            Mage::register('upsl', $collection->getData());
            $collection = $collections->getCollection()->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 1)->getFirstItem();
            Mage::register('upsl2', $collection->getData());
            Mage::register('error', array());
        }
        Mage::register('type', $type);
        Mage::register('backLink', $backLink);

        $path_upsdir = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "update" . DS;
        if (!file_exists($path_upsdir . 'last_update.txt') || (int)file_get_contents($path_upsdir . 'last_update.txt') < time() - 82400) {
            Mage::getModel('upslabel/cron')->update();
        }

        $this->renderLayout();
        /* } else {
             echo Mage::helper('adminhtml')->__('Error: Required to fill in the Order number in the module configuration');
         }*/
    }

    public
    function saveDB($upsl, $upsl2 = NULL, $params, $order_id, $shipment_id, $type)
    {
        
        $upslabel = Mage::getModel('upslabel/upslabel');
        $colls2 = $upslabel->getCollection()->addFieldToFilter('order_id', $order_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 1);
        if (count($colls2) > 0) {
            foreach ($colls2 AS $c) {
                $c->delete();
            }
        }
        if (!array_key_exists('error', $upsl) || !$upsl['error']) {
            if ($shipment_id == 0 && $type == "shipment") {
                if ($this->imOrder->canShip()) {
                    $itemQty = $this->imOrder->getItemsCollection()->count();
                    $shipment = Mage::getModel('sales/service_order', $this->imOrder)->prepareShipment($itemQty);
                    $shipment = new Mage_Sales_Model_Order_Shipment_Api();
                    $shipmentId = $shipment->create($this->imOrder->getIncrementId(), array(), '', true, false);
                    $shipment_id = Mage::getModel('sales/order_shipment')->load($shipmentId, 'increment_id')->getId();
                } else {
                    $shipment = $this->imOrder->getShipmentsCollection()->getFirstItem();
                    $shipment_id = $shipment->getId();
                }
            }
            foreach ($upsl['arrResponsXML'] AS $upsl_one) {
                $upslabel = Mage::getModel('upslabel/upslabel');
                $upslabel->setTitle('Order ' . $order_id . ' TN' . $upsl_one['trackingnumber']);
                $upslabel->setOrderId($order_id);
                $upslabel->setShipmentId($shipment_id);
                $upslabel->setType($type);
                /*$upslabel->setBase64Image();*/
                $upslabel->setTrackingnumber($upsl_one['trackingnumber']);
                $upslabel->setShipmentidentificationnumber($upsl['shipidnumber']);
                $upslabel->setShipmentdigest($upsl['digest']);
                $upslabel->setLabelname('label' . $upsl_one['trackingnumber'] . '.' . strtolower($upsl_one['type_print']));
                $upslabel->setStatustext(Mage::helper('adminhtml')->__('Successfully'));
                $upslabel->setTypePrint($upsl_one['type_print']);
                $upslabel->setStatus(0);
                $upslabel->setCreatedTime(Date("Y-m-d H:i:s"));
                $upslabel->setUpdateTime(Date("Y-m-d H:i:s"));
                if ($upslabel->save() !== FALSE && Mage::getStoreConfig('upslabel/printing/automatic_printing') == 1 /*&& $upsl_one['type_print'] != "GIF"*/) {
                    Mage::helper('upslabel/help')->sendPrint($upsl_one['graphicImage'], $storeId, $upsl_one['type_print']);
                }

                $upslabel = Mage::getModel('upslabel/labelprice');
                $upslabel->setOrderId($order_id);
                $upslabel->setShipmentId($shipment_id);
                $upslabel->setPrice($upsl['price']['price'] . " " . $upsl['price']['currency']);
                $upslabel->setType($type);
                $upslabel->save();
                $this->upsl = $upslabel;
            }
            if ($params['default_return'] == 1) {
                if (isset($upsl2) && !empty($upsl2) && (!array_key_exists('error', $upsl2) || !$upsl2['error'])) {
                    foreach ($upsl2['arrResponsXML'] AS $upsl_one) {
                        $upslabel = Mage::getModel('upslabel/upslabel');
                        $upslabel->setTitle('Order ' . $order_id . ' TN' . $upsl_one['trackingnumber']);
                        $upslabel->setOrderId($order_id);
                        $upslabel->setShipmentId($shipment_id);
                        $upslabel->setType($type);
                        /*$upslabel->setBase64Image();*/
                        $upslabel->setTrackingnumber($upsl_one['trackingnumber']);
                        $upslabel->setShipmentidentificationnumber($upsl['shipidnumber']);
                        $upslabel->setShipmentdigest($upsl['digest']);
                        $upslabel->setLabelname('label' . $upsl_one['trackingnumber'] . '.' . strtolower($upsl_one['type_print']));
                        $upslabel->setStatustext(Mage::helper('adminhtml')->__('Successfully'));
                        $upslabel->setTypePrint($upsl_one['type_print']);
                        $upslabel->setStatus(0);
                        $upslabel->setCreatedTime(Date("Y-m-d H:i:s"));
                        $upslabel->setUpdateTime(Date("Y-m-d H:i:s"));
                        if ($upslabel->save() !== FALSE && Mage::getStoreConfig('upslabel/printing/automatic_printing') == 1 /*&& $upsl_one['type_print'] != "GIF"*/) {
                            Mage::helper('upslabel/help')->sendPrint($upsl_one['graphicImage']);
                        }

                        $upslabel = Mage::getModel('upslabel/labelprice');
                        $upslabel->setOrderId($order_id);
                        $upslabel->setShipmentId($shipment_id);
                        $upslabel->setPrice($upsl2['price']['price'] . " " . $upsl2['price']['currency']);
                        $upslabel->setType($type);
                        $upslabel->save();
                        $this->upsl2 = $upslabel;
                    }
                    if ($params['addtrack'] == 1 && $type == 'shipment') {
                        $trTitle = 'United Parcel Service (return)';
                        $shipment = Mage::getModel('sales/order_shipment')->load($shipment_id);
                        foreach ($upsl2['arrResponsXML'] AS $upsl_one1) {
                            $track = Mage::getModel('sales/order_shipment_track')
                                ->setNumber(trim($upsl_one1['trackingnumber']))
                                ->setCarrierCode('ups')
                                ->setTitle($trTitle);
                            $shipment->addTrack($track);
                        }
                        $shipment->save();
                    }
                } else {
                    $upslabel = Mage::getModel('upslabel/upslabel');
                    $upslabel->setTitle('Order ' . $order_id);
                    $upslabel->setOrderId($order_id);
                    $upslabel->setShipmentId($shipment_id);
                    $upslabel->setType($type);
                    $upslabel->setStatustext($upsl2['errordesc']);
                    $upslabel->setStatus(1);
                    $upslabel->setCreatedTime(Date("Y-m-d H:i:s"));
                    $upslabel->setUpdateTime(Date("Y-m-d H:i:s"));
                    $upslabel->save();
                    $this->upsl2 = $upslabel;
                }
            }

            if ($params['addtrack'] == 1 && $type == 'shipment') {
                $trTitle = 'United Parcel Service';
                $shipment = Mage::getModel('sales/order_shipment')->load($shipment_id);
                foreach ($upsl['arrResponsXML'] AS $upsl_one1) {
                    $track = Mage::getModel('sales/order_shipment_track')
                        ->setNumber(trim($upsl_one1['trackingnumber']))
                        ->setCarrierCode('ups')
                        ->setTitle($trTitle);
                    $shipment->addTrack($track);
                }
                $shipment->save();
            }
        } else {
            $upslabel = Mage::getModel('upslabel/upslabel');
            $upslabel->setTitle('Order ' . $order_id);
            $upslabel->setOrderId($order_id);
            $upslabel->setShipmentId($shipment_id);
            $upslabel->setType($type);
            $upslabel->setStatustext($upsl['errordesc']);
            $upslabel->setStatus(1);
            $upslabel->setCreatedTime(Date("Y-m-d H:i:s"));
            $upslabel->setUpdateTime(Date("Y-m-d H:i:s"));
            $upslabel->save();
            $this->upsl = $upslabel;
        }
        return $upslabel;
    }

    public
    function setParams($lbl, $params, $packages )
    {
        $configOptions = new Infomodus_Upslabel_Model_Config_Options;
        $configMethod = new Infomodus_Upslabel_Model_Config_Upsmethod;
        $lbl->packages = $packages;
        
        $lbl->shipmentDescription = Infomodus_Upslabel_Helper_Help::escapeXML($params['shipmentdescription']);
        $lbl->shipperName = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipper_no'] . '/companyname'));
        $lbl->shipperAttentionName = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipper_no'] . '/attentionname'));
        $lbl->shipperPhoneNumber = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipper_no'] . '/phonenumber'));
        $lbl->shipperAddressLine1 = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipper_no'] . '/addressline1'));
        $lbl->shipperCity = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipper_no'] . '/city'));
        $lbl->shipperStateProvinceCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipper_no'] . '/stateprovincecode'));
        $lbl->shipperPostalCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipper_no'] . '/postalcode'));
        $lbl->shipperCountryCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipper_no'] . '/countrycode'));

        $lbl->shiptoCompanyName = Infomodus_Upslabel_Helper_Help::escapeXML($params['shiptocompanyname']);
        $lbl->shiptoAttentionName = Infomodus_Upslabel_Helper_Help::escapeXML($params['shiptoattentionname']);
        $lbl->shiptoPhoneNumber = Infomodus_Upslabel_Helper_Help::escapeXML($params['shiptophonenumber']);
        $lbl->shiptoAddressLine1 = trim(Infomodus_Upslabel_Helper_Help::escapeXML($params['shiptoaddressline1']));
        $lbl->shiptoAddressLine2 = trim(Infomodus_Upslabel_Helper_Help::escapeXML($params['shiptoaddressline2']));
        $lbl->shiptoCity = Infomodus_Upslabel_Helper_Help::escapeXML($params['shiptocity']);
        $lbl->shiptoStateProvinceCode = Infomodus_Upslabel_Helper_Help::escapeXML($configOptions->getProvinceCode($params['shiptostateprovincecode']));
        $lbl->shiptoPostalCode = Infomodus_Upslabel_Helper_Help::escapeXML($params['shiptopostalcode']);
        $lbl->shiptoCountryCode = Infomodus_Upslabel_Helper_Help::escapeXML($params['shiptocountrycode']);
        $lbl->residentialAddress = $params['residentialaddress'];

        $lbl->shipfromCompanyName = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipfrom_no'] . '/companyname'));
        $lbl->shipfromAttentionName = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipfrom_no'] . '/attentionname'));
        $lbl->shipfromPhoneNumber = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipfrom_no'] . '/phonenumber'));
        $lbl->shipfromAddressLine1 = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipfrom_no'] . '/addressline1'));
        $lbl->shipfromCity = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipfrom_no'] . '/city'));
        $lbl->shipfromStateProvinceCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipfrom_no'] . '/stateprovincecode'));
        $lbl->shipfromPostalCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipfrom_no'] . '/postalcode'));
        $lbl->shipfromCountryCode = Infomodus_Upslabel_Helper_Help::escapeXML(Mage::getStoreConfig('upslabel/address_' . $params['shipfrom_no'] . '/countrycode'));

        $lbl->serviceCode = array_key_exists('serviceCode', $params) ? $params['serviceCode'] : '';
        $lbl->serviceDescription = $configMethod->getUpsMethodName(array_key_exists('serviceCode', $params) ? $params['serviceCode'] : '');

        $lbl->weightUnits = array_key_exists('weightunits', $params) ? $params['weightunits'] : '';
        $lbl->weightUnitsDescription = Infomodus_Upslabel_Helper_Help::escapeXML(array_key_exists('weightunitsdescription', $params) ? $params['weightunitsdescription'] : '');

        $lbl->includeDimensions = array_key_exists('includedimensions', $params) ? $params['includedimensions'] : 0;
        $lbl->unitOfMeasurement = array_key_exists('unitofmeasurement', $params) ? $params['unitofmeasurement'] : '';
        $lbl->unitOfMeasurementDescription = Infomodus_Upslabel_Helper_Help::escapeXML(array_key_exists('unitofmeasurementdescription', $params) ? $params['unitofmeasurementdescription'] : '');

        $lbl->adult = Infomodus_Upslabel_Helper_Help::escapeXML($params['adult']);

        $lbl->codYesNo = array_key_exists('cod', $params) ? $params['cod'] : '';
        $lbl->currencyCode = array_key_exists('currencycode', $params) ? $params['currencycode'] : '';
        $lbl->codMonetaryValue = array_key_exists('codmonetaryvalue', $params) ? $params['codmonetaryvalue'] : '';
        $lbl->codFundsCode = array_key_exists('codfundscode', $params) ? $params['codfundscode'] : '';
        $lbl->carbon_neutral = array_key_exists('carbon_neutral', $params) ? $params['carbon_neutral'] : '';

        if (array_key_exists('qvn', $params) && $params['qvn'] > 0) {
            $lbl->qvn = 1;
            $lbl->qvn_code = $params['qvn_code'];
        }
        $lbl->qvn_email_shipper = $params['qvn_email_shipper'];
        $lbl->qvn_email_shipto = $params['qvn_email_shipto'];

        if ($lbl->shipfromCountryCode != $lbl->shiptoCountryCode) {
            $lbl->shipmentcharge = array_key_exists('shipmentcharge', $params) ? $params['shipmentcharge'] : 0;
        }

        if (array_key_exists('invoicelinetotalyesno', $params) && $params['invoicelinetotalyesno'] > 0) {
            $lbl->invoicelinetotal = array_key_exists('invoicelinetotal', $params) ? $params['invoicelinetotal'] : '';
        } else {
            $lbl->invoicelinetotal = '';
        }
        if (isset($params['upsaccount']) && $params['upsaccount'] != 0) {
            $lbl->upsAccount = 1;
            $lbl->accountData = Mage::getModel('upslabel/account')->load($params['upsaccount']);
        }

        $lbl->adult = Infomodus_Upslabel_Helper_Help::escapeXML($params['adult']);

        $lbl->testing = $params['testing'];

        $lbl->saturdayDelivery = isset($params['saturday_delivery']) ? $params['saturday_delivery'] : "";
        if (isset($params['accesspoint'])) {
            $lbl->accesspoint = $params['accesspoint'];
            if ($lbl->accesspoint == 1) {
                $lbl->accesspoint_type = Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_type']);
                $lbl->accesspoint_name = isset($params['accesspoint_name']) ? Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_name']) : '';
                $lbl->accesspoint_atname = isset($params['accesspoint_atname']) ? Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_atname']) : '';
                $lbl->accesspoint_appuid = isset($params['accesspoint_appuid']) ? Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_appuid']) : '';
                $lbl->accesspoint_street = Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_street']);
                $lbl->accesspoint_street1 = isset($params['accesspoint_street1']) ? Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_street1']) : '';
                $lbl->accesspoint_street2 = isset($params['accesspoint_street2']) ? Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_street2']) : '';
                $lbl->accesspoint_city = Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_city']);
                $lbl->accesspoint_provincecode = isset($params['accesspoint_provincecode']) ? $params['accesspoint_provincecode'] : '';
                $lbl->accesspoint_postal = Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_postal']);
                $lbl->accesspoint_country = Infomodus_Upslabel_Helper_Help::escapeXML($params['accesspoint_country']);
            }
        }
        return $lbl;
    }

    public
    function intermediateAction()
    {
        /* if (strlen(Mage::getStoreConfig('upslabel/additional_settings/order_number')) > 1) {*/
        $this->loadLayout();
        //$block = $this->getLayout()->getBlock('intermediate');
        $order_id = $this->getRequest()->getParam('order_id');
        $type = $this->getRequest()->getParam('type');
        $shipment_id = $this->getRequest()->getParam('shipment_id');
        $this->intermediatehandy($order_id, $type, $shipment_id);
        Mage::register('order', $this->imOrder);
        Mage::register('shippingAmount', $this->shippingAmount['shipping_amount']);
        Mage::register('shipment', $this->imShipment);
        Mage::register('type', $type);
        Mage::register('upsAccounts', $this->upsAccounts);
        Mage::register('shipmentTotalPrice', $this->shipmentTotalPrice);
        Mage::register('shipmentTotalWeight', $this->totalWight);
        Mage::register('shipByUps', $this->shipByUps);
        Mage::register('shipByUpsCode', $this->shipByUpsCode);
        Mage::register('shipTo', $this->shippingAddress);
        Mage::register('shipByUpsMethodName', $this->shipByUpsMethodName);
        Mage::register('shipByUpsMethods', $this->configMethod->getUpsMethods());
        Mage::register('unitofmeasurement', $this->configOptions->getUnitOfMeasurement());
        Mage::register('sku', $this->sku);
        Mage::register('defParams', $this->defParams);
        Mage::register('defConfParams', $this->defConfParams);
        $this->renderLayout();
        /*} else {
            echo Mage::helper('adminhtml')->__('Error: Required to fill in the Order number in the module configuration');
        }*/

    }

    public
    function intermediatehandy($order_id, $type, $shipment_id = NULL)
    {
        $this->configOptions = new Infomodus_Upslabel_Model_Config_Options;
        $this->configMethod = new Infomodus_Upslabel_Model_Config_Upsmethod;

        $this->imOrder = Mage::getModel('sales/order')->load($order_id);
            
        $this->paymentmethod = $this->imOrder->getPayment()->getData();
        $this->paymentmethod = $this->paymentmethod['method'];
        $this->shippingAddress = $this->imOrder->getShippingAddress();
        if (Mage::getStoreConfig('carriers/upsap/active') == 1 && Mage::helper('core')->isModuleOutputEnabled("Infomodus_Upsap")) {
            $modelAccessPoint = Mage::getModel("upsap/accesspoint")->getCollection()->addFieldToFilter('order_id', $order_id)->getFirstItem()->getData();
            if (count($modelAccessPoint) > 0) {
                $this->shippingAddress = $this->imOrder->getBillingAddress();
            }
        }

        if ($shipment_id !== NULL) {
            if ($type == 'shipment') {
                $this->imShipment = Mage::getModel('sales/order_shipment')->load($shipment_id);
                if ($this->imShipment) {
                    $this->shippingAmount = $this->imShipment->getShippingAddress()->getOrder()->getData();
                }
            } else {
                $this->imShipment = Mage::getModel('sales/order_creditmemo')->load($shipment_id);
                if ($this->imShipment) {
                    $this->shippingAmount = $this->imShipment->getShippingAddress()->getOrder()->getData();
                }
            }

            $shipmentAllItems = $this->imShipment->getAllItems();
        } else {
            $this->shippingAmount = $this->imOrder->getData();
            $shipmentAllItems = $this->imOrder->getAllItems();
        }
        /*print_r($this->shippingAmount);
        print_r($shipmentAllItems);*/
        $totalPrice = 0;
        $this->totalWight = 0;
        $totalShipmentQty = 0;
        $this->sku = array();
        foreach ($shipmentAllItems AS $item) {
            $itemData = $item->getData();
            $this->sku[] = $itemData['sku'];
            if (!isset($itemData['qty'])) {
                $itemData['qty'] = $itemData['qty_ordered'];
            }
            if (!isset($itemData['weight'])) {
                $wItems = $this->imOrder->getAllItems();
                foreach ($wItems AS $w) {
                    if ($w->getProductId() == $itemData["product_id"]) {
                        $itemData['weight'] = $w->getWeight();
                    }
                }
            }
            $totalPrice += $itemData['price'] * $itemData['qty'];
            $this->totalWight += $itemData['weight'] * $itemData['qty'];
            $totalShipmentQty += $itemData['qty'];
        }
        $this->sku = implode(",", $this->sku);
        $totalQty = 0;
        foreach ($this->imOrder->getAllItems() AS $item) {
            $itemData = $item->getData();
            $totalQty += $itemData['qty_ordered'];
        }
        $this->upsAccounts = array("Shipper");
        $upsAcctModel = Mage::getModel('upslabel/account')->getCollection();
        foreach ($upsAcctModel AS $key => $u1) {
            $this->upsAccounts[$key] = $u1->getCompanyname();
        }
        $this->shipmentTotalPrice = $this->shippingAmount['grand_total'];
        $this->defConfParams = array();
        $this->defConfParams['upsaccount'] = 0;

        $ship_method = $this->imOrder->getShippingMethod();
        $shippingInternational = $this->shippingAddress->getCountryId() == Mage::getStoreConfig('upslabel/address_' . Mage::getStoreConfig('upslabel/shipping/defaultshipfrom') . '/countrycode') ? 0 : 1;
        $this->shipByUps = preg_replace("/^ups_.{1,4}$/", 'ups', $ship_method);

        if ($this->shipByUps == 'ups') {
            $this->shipByUpsCode = $this->configMethod->getUpsMethodNumber(preg_replace("/^ups_(.{2,4})$/", '$1', $ship_method));
            $this->shipByUpsMethodName = $this->configMethod->getUpsMethodName($this->shipByUpsCode);
            $this->defConfParams['serviceCode'] = $this->shipByUpsCode;
        } else if ($this->shipByUps = preg_replace("/^upsap_.{1,100}$/", 'upsap', $ship_method) == 'upsap') {
            $this->shipByUps = 'ups';
            $this->shipByUpsCode = explode("_", $ship_method);
            $this->shipByUpsCode = $this->shipByUpsCode[2];
            $this->defConfParams['serviceCode'] = $this->shipByUpsCode;
            $this->shipByUpsMethodName = $this->configMethod->getUpsMethodName($this->shipByUpsCode);
        } else if (Mage::getStoreConfig('upslabel/shipping/shipping_method_native') == 1) {
            $modelConformity = Mage::getModel("upslabel/conformity")->getCollection()->addFieldToFilter('method_id', $this->shipByUps)->addFieldToFilter('store_id', 
                    1) /*->addFieldToFilter('international', $shippingInternational)*/
                ->getSelect()->where('CONCAT(",", country_ids, ",") LIKE "%,' . $this->shippingAddress->getCountryId() . ',%"')->query()->fetch();
            if ($modelConformity && count($modelConformity) > 0) {
                $this->defConfParams['serviceCode'] = $modelConformity["upsmethod_id"];
            }
        }
        if (!isset($this->defConfParams['serviceCode'])) {
            $this->defConfParams['serviceCode'] = $shippingInternational == 0 ? Mage::getStoreConfig('upslabel/shipping/defaultshipmentmethod') : Mage::getStoreConfig('upslabel/shipping/defaultshipmentmethodworld');
        }

        if ($this->totalWight <= 0) {
            $this->totalWight = Mage::getStoreConfig('upslabel/weightdimension/defweigth');
        }
        if (
            $type == 'shipment' && Mage::getStoreConfig('upslabel/frontend_autocreate_label/frontend_multipackes_enable') == 1
        ) {
            $i = 0;
            $defParArr_1 = array();
            foreach ($shipmentAllItems AS $item) {
                $itemData = $item->getData();
                if (!isset($itemData['qty'])) {
                    $itemData['qty'] = $itemData['qty_ordered'];
                }
                if (!isset($itemData['weight'])) {
                    foreach ($this->imOrder->getAllItems() AS $w) {
                        if ($w->getProductId() == $itemData["product_id"]) {
                            $itemData['weight'] = $w->getWeight();
                        }
                    }
                }
                $myproduct = Mage::getModel('catalog/product')->load($itemData['product_id']);
                $myproduct = $myproduct->getData();
                for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                    $is_attribute = 0;
                    if (Mage::getStoreConfig('upslabel/frontend_autocreate_label/packages_by_attribute_enable') == 1) {
                        if (isset($myproduct[Mage::getStoreConfig('upslabel/frontend_autocreate_label/packages_by_attribute_code')])) {
                            $attribute = explode(";", trim($myproduct[Mage::getStoreConfig('upslabel/frontend_autocreate_label/packages_by_attribute_code')], ";"));
                            if (count($attribute) > 1) {
                                $rvaPrice = $itemData['price'];
                                foreach ($attribute AS $v) {
                                    $itemData['weight'] = $v;
                                    $itemData['price'] = round($rvaPrice / count($attribute), 2);
                                    $defParArr_1[$i] = $this->setDefParams($itemData , $type);
                                    $i++;
                                }
                                $is_attribute = 1;
                            }
                        }
                    }
                    if ($is_attribute !== 1) {
                        $defParArr_1[$i] = $this->setDefParams($itemData , $type);
                        $i++;
                    }
                }
            }
            $this->defParams = $defParArr_1;
        } else {
            $this->defParams = array();
            $i = 0;
            $rvaShipmentTotalPriceStart = $this->shipmentTotalPrice;
            $rvaShipmentTotalPrice = $rvaShipmentTotalPriceStart;
            if (Mage::getStoreConfig('upslabel/frontend_autocreate_label/packages_by_attribute_enable') == 1 && $type == 'shipment') {
                foreach ($shipmentAllItems AS $item) {
                    $itemData = $item->getData();
                    if (!isset($itemData['qty'])) {
                        $itemData['qty'] = $itemData['qty_ordered'];
                    }
                    if (!isset($itemData['weight'])) {
                        foreach ($this->imOrder->getAllItems() AS $w) {
                            if ($w->getProductId() == $itemData["product_id"]) {
                                $itemData['weight'] = $w->getWeight();
                            }
                        }
                    }
                    $itemData2 = $itemData;
                    $myproduct = Mage::getModel('catalog/product')->load($itemData['product_id']);
                    $myproduct = $myproduct->getData();
                    for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                        if (isset($myproduct[Mage::getStoreConfig('upslabel/frontend_autocreate_label/packages_by_attribute_code')])) {
                            $attribute = explode(";", trim($myproduct[Mage::getStoreConfig('upslabel/frontend_autocreate_label/packages_by_attribute_code')], ";"));
                            if (count($attribute) > 1) {
                                foreach ($attribute AS $v) {
                                    $this->totalWight = $this->totalWight - $itemData2['weight'];
                                    $itemData['price'] = round($itemData2['price'] / count($attribute), 2);
                                    $itemData['weight'] = $v;
                                    $this->defParams[$i] = $this->setDefParams($itemData , $type);
                                    $i++;
                                }
                                $rvaShipmentTotalPrice = $rvaShipmentTotalPrice - $itemData2['price'];
                            }
                        }
                    }
                }
                $this->shipmentTotalPrice = $rvaShipmentTotalPrice;
            }
            if ($this->totalWight > 0) {
                $this->defParams[$i] = $this->setDefParams(NULL , $type);
            }
            $this->shipmentTotalPrice = $rvaShipmentTotalPriceStart;
        }

        $this->defConfParams['shipper_no'] = Mage::getStoreConfig('upslabel/shipping/defaultshipper');
        $this->defConfParams['shipfrom_no'] = Mage::getStoreConfig('upslabel/shipping/defaultshipfrom');
        $this->defConfParams['testing'] = Mage::getStoreConfig('upslabel/testmode/testing');
        $this->defConfParams['addtrack'] = Mage::getStoreConfig('upslabel/shipping/addtrack');
        $this->defConfParams['shipmentdescription'] = Mage::getStoreConfig('upslabel/shipping/shipmentdescription') == 1 ? ($this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname() . ' ' . Mage::helper('adminhtml')->__('Order Id') . ': ' . $this->imOrder->getIncrementId()) : (Mage::getStoreConfig('upslabel/shipping/shipmentdescription') == 2 ? $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname() : (Mage::getStoreConfig('upslabel/shipping/shipmentdescription') !== '' ? Mage::helper('adminhtml')->__('Order Id') . ': ' . $this->imOrder->getIncrementId() : ''));
        $this->defConfParams['currencycode'] = Mage::getStoreConfig('upslabel/ratepayment/currencycode');
        $this->defConfParams['shipmentcharge'] = Mage::getStoreConfig('upslabel/ratepayment/shipmentcharge');
        $this->defConfParams['cod'] = Mage::getStoreConfig('upslabel/ratepayment/cod')==1?1:(($this->paymentmethod == 'cashondelivery' || $this->paymentmethod == 'phoenix_cashondelivery')?1:0);
        $this->defConfParams['codmonetaryvalue'] = Mage::getStoreConfig('upslabel/ratepayment/cod_shipping_cost') == 0 ? $this->shipmentTotalPrice - $this->shippingAmount['shipping_amount'] : $this->shipmentTotalPrice;
        $this->defConfParams['codfundscode'] = 1;
        $this->defConfParams['invoicelinetotalyesno'] = Mage::getStoreConfig('upslabel/ratepayment/invoicelinetotal');
        $this->defConfParams['invoicelinetotal'] = $this->shipmentTotalPrice;
        $this->defConfParams['carbon_neutral'] = Mage::getStoreConfig('upslabel/ratepayment/carbon_neutral');
        $this->defConfParams['default_return'] = (Mage::getStoreConfig('upslabel/return/default_return') == 0 || Mage::getStoreConfig('upslabel/return/default_return_amount') > $this->shipmentTotalPrice) ? 0 : 1;
        $this->defConfParams['default_return_servicecode'] = Mage::getStoreConfig('upslabel/return/default_return_method');
        $this->defConfParams['qvn'] = Mage::getStoreConfig('upslabel/quantum/qvn');
        $this->defConfParams['qvn_code'] = explode(",", Mage::getStoreConfig('upslabel/quantum/qvn_code'));
        $this->defConfParams['qvn_email_shipper'] = Mage::getStoreConfig('upslabel/quantum/qvn_email_shipper');
        $this->defConfParams['adult'] = Mage::getStoreConfig('upslabel/quantum/adult');
        $this->defConfParams['weightunits'] = Mage::getStoreConfig('upslabel/weightdimension/weightunits');
        $this->defConfParams['largepackageindicator'] = $this->totalWight >= 90 ? '<LargePackageIndicator />' : '';
        $this->defConfParams['includedimensions'] = Mage::getStoreConfig('upslabel/weightdimension/includedimensions');
        $this->defConfParams['unitofmeasurement'] = Mage::getStoreConfig('upslabel/weightdimension/unitofmeasurement');
        $this->defConfParams['residentialaddress'] = strlen($this->shippingAddress->getCompany()) > 0 ? '' : '<ResidentialAddress />';
        $this->defConfParams['shiptocompanyname'] = strlen($this->shippingAddress->getCompany()) > 0 ? $this->shippingAddress->getCompany() : $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname();
        $this->defConfParams['shiptoattentionname'] = $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname();
        $this->defConfParams['shiptophonenumber'] = $this->shippingAddress->getTelephone();
        $addressLine1 = $this->shippingAddress->getStreet();
        $this->defConfParams['shiptoaddressline1'] = is_array($addressLine1) && array_key_exists(0, $addressLine1) ? $addressLine1[0] : $addressLine1;
        $this->defConfParams['shiptoaddressline2'] = (is_array($addressLine1) && isset($addressLine1[1])) ? $addressLine1[1] : '';
        $this->defConfParams['shiptocity'] = $this->shippingAddress->getCity();
        $this->defConfParams['shiptostateprovincecode'] = $this->shippingAddress->getRegion();
        $this->defConfParams['shiptopostalcode'] = $this->shippingAddress->getPostcode();
        $this->defConfParams['shiptocountrycode'] = $this->shippingAddress->getCountryId();
        $this->defConfParams['qvn_email_shipto'] = $this->shippingAddress->getEmail();
        $this->defConfParams['saturday_delivery'] = Mage::getStoreConfig('upslabel/shipping/saturday_delivery') == 0 ? "" : '<SaturdayDelivery />';

        $this->defConfParams['accesspoint'] = 0;
        if (Mage::getStoreConfig('carriers/upsap/active') == 1 && Mage::helper('core')->isModuleOutputEnabled("Infomodus_Upsap")) {
            if (count($modelAccessPoint) > 0) {
                $modelAccessPoint = json_decode($modelAccessPoint['address'], true);
                $this->defConfParams['accesspoint'] = 1;
                $this->defConfParams['accesspoint_type'] = Mage::getStoreConfig('carriers/upsap/type');
                $this->defConfParams['accesspoint_name'] = $modelAccessPoint['name'];
                $this->defConfParams['accesspoint_atname'] = $modelAccessPoint['name'];
                $this->defConfParams['accesspoint_appuid'] = $modelAccessPoint['appuId'];
                $this->defConfParams['accesspoint_street'] = $modelAccessPoint['addLine1'];
                if (isset($modelAccessPoint['addLine2'])) {
                    $this->defConfParams['accesspoint_street1'] = $modelAccessPoint['addLine2'];
                    if (isset($modelAccessPoint['addLine3'])) {
                        $this->defConfParams['accesspoint_street2'] = $modelAccessPoint['addLine3'];
                    }
                }
                $this->defConfParams['accesspoint_city'] = $modelAccessPoint['city'];
                $this->defConfParams['accesspoint_provincecode'] = $modelAccessPoint['state'];
                $this->defConfParams['accesspoint_postal'] = $modelAccessPoint['postal'];
                $this->defConfParams['accesspoint_country'] = $modelAccessPoint['country'];
            }
        }

    }

    public
    function setDefParams($itemData = NULL , $type = "shipment")
    {
        $defParArr_1['packagingtypecode'] = Mage::getStoreConfig('upslabel/packaging/packagingtypecode');
        $defParArr_1['packagingdescription'] = Mage::getStoreConfig('upslabel/packaging/packagingdescription');
        $defParArr_1['packagingreferencenumbercode'] = Mage::getStoreConfig('upslabel/packaging/packagingreferencenumbercode');
        $defParArr_1['packagingreferencebarcode'] = Mage::getStoreConfig('upslabel/packaging/packagingreferencebarcode');
        $defParArr_1['packagingreferencenumbervalue'] = str_replace(array("#order_id#", "#customer_name#"), array($this->imOrder->getIncrementId(), $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname()), Mage::getStoreConfig('upslabel/packaging/packagingreferencenumbervalue'));
        $defParArr_1['packagingreferencenumbercode2'] = Mage::getStoreConfig('upslabel/packaging/packagingreferencenumbercode2');
        $defParArr_1['packagingreferencebarcode2'] = Mage::getStoreConfig('upslabel/packaging/packagingreferencebarcode2');
        $defParArr_1['packagingreferencenumbervalue2'] = str_replace(array("#order_id#", "#customer_name#"), array($this->imOrder->getIncrementId(), $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname()), Mage::getStoreConfig('upslabel/packaging/packagingreferencenumbervalue2'));
        $defParArr_1['weight'] = $itemData !== NULL ? $itemData['weight'] : $this->totalWight;
        $defParArr_1['packweight'] = round(Mage::getStoreConfig('upslabel/weightdimension/packweight'), 1) > 0 ? round(Mage::getStoreConfig('upslabel/weightdimension/packweight'), 1) : '0';
        $defParArr_1['large'] = ($itemData !== NULL ? $itemData['weight'] : $this->totalWight) >= 90 ? '<LargePackageIndicator />' : '';
        $defParArr_1['additionalhandling'] = Mage::getStoreConfig('upslabel/ratepayment/additionalhandling') == 1 ? '<AdditionalHandling />' : '';
        $defParArr_1['dimansion_id'] = Mage::getStoreConfig('upslabel/weightdimension/defaultdimensionsset');
        $defParArr_1['currencycode'] = Mage::getStoreConfig('upslabel/ratepayment/currencycode');
        $defParArr_1['cod'] = Mage::getStoreConfig('upslabel/ratepayment/cod')==1?1:(($this->paymentmethod == 'cashondelivery' || $this->paymentmethod == 'phoenix_cashondelivery')?1:0);
        $defParArr_1['codfundscode'] = 0;
        $defParArr_1['codmonetaryvalue'] = $itemData !== NULL ? $itemData['price'] : (Mage::getStoreConfig('upslabel/ratepayment/cod_shipping_cost') == 0 ? $this->shipmentTotalPrice - $this->shippingAmount['shipping_amount'] : $this->shipmentTotalPrice);
        $defParArr_1['insuredmonetaryvalue'] = Mage::getStoreConfig('upslabel/ratepayment/insured_automaticaly') == 1 ? ($itemData !== NULL ? $itemData['price'] : $this->shipmentTotalPrice) : 0;
        return ($defParArr_1);
    }

    public
    function deletelabelAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $shipment_id = $this->getRequest()->getParam('shipment_id');
        $type = $this->getRequest()->getParam('type');
        $upslabel = Mage::getModel('upslabel/labelprice')->getCollection()->addFieldToFilter('order_id', $order_id)->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', $type);
        if (count($upslabel) > 0) {
            foreach ($upslabel AS $c) {
                $c->delete();
            }
        }
        $this->loadLayout();
        $this->_addLeft($this->getLayout()->createBlock('upslabel/adminhtml_upslabel_label_del'));
        $this->renderLayout();
    }

    public
    function autoprintAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $shipment_id = $this->getRequest()->getParam('shipment_id');
        $label_id = $this->getRequest()->getParam('label_id');
        $type = $this->getRequest()->getParam('type');
        $path = Mage::getBaseDir('media') . DS . 'upslabel' . DS . 'label' . DS;

        

        $upslabel = Mage::getModel('upslabel/upslabel');
        if (!isset($label_id) || empty($label_id) || $label_id <= 0) {
            $colls2 = $upslabel->getCollection()->addFieldToFilter('order_id', $order_id)->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 0);
            foreach ($colls2 AS $coll) {
                if (file_exists($path . ($coll->getLabelname()))) {
                    if ($data = file_get_contents($path . ($coll->getLabelname()))) {
                        Mage::helper('upslabel/help')->sendPrint($data, $storeId, $coll->getTypePrint());
                    }
                }
            }
        } else {
            $label = $upslabel->load($label_id);
            if (file_exists($path . ($label->getLabelname()))) {
                if ($data = file_get_contents($path . ($label->getLabelname()))) {
                    Mage::helper('upslabel/help')->sendPrint($data);
                }
            }
        }

    }

    public
    function downloadnotgifAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $shipment_id = $this->getRequest()->getParam('shipment_id');
        $label_id = $this->getRequest()->getParam('label_id');
        $type = $this->getRequest()->getParam('type');
        $path = Mage::getBaseDir('media') . DS . 'upslabel' . DS . 'label' . DS;

        

        $upslabel = Mage::getModel('upslabel/upslabel');
        if (!isset($label_id) || empty($label_id) || $label_id <= 0) {
            $colls2 = $upslabel->getCollection()->addFieldToFilter('order_id', $order_id)->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 0);
            if (extension_loaded('zip')) {
                $zip = new ZipArchive();
                $zip_name = sys_get_temp_dir() . DS . 'order' . $order_id . 'shipment' . $shipment_id . '.zip';
                if ($zip->open($zip_name, ZIPARCHIVE::CREATE) !== TRUE) {
                }
                foreach ($colls2 AS $coll) {
                    if (file_exists($path . ($coll->getLabelname())) && $coll->getTypePrint() != 'GIF') {
                        $zip->addFile($path . $coll->getLabelname(), $coll->getLabelname());
                    }
                }
                $zip->close();
                if (file_exists($zip_name)) {
                    header('Content-type: application/zip');
                    header('Content-Disposition: attachment; filename="labels_order' . $order_id . '_shipment' . $shipment_id . '.zip"');
                    readfile($zip_name);
                    unlink($zip_name);
                }
            } else {
                $phar = new Phar(sys_get_temp_dir() . DS . 'order' . $order_id . 'shipment' . $shipment_id . '.phar');
                $phar = $phar->convertToExecutable(Phar::ZIP);
                $applicationType = 'zip';

                foreach ($colls2 AS $coll) {
                    if (file_exists($path . ($coll->getLabelname())) && $coll->getTypePrint() != 'GIF') {
                        if ($data = file_get_contents($path . ($coll->getLabelname()))) {
                            $phar[$coll->getLabelname()] = $data;
                        }
                    }
                }
                if (Phar::canCompress(Phar::GZ)) {
                    $phar->compress(Phar::GZ, '.gz');
                    $applicationType = 'x-gzip';
                }
                if (file_exists($phar::running(false))) {
                    $pdfData = file_get_contents($phar::running(false));
                    @unlink($phar::running(false));
                    header("Content-Disposition: inline; filename=labels_order' . $order_id . '_shipment' . $shipment_id . '.zip");
                    header("Content-type: application/" . $applicationType);
                    echo $pdfData;
                }
            }

        }
        return true;
    }

    public
    function printAction()
    {
        $imname = $this->getRequest()->getParam('imname');
        $path_url = Mage::getBaseUrl('media') . DS . 'upslabel' . DS . 'label' . DS;
        echo '<html>
            <head>
            <title>Print Shipping Label</title>
            </head>
            <body>
            <img src="' . $path_url . $imname . '" />
            <script>
            window.onload = function(){window.print();}
            </script>
            </body>
            </html>';
        exit;
    }
}