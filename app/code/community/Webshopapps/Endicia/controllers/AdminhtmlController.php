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

class Webshopapps_Endicia_AdminhtmlController extends Mage_Adminhtml_Controller_Action
{

    public function balanceAction()
    {
        $carrier = Mage::getModel('wsaendicia/carrier_endicia');
        $balanceArray = $carrier->getPostageBalance();

        $message =  Mage::helper('wsaendicia')->__('There was an issue with your balance enquiry');
        $success = 0;
        foreach($balanceArray as $account =>$balance) {
            if ($account == Webshopapps_Endicia_Model_Carrier_Endicia::ERROR_CODE) {
                $message = Mage::helper('wsaendicia')->__('Endicia current account balance enquiry returned the following error: ' .$balance);
            }
            else {
                $success = 1;
                $message = Mage::helper('wsaendicia')->__('Endicia current account balance for Account ID ' .$account .' is $' .$balance);
            }
        }
        $result= array('result' =>$success, 'message' =>$message);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function recreditAction()
    {
        $carrier = Mage::getModel('wsaendicia/carrier_endicia');
        $amount = $this->getRequest()->getParam('amount');
        $recreditResult = $carrier->purchasePostage($amount);

        $message =  Mage::helper('wsaendicia')->__('There was an issue with your postage purchase');
        $success = 0;
        foreach($recreditResult as $account =>$result) {
            if ($account == Webshopapps_Endicia_Model_Carrier_Endicia::ERROR_CODE) {
                $message = Mage::helper('wsaendicia')->__('Purchase postage from Endicia returned the following error: ' .$result);
            }
            else {
                $success = 1;
                $message = Mage::helper('wsaendicia')->__('Postage has been purchased. Account: ' .$account .' has balance : $' .$result );
            }
        }
        $result= array('result' =>$success, 'message' =>$message);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function changepassphraseAction()
    {
        $carrier = Mage::getModel('wsaendicia/carrier_endicia');
        $passphrase = $this->getRequest()->getParam('newpassphrase');
        $changePassResult = $carrier->changePassPhrase($passphrase);

        $success = 0;
        foreach($changePassResult as $resultCode => $result) {
            if ($resultCode == Webshopapps_Endicia_Model_Carrier_Endicia::ERROR_CODE) {
                $message = Mage::helper('wsaendicia')->__('Change pass phrase returned the following error: ' .$result);
            }
            else {
                $success = 1;
                $message = Mage::helper('wsaendicia')->__('Passphrase has been changed');
            }
        }
        $result= array('result' =>$success, 'message' =>$message);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));


    }

}
