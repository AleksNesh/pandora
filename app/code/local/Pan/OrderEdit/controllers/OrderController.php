<?php

/**
 * Extend/Override TinyBrick_OrderEdit module
 *
 * @category    Pan
 * @package     Pan_OrderEdit
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// explicitly require parent controller b/c controllers are not autoloaded
include_once("TinyBrick/OrderEdit/controllers/OrderController.php");

class Pan_OrderEdit_OrderController extends TinyBrick_OrderEdit_OrderController
{
    public function editAction()
    {
        Mage::log('hit ' . __CLASS__ . '::' . __FUNCTION__);
        // exit;

        $order = $this->_initOrder();
        /*
         * arrays for restoring order if error is thrown or payment is declined
         */
        $orderArr   = $order->getData();
        $billingArr = $order->getBillingAddress()->getData();
        if (!$order->getIsVirtual()){
            $shippingArr = $order->getShippingAddress()->getData();
        }

        try {
            $preTotal   = $order->getGrandTotal();
            $edits      = array();
            $msgs       = array();
            $changes    = array();

            foreach ($this->getRequest()->getParams() as $param) {
                if (substr($param, 0, 1) == '{') {
                    if ($param = Zend_Json::decode($param)) {
                        $edits[] = $param;
                    }
                }
            }

            foreach ($edits as $edit) {
                if ($edit['type']) {
                    $model = Mage::getModel('orderedit/edit_updater_type_' . $edit['type']);
                    if (!$changes[] = $model->edit($order, $edit)) {
                        $msgs[] = "Error updating " . $edit['type'];
                    }
                }
            }

            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log('$edits: ' . print_r($edits, true));
            Mage::log('$msgs: ' . print_r($msgs, true));

            $order->collectTotals()->save();

            $postTotal = $order->getGrandTotal();

            $_SESSION['teo_post_total'] = $postTotal;
            $_SESSION['teo_pre_total']  = $preTotal;

            if (count($msgs) < 1) {

                /**
                 * auth for more if the total has increased and configured to do so
                 */
                if(Mage::getStoreConfig('toe/orderedit/auth')) {
                    if($postTotal > $preTotal) {
                        $payment        = $order->getPayment();
                        $orderMethod    = $payment->getMethod();

                        // changed to pass only amount that was added to order (used to be: $postTotal)
                        $diffAmount = $postTotal - $preTotal;
                        // make sure the amount is in decimal format when trying to authorize
                        $authAmount = number_format((float) $diffAmount, 2);

                        /**
                         * only try an authorization if the $authAmount
                         * is greater than zero (i.e., something was added
                         * to the order)
                         */
                        if(!in_array($orderMethod, array('free', 'checkmo', 'purchaseorder')) && $authAmount > 0) {
                        // if(!in_array($orderMethod, array('free', 'checkmo', 'purchaseorder'))) {
                            // if unable to authorize new amount, rollback
                            if(!$payment->authorize($payment, $authAmount)) {
                                $message = "There was an error re-authorizing payment.";
                                $this->_getSession()->addError($message);
                                $this->_orderRollBack($order, $orderArr, $billingArr, $shippingArr);
                                echo $message;
                                return $this;
                            }
                        }
                    }
                }

                /**
                 * fire event and log changes
                 */
                Mage::dispatchEvent('orderedit_edit', array('order' => $order));
                $this->_logChanges($order, $this->getRequest()->getParam('comment'), $this->getRequest()->getParam('admin_user'), $changes);
                echo "Order updated successfully. The page will now refresh.";
            } else {
                $this->_orderRollBack($order, $orderArr, $billingArr, $shippingArr);
                echo "There was an error saving information, please try again.";
            }
        } catch (Exception $e) {
            // Add the exception message to the admin session
            $this->_getSession()->addError($e->getMessage());
            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . '() AT LINE ' . __LINE__);
            echo $e->getMessage();
            $this->_orderRollBack($order, $orderArr, $billingArr, $shippingArr);
        }
        return $this;
    }
}
