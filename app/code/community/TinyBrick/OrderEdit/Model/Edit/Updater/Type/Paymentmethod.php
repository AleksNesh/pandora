<?php

/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Paymentmethod extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract {

    /**
     * Edit te payment method of an order
     * @param TinyBrick_OrderEdit_Model_Order $order
     * @param array $data
     * @return string 
     */
    public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array()) {
        $array = array();
        $payment = $order->getPayment();

        $oldPaymentmethod = $payment->getMethod();
        //echo '<pre>old';print_r($oldPaymentmethod);echo '</pre>';
        //echo '<pre>new';print_r($data);echo '</pre>';die;
        if ($data['paymentmethod_id'] != '') {
            $payment->setMethod($data['paymentmethod_id']);
        }
        try {
            $payment->save();
            $newPaymentmethod = $payment->getMethod();
            $results = strcmp($oldPaymentmethod, $newPaymentmethod);
            if ($results != 0) {
                $comment = "Changed payment method:<br />";
                $comment .= "Changed FROM: " . $oldPaymentmethod . " TO: " . $newPaymentmethod . "<br /><br />";
                return $comment;
            }
            return true;
        } catch (Exception $e) {
            $array['status'] = 'error';
            $array['msg'] = "Error updating payment method";
            return false;
        }
        return true;
    }

}