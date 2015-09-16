<?php

/**
 * Extend/Override Infomodus_Upslabel module
 *
 * @category    Pan_Infomodus
 * @package     Pan_Infomodus_Upslabel
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


// explicitly require parent controller b/c controllers are not autoloaded
require_once(Mage::getModuleDir('controllers','Infomodus_Upslabel') .DS. 'Adminhtml' .DS. 'UpslabelController.php');



class Pan_Infomodusupslabel_Helper_Data extends Infomodus_Upslabel_Helper_Data
{

    const UPS_SHIPPING_METHODS_PATTERN  = "/^Ship Option \- (UPS\s+.*)$/i";
    const USPS_SHIPPING_METHODS_PATTERN = "/^Ship Option \- ((?:US Postal|USPS)\s+.*)$/i";

    /**
     * checkIfUpsShippingMethod
     *
     * @param  Mage_Sales_Model_Order   $order
     * @param  string                   $pattern    # regex pattern to apply against the order's shipping description
     * @return string
     */
    public function checkIfUpsShippingMethod(Mage_Sales_Model_Order $order, $pattern = self::UPS_SHIPPING_METHODS_PATTERN)
    {
        $matches = $this->_matchesCarrier($order->getData('shipping_description'), $pattern);

        if (!empty($matches)) {
            $shippingMethod = $matches[1];
        } else {
            $shippingMethod = '';
        }

        return $shippingMethod;
    }

    /**
     * checkIfPostalShippingMethod
     *
     * @param  Mage_Sales_Model_Order   $order
     * @param  string                   $pattern    # regex pattern to apply against the order's shipping description
     * @return string
     */
    public function checkIfPostalShippingMethod(Mage_Sales_Model_Order $order, $pattern = self::USPS_SHIPPING_METHODS_PATTERN)
    {
        $matches = $this->_matchesCarrier($order->getData('shipping_description'), $pattern);
        if (!empty($matches)) {
            $shippingMethod = $matches[1];
        } else {
            $shippingMethod = '';
        }

        return $shippingMethod;
    }

    /**
     * _matchesCarrier
     *
     * @param  string   $shippingDesc   # order's shipping description
     * @param  string   $pattern        # regex pattern to apply against the order's shipping description
     * @return array
     */
    protected function _matchesCarrier($shippingDesc, $pattern)
    {
        preg_match($pattern, $shippingDesc, $matches);
        return $matches;
    }


    /**
     * generateTrackingNumberAndLabelForOrder
     *
     * Gutted the Infomodus_Upslabel_Adminhtml_AutocreatelabelController::indexAction
     * functionality and put it in here so it can be called from other places
     * (i.e. Xtento_GridActions module controllers for example)
     *
     * @param  integer|string   $orderId
     * @return void
     */
    public function generateTrackingNumberAndLabelForOrder($orderId, $type = 'shipment', $ptype = 'order')
    {
        try {


            Mage::log('HIT FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE: ' . __LINE__);

            $AccessLicenseNumber    = Mage::getStoreConfig('upslabel/credentials/accesslicensenumber');
            $UserId                 = Mage::getStoreConfig('upslabel/credentials/userid');
            $Password               = Mage::getStoreConfig('upslabel/credentials/password');
            $shipperNumber          = Mage::getStoreConfig('upslabel/credentials/shippernumber');


            $order = Mage::getModel('sales/order')->load($orderId);

            if ($order->canShip()) {
                $itemQty    = $order->getItemsCollection()->count();
                $shipment   = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
                $shipment   = new Mage_Sales_Model_Order_Shipment_Api();
                $shipmentId = $shipment->create($order->getIncrementId(), array(), '', true, true);
                $shipmentId = Mage::getModel('sales/order_shipment')->load($shipmentId, 'increment_id')->getId();
            } else {
                $shipment   = $order->getShipmentsCollection()->getFirstItem();
                $shipmentId = $shipment->getId();
            }
            if ($shipmentId && $shipmentId > 0) {

                $collection = Mage::getModel('upslabel/upslabel')
                    ->getCollection()
                    ->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('shipment_id', $shipmentId)
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('status', 0);

                if ($collection->count() === 0) {
                    $controller = new Infomodus_Upslabel_Adminhtml_UpslabelController();
                    $controller->intermediatehandy($orderId, $type, $shipmentId);


                    $lbl = Mage::getModel('upslabel/ups');

                    $lbl->setCredentials($AccessLicenseNumber, $UserId, $Password, $shipperNumber);
                    $lbl = $controller->setParams($lbl, $controller->defConfRarams, array($controller->defParams));

                    $upsl = $lbl->getShip();
                    if ($controller->defConfRarams['default_return'] == 1) {
                        $lbl->serviceCode = (array_key_exists('default_return_servicecode', $controller->defConfRarams))
                            ? $controller->defConfRarams['default_return_servicecode']
                            : '';

                        $upsl2 = $lbl->getShipFrom();
                    }


                    $upslabel   = Mage::getModel('upslabel/upslabel');
                    $colls2     = $upslabel->getCollection()
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('shipment_id', $shipmentId)
                        ->addFieldToFilter('type', $type)
                        ->addFieldToFilter('status', 1);

                    if ($colls2->count() > 0) {
                        foreach ($colls2 AS $c) {
                            $c->delete();
                        }
                    }

                    if (!array_key_exists('error', $upsl) || !$upsl['error']) {
                        foreach ($upsl['arrResponsXML'] AS $upsl_one) {
                            $upslabel = Mage::getModel('upslabel/upslabel');
                            $upslabel->setTitle('Order ' . $orderId . ' TN' . $upsl_one['trackingnumber']);
                            $upslabel->setOrderId($orderId);
                            $upslabel->setShipmentId($shipmentId);
                            $upslabel->setType($type);
                            /*$upslabel->setBase64Image();*/
                            $upslabel->setTrackingnumber($upsl_one['trackingnumber']);
                            $upslabel->setShipmentidentificationnumber($upsl['shipidnumber']);
                            $upslabel->setShipmentdigest($upsl['digest']);
                            $upslabel->setLabelname('label' . $upsl_one['trackingnumber'] . '.gif');
                            $upslabel->setStatustext(Mage::helper('adminhtml')->__('Successfully'));
                            $upslabel->setStatus(0);
                            $upslabel->setCreatedTime(Date("Y-m-d H:i:s"));
                            $upslabel->setUpdateTime(Date("Y-m-d H:i:s"));
                            $upslabel->save();

                            $upslabel = Mage::getModel('upslabel/labelprice');
                            $upslabel->setOrderId($orderId);
                            $upslabel->setShipmentId($shipmentId);
                            $upslabel->setPrice($upsl['price']['price'] . " " . $upsl['price']['currency']);
                            $upslabel->save();
                        }
                        if ($controller->defConfRarams['default_return'] == 1) {
                            if (!array_key_exists('error', $upsl2) || !$upsl2['error']) {
                                foreach ($upsl2['arrResponsXML'] AS $upsl_one) {
                                    $upslabel = Mage::getModel('upslabel/upslabel');
                                    $upslabel->setTitle('Order ' . $orderId . ' TN' . $upsl_one['trackingnumber']);
                                    $upslabel->setOrderId($orderId);
                                    $upslabel->setShipmentId($shipmentId);
                                    $upslabel->setType($type);
                                    /*$upslabel->setBase64Image();*/
                                    $upslabel->setTrackingnumber($upsl_one['trackingnumber']);
                                    $upslabel->setShipmentidentificationnumber($upsl['shipidnumber']);
                                    $upslabel->setShipmentdigest($upsl['digest']);
                                    $upslabel->setLabelname('label' . $upsl_one['trackingnumber'] . '.gif');
                                    $upslabel->setStatustext(Mage::helper('adminhtml')->__('Successfully'));
                                    $upslabel->setStatus(0);
                                    $upslabel->setCreatedTime(Date("Y-m-d H:i:s"));
                                    $upslabel->setUpdateTime(Date("Y-m-d H:i:s"));
                                    $upslabel->save();

                                    $upslabel = Mage::getModel('upslabel/labelprice');
                                    $upslabel->setOrderId($orderId);
                                    $upslabel->setShipmentId($shipmentId);
                                    $upslabel->setPrice($upsl2['price']['price'] . " " . $upsl2['price']['currency']);
                                    $upslabel->save();
                                }
                            } else {
                                $upslabel = Mage::getModel('upslabel/upslabel');
                                $upslabel->setTitle('Order ' . $orderId);
                                $upslabel->setOrderId($orderId);
                                $upslabel->setShipmentId($shipmentId);
                                $upslabel->setType($type);
                                $upslabel->setStatustext($upsl2['errordesc']);
                                $upslabel->setStatus(1);
                                $upslabel->setCreatedTime(Date("Y-m-d H:i:s"));
                                $upslabel->setUpdateTime(Date("Y-m-d H:i:s"));
                                $upslabel->save();
                            }
                        }
                        if ($controller->defConfRarams['addtrack'] == 1 && $type == 'shipment') {
                            $trTitle = 'United Parcel Service';
                            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
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
                        $upslabel->setTitle('Order ' . $orderId);
                        $upslabel->setOrderId($orderId);
                        $upslabel->setShipmentId($shipmentId);
                        $upslabel->setType($type);
                        $upslabel->setStatustext($upsl['errordesc']);
                        $upslabel->setStatus(1);
                        $upslabel->setCreatedTime(Date("Y-m-d H:i:s"));
                        $upslabel->setUpdateTime(Date("Y-m-d H:i:s"));
                        $upslabel->save();
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log('[ EXCEPTION!!! ] FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log($e->getMessage());
        }
    }


    /**
     * generatePdfForOrders
     *
     * Create a PDF file from one or multiple orders
     *
     * Returns an array of:
     *
     *  $success - (bool) whether or not PDF could be created
     *  $pdf     - (Zend_PDF|null) PDF object or null if unsuccessful
     *  $message - (string) message to be displayed to the user to help debug
     *
     * @param  array|string     $order_ids
     * @param  string           $type
     * @param  string           $ptype
     * @return array            # return array($success, $pdf, $message)
     */
    public function generatePdfForOrders($order_ids, $type = 'shipment', $ptype = 'shipment')
    {
        $pdf = new Zend_Pdf();

        // keep track of order_ids that did not have a UPS Label generated
        $missingLabels = array();

        if (!is_array($order_ids)) {
            $order_ids = explode(',', $order_ids);
        }


        foreach ($order_ids as $order_id) {
            $collection = $this->_getLabelCollectionForOrder($order_id, $type, $ptype, 0);

            if ($collection->count() <= 0) {
                $order = Mage::getModel('sales/order')->load($order_id);
                // add the order number to the $missingLabels array
                $missingLabels[] = $order->getIncrementId();
            } else {
                try {
                    $pdf = $this->generateUpsLabelPdfForOrder($order_id, $pdf);
                } catch (Exception $e) {
                    Mage::log('[ EXCEPTION!!! ] FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
                    Mage::log($e->getMessage());
                }
            }
        }

        $missing    = count($missingLabels);
        $message    = '';

        if ($missing > 0) {
            $message    = Mage::helper('adminhtml')->__("No UPS Labels exist for an order! {$missing} orders were missing labels.\nOrders missing UPS Labels: " . implode(', ', $missingLabels) . '. Skipping printing of UPS Labels PDF. Please choose Orders that either have existing UPS Labels or choose "Create UPS Labels" from the bulk actions menu to proceed.');
            $url        = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order");

            $success    = false;
            $pdf        = null;
        } else {
            $success = true;
        }
        return array($success, $pdf, $message);
    }

    /**
     * generateUpsLabelPdf
     *
     * @param  string|integer   $orderId
     * @param  null|Zend_PDF    $pdf
     * @return Zend_PDF
     */
    public function generateUpsLabelPdfForOrder($orderId, $pdf = null, $shipmentId = null, $ptype = 'order')
    {
        if (is_null($pdf)) {
            $pdf = new Zend_Pdf();
        }

        // fetch configuration values
        $dimensionX     = Mage::getStoreConfig('upslabel/printing/dimensionx');
        $dimensionY     = Mage::getStoreConfig('upslabel/printing/dimensiony');
        $holstX         = Mage::getStoreConfig('upslabel/printing/holstx');
        $holstY         = Mage::getStoreConfig('upslabel/printing/holsty');

        // pdf image width and height values
        $width          = strlen($dimensionX) > 0 ? $dimensionX : (1400 / 2.6);
        $height         = strlen($dimensionY) > 0 ? $dimensionY : (800 / 2.6);

        if(strlen($holstX) > 0 && strlen($holstY) > 0) {
            $holstSize = $holstX . ':' . $holstY . ':';
        } else {
            $holstSize = Zend_Pdf_Page::SIZE_A4;
        }

        $img_path       = Mage::getBaseDir('media') . '/upslabel/label/';

        $collection     = $this->_getLabelCollectionForOrder($orderId);

        foreach ($collection as $k => $v) {
            $labelId    = $v['upslabel_id'];
            $label      = Mage::getModel('upslabel/upslabel')->load($labelId);

            if (($label->getOrderId() === $orderId && $ptype === "order") ||
                ($label->getShipmentId() === $orderId && $ptype !== "order")) {
                if (file_exists($img_path . $label->getLabelname()) &&
                    filesize($img_path . $label->getLabelname()) > 1024) {

                    $page           = $pdf->newPage($holstSize);
                    $pdf->pages[]   = $page;

                    $f_cont         = file_get_contents($img_path . $label->getLabelname());
                    $img            = imagecreatefromstring($f_cont);
                    if (Mage::getStoreConfig('upslabel/printing/verticalprint') == 1) {
                        $FullImage_width    = imagesx($img);
                        $FullImage_height   = imagesy($img);
                        $full_id            = imagecreatetruecolor($FullImage_width, $FullImage_height);
                        $col                = imagecolorallocate($img, 125, 174, 240);
                        $IMGfuul            = imagerotate($img, -90, $col);
                    } else {
                        $IMGfuul = $img;
                    }
                    $rnd = rand(10000, 999999);
                    imagejpeg($IMGfuul, $img_path . 'lbl' . $rnd . '.jpeg', 100);
                    $image = Zend_Pdf_Image::imageWithPath($img_path . 'lbl' . $rnd . '.jpeg');
                    $page->drawImage($image, 0, 0, $width, $height);

                    // update the label's base64_image column and the shipment's shippingLabel attribute
                    $label->setData('base64_image', base64_encode($f_cont));
                    $label->save();

                    // update the shipment's shipping label attribute as well
                    $shipment = Mage::getModel('sales/order_shipment')->load($label->getShipmentId());
                    $shipment->setData('shipping_label', $pdf->render());
                    $shipment->save();
                }
            }
            unset($IMGfuul);
        }

        if (!empty($shipmentId)) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if ($shipment) {
                // update the shipment's shipping label attribute
                $shipment->setData('shipping_label', $pdf->render());
                $shipment->save();
            }
        }

        return $pdf;
    }


    /**
     * _getLabelCollectionForOrder
     *
     * Return a collection for upslabel records filtered by the order id
     *
     * @param  string|integer       $orderId
     * @param  string               $type
     * @param  string               $ptype
     * @param  integer|bool         $status
     * @return Infomodus_Upslabel_Model_Mysql4_Upslabel_Collection
     */
    protected function _getLabelCollectionForOrder($orderId, $type = null, $ptype = null, $status = 0)
    {
        $collection = Mage::getModel('upslabel/upslabel')
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', $status);

        if(!is_null($ptype)) {
            $collection->addFieldToFilter($ptype . '_id', $orderId);
        }

        if(!is_null($type)) {
            $collection->addFieldToFilter('type', $type);
        }

        return $collection;
    }

}
