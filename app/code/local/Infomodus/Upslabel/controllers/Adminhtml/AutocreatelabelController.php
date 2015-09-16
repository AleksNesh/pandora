<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 29.05.14
 * Time: 11:37
 */
require_once 'UpslabelController.php';
require_once 'PdflabelsController.php';

class Infomodus_Upslabel_Adminhtml_AutocreatelabelController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        try {
            $ptype = $this->getRequest()->getParam('type');
            $type = 'shipment';
            $order_ids = $this->getRequest()->getParam($ptype . '_ids');
            $countCreateLabel = 0;
            foreach ($order_ids AS $order_id) {
                $order = Mage::getModel('sales/order')->load($order_id);
                
                $shippingActiveMethods = trim(Mage::getStoreConfig('upslabel/frontend_autocreate_label/apply_to'), " ,");
                $shippingActiveMethods = strlen($shippingActiveMethods) > 0 ? explode(",", $shippingActiveMethods) : array();
                $orderStatuses = explode(",", trim(Mage::getStoreConfig('upslabel/frontend_autocreate_label/orderstatus'), " ,"));
                if (((isset($shippingActiveMethods) && count($shippingActiveMethods) > 0 && in_array($order->getShippingMethod(), $shippingActiveMethods)) || strpos($order->getShippingMethod(), "ups_") === 0)
                    && (isset($orderStatuses) && count($orderStatuses) > 0 && in_array($order->getStatus(), $orderStatuses))
                ) {
                    $collections = Mage::getModel('upslabel/upslabel');
                    $colls = $collections->getCollection()->addFieldToFilter('order_id', $order_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 0);
                    if (count($colls) == 0) {
                        $controller = new Infomodus_Upslabel_Adminhtml_UpslabelController();
                        $controller->intermediatehandy($order_id, $type);
                        $AccessLicenseNumber = Mage::getStoreConfig('upslabel/credentials/accesslicensenumber');
                        $UserId = Mage::getStoreConfig('upslabel/credentials/userid');
                        $Password = Mage::getStoreConfig('upslabel/credentials/password');
                        $shipperNumber = Mage::getStoreConfig('upslabel/credentials/shippernumber');

                        $lbl = Mage::getModel('upslabel/ups');
                        $lbl->setCredentials($AccessLicenseNumber, $UserId, $Password, $shipperNumber);
                        $lbl = $controller->setParams($lbl, $controller->defConfParams, $controller->defParams );
                        $upsl = $lbl->getShip();
                        if ($controller->defConfParams['default_return'] == 1) {
                            $lbl->serviceCode = array_key_exists('default_return_servicecode', $controller->defConfParams) ? $controller->defConfParams['default_return_servicecode'] : '';
                            $upsl2 = $lbl->getShipFrom();
                        }
                        Mage::register('isCreateLabelNow' . $order_id, 2);
                        if (!isset($upsl2)) {
                            $upsl2 = NULL;
                        }
                        $upslabel = $controller->saveDB($upsl, $upsl2, $controller->defConfParams, $order_id, 0, $type);
                        if ($upslabel && Mage::getStoreConfig('upslabel/frontend_autocreate_label/track_send') == 1 && $upslabel->getShipmentId() > 0) {
                            $shipment = Mage::getModel('sales/order_shipment')->load($upslabel->getShipmentId());
                            if ($shipment) {
                                $shipment->sendEmail(true, '')
                                    ->setEmailSent(true)
                                    ->save();
                            }
                        }
                        $countCreateLabel++;
                    }
                }

            }
            $path_upsdir = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "update" . DS;
            if (!file_exists($path_upsdir . 'last_update.txt') || (int)file_get_contents($path_upsdir . 'last_update.txt') < time() - 86400) {
                Mage::getModel('upslabel/cron')->update();
            }

            /*$resp = false;
            if($countCreateLabel==0){
                $this->_getSession()->addError($this->__('Not created any labels'));
            }
            else {
                $resp = Infomodus_Upslabel_Adminhtml_PdflabelsController::create($order_ids, $type, $ptype);
            }
            if (!$resp) {*/
            $this->_redirectReferer();
            /*}*/
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return true;
    }
} 