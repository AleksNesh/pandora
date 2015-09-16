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
require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';
class Webshopapps_Endicia_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{

    protected $_ourCarrier;

    public function createEndiciaLabelAction()
    {
       $response = new Varien_Object();
        try {
            $shipment = $this->_initShipment();
            if ($this->_createShippingLabel($shipment)) {
                $shipment->save();
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The shipping label has been created.'));
                $response->setOk(true);
            }
        } catch (Mage_Core_Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $response->setError(true);
            $response->setMessage(Mage::helper('sales')->__('An error occurred while creating shipping label.'));
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return null
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        $isNeedCreateLabel = false;
        $responseAjax = new Varien_Object();
        $redirect = 'adminhtml/sales_order/view';
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }
        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $upsInfomodusSet = isset($data['upslabel_create']);
            $dhlInfomodusSet = isset($data['dhllabel_create']);

            if(Mage::helper('wsacommon')->isModuleEnabled('Infomodus_Upslabel') && ($dhlInfomodusSet || $upsInfomodusSet)) {
                if ($upsInfomodusSet) {
                    $redirect = 'upslabel/adminhtml_upslabel/intermediate';
                }
                if ($dhlInfomodusSet) {
                    $redirect = 'dhllabel/adminhtml_dhllabel/intermediate';
                }
            } else {
                $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

                if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                    $responseAjax->setOk(true);
                }
            }

            $this->_saveShipment($shipment);

            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = $this->__('The shipment has been created.');
            $labelCreatedMessage    = $this->__('The shipping label has been created.');

            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect($redirect, array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect($redirect, array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect($redirect, array('order_id' => $shipment->getOrderId(), 'shipment_id' => $shipment->getId(), 'type' => 'shipment'));
        }
    }

    /**
     * Create shipping label for specific shipment with validation.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    protected function _createShippingLabel(Mage_Sales_Model_Order_Shipment $shipment)
    {
        if (!$shipment) {
            return false;
        }
        $endiciaCarrier = Mage::getModel('wsaendicia/carrier_endicia');
        $useEndicia = false;
        $packages = $this->getRequest()->getParam('packages');
        foreach($packages as $key => $package) {
            if(array_key_exists('use_endicia', $package['params']) && $package['params']['use_endicia']) {
                $useEndicia = true;
            }
            $containerArray = explode('#',$package['params']['container']);
            if(count($containerArray) > 1) {
                $newpackage = $package;
                $newpackage['params']['container'] = $containerArray[1];
                $packages[$key] = $newpackage;
            }
        }

        //EN-8
        $this->getRequest()->setParam('packages', $packages);

        if(!$useEndicia) {
            return parent::_createShippingLabel($shipment);
        }
        if($useEndicia) {
            $carrier = $endiciaCarrier;
        }
        else {
            $carrier = $shipment->getOrder()->getShippingCarrier();
        }

        if (!$carrier->isShippingLabelsAvailable()) {
            return false;
        }

        $shipment->setPackages($this->getRequest()->getParam('packages'));
        $response = Mage::getModel('wsaendicia/shipping_shipping')->requestToShipment($shipment);

        if ($response->hasErrors()) {
            Mage::throwException($response->getErrors());
        }
        if (!$response->hasInfo()) {
            return false;
        }
        $labelsContent = array();
        $trackingNumbers = array();
        $info = $response->getInfo();
        foreach ($info as $inf) {
            if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {
                $labelsContent[] = $inf['label_content'];
                $trackingNumbers[] = $inf['tracking_number'];
            }
        }
        $outputPdf = $this->_combineLabelsPdf($labelsContent);
        $shipment->setShippingLabel($outputPdf->render());
        $carrierCode = $carrier->getCarrierCode();
        $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $shipment->getStoreId());
        if ($trackingNumbers) {
            foreach ($trackingNumbers as $trackingNumber) {
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($trackingNumber)
                    ->setCarrierCode($carrierCode)
                    ->setTitle($carrierTitle);
                $shipment->addTrack($track);
            }
        }
        return true;

    }

    public function retrieveContainerTypesAction()
    {
        //Placeholder - ajax function in template is commented out
        $resultSet = array();
        if ($this->getRequest()->isGet()) {
            $shippingMethod = $this->getRequest()->getParam('shipping_method');
            $shippingCountry = $this->getRequest()->getParam('country_recipient');
            $storeId = $this->getRequest()->getParam('store_id');


            if(!$this->_ourCarrier) {
                $this->_ourCarrier = Mage::getSingleton('wsaendicia/carrier_endicia');
            }

            $params = new Varien_Object(array(
                'method' => $shippingMethod,
                'country_shipper' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $storeId),
                'country_recipient' =>$shippingCountry,
            ));
            $resultSet['container_types'] = $this->_ourCarrier->getContainerTypes($params);
        }
        else {
            $resultSet['container_types'] = false;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($resultSet));
    }

}