<?php

include_once 'Mage/Checkout/controllers/MultishippingController.php';

class Magestore_Giftwrap_MultishippingController extends Mage_Checkout_MultishippingController {

    public function addgiftwrapAction() {
        $this->_getState()->setActiveStep(
                Magestore_Giftwrap_Model_Multishipping_State::STEP_SHIPPING_WIFTWRAP
        );
	
        $this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Select Giftwrap'));
        $this->renderLayout();
    }

    public function getCheckout() {
        return Mage::getSingleton('checkout/type_multishipping');
    }

    public function addressesPostAction() {

        $quoteId = $this->getCheckout()->getQuote()->getId();
        $giftwrapCollection = $selectionCollection = Mage::getModel('giftwrap/selection')
                        ->getCollection()
                        ->addFieldToFilter('quote_id', $quoteId)
                        //->addFieldToFilter('addressgift_id', array('notnull' => true))
        ;
		Mage::getSingleton('core/session')->setData('multi',1);
        if (count($giftwrapCollection)) {
            foreach ($giftwrapCollection as $collection) {
                $collection->delete();
            }
        }

        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('checkout/multishipping_address/newShipping');
            return;
        }
        try {
            if ($this->getRequest()->getParam('continue', false)) {
                $this->_getCheckout()->setCollectRatesFlag(true);
                $this->_getState()->setActiveStep(
                        Magestore_Giftwrap_Model_Multishipping_State::STEP_SHIPPING_WIFTWRAP
                );
                $this->_getState()->setCompleteStep(
                        Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES
                );
                $this->_redirect('*/*/addgiftwrap');
            } elseif ($this->getRequest()->getParam('new_address')) {
                $this->_redirect('checkout/multishipping_address/newShipping');
            } else {
                $this->_redirect('checkout/multishipping/addresses');
            }
            if ($shipToInfo = $this->getRequest()->getPost('ship')) {
                $this->_getCheckout()->setShippingItemsInformation($shipToInfo);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/addresses');
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addException(
                    $e,
                    Mage::helper('checkout')->__('Data saving problem')
            );
            $this->_redirect('*/*/addresses');
        }
    }

    public function backToAddressesAction() {
        $this->_getState()->setActiveStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES
        );
        $this->_getState()->unsCompleteStep(
                Magestore_Giftwrap_Model_Multishipping_State::STEP_SHIPPING_WIFTWRAP
        );
        $this->_redirect('checkout/multishipping/addresses');
    }

    public function backToGiftwapAction() {
        $this->_getState()->setActiveStep(
                Magestore_Giftwrap_Model_Multishipping_State::STEP_SHIPPING_WIFTWRAP
        );
        $this->_getState()->unsCompleteStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
        );
        $this->_redirect('giftwrap/multishipping/addgiftwrap');
    }

}