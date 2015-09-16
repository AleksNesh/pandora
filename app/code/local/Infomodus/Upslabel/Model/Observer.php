<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 28.12.11
 * Time: 9:38
 * To change this template use File | Settings | File Templates.
 */
require_once Mage::getBaseDir('app') . '/code/local/Infomodus/Upslabel/controllers/Adminhtml/UpslabelController.php';

class Infomodus_Upslabel_Model_Observer
{
    public function initUpslabel($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction) {
            $type = '';
            if ($block->getRequest()->getControllerName() == 'sales_order') {
                $type = 'order';
            } else if ($block->getRequest()->getControllerName() == 'sales_shipment') {
                $type = 'shipment';
            } else if ($block->getRequest()->getControllerName() == 'sales_creditmemo') {
                $type = 'creditmemo';
            }
            if (strlen($type) > 0) {
                $block->addItem('upslabel_pdflabels', array(
                    'label' => Mage::helper('sales')->__('Print UPS Shipping Labels'),
                    'url' => Mage::app()->getStore()->getUrl('upslabel/adminhtml_pdflabels', array('type' => $type)),
                ));
                if ($type == 'order') {
                    $block->addItem('upslabel_autocreatelabel', array(
                        'label' => Mage::helper('sales')->__('Create UPS Labels for Orders'),
                        'url' => Mage::app()->getStore()->getUrl('upslabel/adminhtml_autocreatelabel', array('type' => $type)),
                    ));
                }
            }
        }
    }

    public function addbutton($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View) {
            $block->removeButton('order_label');
            $shipment_id = $block->getShipment()->getId();
            if ($shipment_id) {
                $order_idd = $block->getShipment()->getOrderId();
                if ($order_idd) {
                    $collections = Mage::getModel('upslabel/upslabel');
                    $colls = $collections->getCollection()->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', 'shipment')->addFieldToFilter('status', 0)->getFirstItem();
                    if ($colls->getShipmentId() == $shipment_id) {
                        $block->addButton('order_label', array(
                            'label' => Mage::helper('sales')->__('UPS Label'),
                            'onclick' => 'setLocation(\'' . $block->getUrl('upslabel/adminhtml_upslabel/showlabel/order_id/' . $order_idd . '/shipment_id/' . $shipment_id . '/type/shipment') . '\')',
                            'class' => 'go'
                        ));
                    } else {
                        $block->addButton('order_label', array(
                            'label' => Mage::helper('sales')->__('UPS Label'),
                            'onclick' => 'setLocation(\'' . $block->getUrl('upslabel/adminhtml_upslabel/intermediate/order_id/' . $order_idd . '/shipment_id/' . $shipment_id . '/type/shipment') . '\')',
                            'class' => 'go'
                        ));
                    }
                }
            }
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Creditmemo_View) {
            $block->removeButton('cancel');
            $shipment_id = $block->getCreditmemo()->getId();
            $order_idd = $block->getCreditmemo()->getOrderId();
            if ($shipment_id) {
                $collections = Mage::getModel('upslabel/upslabel');
                $colls = $collections->getCollection()->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', 'refund')->addFieldToFilter('status', 0)->getFirstItem();
                if ($colls->getShipmentId() != $shipment_id) {
                    $block->addButton('cancel', array(
                            'label' => Mage::helper('sales')->__('UPS label'),
                            'class' => 'save',
                            'onclick' => 'setLocation(\'' . $block->getUrl('upslabel/adminhtml_upslabel/intermediate/order_id/' . $order_idd . '/shipment_id/' . $shipment_id . '/type/refund') . '\')'
                        )
                    );
                } else {
                    $block->addButton('cancel', array(
                            'label' => Mage::helper('sales')->__('UPS label'),
                            'class' => 'save',
                            'onclick' => 'setLocation(\'' . $block->getUrl('upslabel/adminhtml_upslabel/showlabel/order_id/' . $order_idd . '/shipment_id/' . $shipment_id . '/type/refund') . '\')'
                        )
                    );
                }
            }
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid && Mage::getStoreConfig('upslabel/additional_settings/order_grid_column_enable') == 1) {
            $block->addColumnAfter('statuslabel', array(
                'header' => Mage::helper('upslabel')->__('UPS label status'),
                'index' => 'statuslabel',
                'type' => 'options',
                'width' => '120px',
                'sortable' => false,
                'frame_callback' => array($this, 'callback_upsstatus'),
                'filter_condition_callback' => array($this, '_orderUpsStatusFilter'),
                'options' => Mage::getModel('upslabel/config_statuslabels')->getStatus(),
            ), 'status');
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Shipment_Grid && Mage::getStoreConfig('upslabel/additional_settings/shipment_grid_column_enable') == 1) {
            $block->addColumnAfter('upsprice', array(
                'header' => Mage::helper('adminhtml')->__('Price (UPS)'),
                'index' => 'upsprice',
                'type' => 'options',
                'width' => '100px',
                'sortable' => false,
                'frame_callback' => array($this, 'callback_upsprice'),
                'filter_condition_callback' => array($this, '_ShipUpsStatusFilter'),
                'options' => Mage::getModel('upslabel/config_statuslabels')->getStatus(),
            ), "total_qty");
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Creditmemo_Grid && Mage::getStoreConfig('upslabel/additional_settings/credit_grid_column_enable') == 1) {
            $block->addColumnAfter('upsprice', array(
                'header' => Mage::helper('adminhtml')->__('Price (UPS)'),
                'index' => 'upsprice',
                'type' => 'options',
                'width' => '100px',
                'sortable' => false,
                'frame_callback' => array($this, 'callback_upspricecredit'),
                'filter_condition_callback' => array($this, '_CreditUpsStatusFilter'),
                'options' => Mage::getModel('upslabel/config_statuslabels')->getStatus(),
            ), "state");
        }
    }

    public function frontorderplace(Varien_Event_Observer $event)
    {
        $order = $event->getEvent()->getOrder();
        if(Mage::registry('isCreateLabelNow'.$order->getId())==2){return true;}
        if (Mage::getStoreConfig('upslabel/frontend_autocreate_label/frontend_order_autocreate_label_enable' ) == 1) {
            
            $shippingActiveMethods = trim(Mage::getStoreConfig('upslabel/frontend_autocreate_label/apply_to'), " ,");
            $shippingActiveMethods = strlen($shippingActiveMethods) > 0 ? explode(",", $shippingActiveMethods) : array();
            $orderStatuses = explode(",", trim(Mage::getStoreConfig('upslabel/frontend_autocreate_label/orderstatus'), " ,"));
            if (((isset($shippingActiveMethods) && count($shippingActiveMethods) > 0 && in_array($order->getShippingMethod(), $shippingActiveMethods)) || strpos($order->getShippingMethod(), "ups_") === 0)
                && (isset($orderStatuses) && count($orderStatuses) > 0 && in_array($order->getStatus(), $orderStatuses))
            ) {
                $order_id = $order->getId();
                $type = 'shipment';
                $collections = Mage::getModel('upslabel/upslabel');
                $colls = $collections->getCollection()->addFieldToFilter('order_id', $order_id)->addFieldToFilter('type', $type);
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
                    $upsl2 = NULL;
                    if ($controller->defConfParams['default_return'] == 1) {
                        $lbl->serviceCode = array_key_exists('default_return_servicecode', $controller->defConfParams) ? $controller->defConfParams['default_return_servicecode'] : '';
                        $upsl2 = $lbl->getShipFrom();
                    }
                    Mage::register('isCreateLabelNow'.$order_id, 2);
                    $upslabel = $controller->saveDB($upsl, $upsl2, $controller->defConfParams, $order_id, 0, $type);
                    if($upslabel && Mage::getStoreConfig('upslabel/frontend_autocreate_label/track_send') == 1 && $upslabel->getShipmentId() > 0){
                        $shipment = Mage::getModel('sales/order_shipment')->load($upslabel->getShipmentId());
                        $shipment->sendEmail(true, '')
                            ->setEmailSent(true)
                            ->save();
                    }
                }
            }
            $path_upsdir = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "update" . DS;
            if (!file_exists($path_upsdir . 'last_update.txt') || (int)file_get_contents($path_upsdir . 'last_update.txt') < time() - 82400) {
                Mage::getModel('upslabel/cron')->update();
            }
        }
        return $this;
    }

    public function callback_upsstatus($value, $row, $column, $isExport)
    {
        $collections = Mage::getModel('upslabel/upslabel');
        $item = $collections->getCollection()->addFieldToFilter('order_id', $row->getId())->addFieldToFilter('type', 'shipment')->getFirstItem();
        if ($item->getStatustext()) {
            return $item->getStatustext();
        } else {
            $order = Mage::getModel('sales/order')->load($row->getId());
            
            $shippingActiveMethods = trim(Mage::getStoreConfig('upslabel/frontend_autocreate_label/apply_to'), " ,");
            $shippingActiveMethods = strlen($shippingActiveMethods) > 0 ? explode(",", $shippingActiveMethods) : array();
            if (Mage::getStoreConfig('upslabel/shipping/shipping_method_native') == 1) {
                $modelConformity = Mage::getModel("upslabel/conformity")->getCollection()->addFieldToFilter('method_id', $order->getShippingMethod())->addFieldToFilter('store_id', 
                        1);
                if ($modelConformity) {
                    foreach ($modelConformity AS $conform) {
                        $shippingActiveMethods[] = $conform["method_id"];
                    }
                }
            }

            if ((isset($shippingActiveMethods) && count($shippingActiveMethods) > 0 && in_array($order->getShippingMethod(), $shippingActiveMethods)) || strpos($order->getShippingMethod(), "ups_") === 0) {
                return Mage::helper('adminhtml')->__('UPS Pending');
            } else {
                return "";
            }
        }
    }

    public function _ShipUpsStatusFilter($collection, $column)
    {
        $this->_orderUpsStatusFilter($collection, $column, $type = "shipment", $id = "shipment_id");
    }

    public function _CreditUpsStatusFilter($collection, $column)
    {
        $this->_orderUpsStatusFilter($collection, $column, $type = "refund", $id = "shipment_id");
    }

    public function _orderUpsStatusFilter($collection, $column, $type = "shipment", $id = "order_id")
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $status = 0;
        $is_need_filter = true;
        switch ($value) {
            case "success":
                $statustext = '="Successfully"';
                $status = 0;
                break;
            case "error":
                $statustext = '!="Successfully"';
                $status = 1;
                break;
            case "notcreated":
                $is_need_filter = false;
                break;
            case "pending":
                $is_need_filter = false;
                break;
        }
        if ($is_need_filter == true) {
            $collection->getSelect()->distinct(true)->join(array("t123ups" => Mage::getConfig()->getTablePrefix() . 'upslabel'), 't123ups.' . $id . ' = main_table.entity_id AND t123ups.type="' . $type . '" AND t123ups.status="' . $status . '" AND t123ups.statustext' . $statustext, NULL);
            //$query = $collection->getSelect();
            //print_r(get_class_methods(get_class($collection->getSelect())));
            $collection->getSelect()->setPart('where', str_replace(array("(status ", " status "), array('(main_table.status ', ' main_table.status '), $collection->getSelect()->getPart('where')));
            //echo $query; exit;
        } else {

            if ($value == "pending") {
                
                $shippingActiveMethods = trim(Mage::getStoreConfig('upslabel/frontend_autocreate_label/apply_to'), " ,");
                $shippingActiveMethods = strlen($shippingActiveMethods) > 0 ? explode(",", $shippingActiveMethods) : array();
                $like = array();
                $like[] = "t1orderups.shipping_method LIKE \"ups_%\"";
                if (count($shippingActiveMethods) > 0) {
                    foreach ($shippingActiveMethods AS $item) {
                        $like[] = "t1orderups.shipping_method = \"" . $item . "\"";
                    }
                }
                if (Mage::getStoreConfig('upslabel/shipping/shipping_method_native') == 1) {
                    $modelConformity = Mage::getModel("upslabel/conformity")->getCollection()->addFieldToFilter('store_id', 
                            1);
                    if ($modelConformity && count($modelConformity) > 0) {
                        foreach ($modelConformity AS $conform) {
                            $like[] = "t1orderups.shipping_method = \"" . $conform->getMethodId() . "\"";
                        }
                    }
                }
                $like = "(" . implode(" OR ", $like) . ")";

                //$modelConformity = Mage::getModel("upslabel/conformity")->getCollection()->addFieldToFilter('store_id', 1);
                /*$shippingConformityMethods = array();
                $like2 = "1=1";
                if ($modelConformity && count($modelConformity) > 0) {
                    foreach ($modelConformity AS $conform) {
                        $shippingConformityMethods[] = "(t1orderups.shipping_method = \"" . $conform->getMethodId() . "\" AND t1addressups.country_id IN('".str_replace(",", "','", $conform->getCountryIds())."'))";
                    }
                    $like2 = "(" . implode(" OR ", $shippingConformityMethods) . ")";
                }*/

                $entityId = "entity_id";
                if ($id != "order_id") {
                    $entityId = "order_id";
                }
                $collection->getSelect()->distinct(true)->join(array("t1orderups" => Mage::getConfig()->getTablePrefix() . 'sales_flat_order'), 'main_table.' . $entityId . ' = t1orderups.entity_id AND ' . $like, NULL);
                /*if (count($shippingConformityMethods) > 0) {
                    $collection->getSelect()->join(array("t1addressups" => Mage::getConfig()->getTablePrefix() . 'sales_flat_order_address'), 'main_table.' . $entityId . ' = t1addressups.parent_id AND ' . $like2, NULL);
                }*/
                $collection->getSelect()->joinLeft(array("t123ups" => Mage::getConfig()->getTablePrefix() . 'upslabel'), 'main_table.entity_id = t123ups.' . $id . ' AND t123ups.type="' . $type . '"', NULL);

            } else {
                $collection->getSelect()->distinct(true)->joinLeft(array("t123ups" => Mage::getConfig()->getTablePrefix() . 'upslabel'), 'main_table.entity_id = t123ups.' . $id . ' AND t123ups.type="' . $type . '"', NULL);
            }
            $collection->getSelect()->where("t123ups." . $id . " IS NULL");
            $collection->getSelect()->setPart('where', str_replace(array("(status ", " status "), array('(main_table.status ', ' main_table.status '), $collection->getSelect()->getPart('where')));
            /*$query = $collection->getSelect();
            echo $query; exit;*/
        }
        return $this;
    }

    public function callback_upsprice($value, $row, $column, $isExport, $type = "shipment")
    {
        $c = '';
        $items = array();
        $collections = Mage::getModel('upslabel/labelprice');
        $items = $collections->getCollection()->addFieldToFilter('shipment_id', $row->getId())->addFieldToFilter('type', $type)->getSelect()->group('price')->query();
        if (count($items) > 0) {
            foreach ($items AS $item) {
                $c .= $item["price"] . "<br>";
            }

            return '<div style=" padding-left: 5px;">' . $c . '</div>';
        } else {
            return "";
        }
    }

    public function callback_upspricecredit($value, $row, $column, $isExport)
    {
        return $this->callback_upsprice($value, $row, $column, $isExport, "refund");
    }
}
