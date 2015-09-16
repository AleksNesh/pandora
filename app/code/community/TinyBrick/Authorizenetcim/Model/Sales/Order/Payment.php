<?php
class TinyBrick_Authorizenetcim_Model_Sales_Order_Payment extends Mage_Sales_Model_Order_Payment
{
	protected function _authorize($isOnline, $amount)
	{
		// Overwrites the fraud status data
		$this->setShouldCloseParentTransaction(false);
		if (!$this->_isCaptureFinal($amount)) {
			$this->setIsFraudDetected(false);
		}
	
		// update totals
		$amount = $this->_formatAmount($amount, true);
		$this->setBaseAmountAuthorized($amount);
	
		// do authorization
		$order  = $this->getOrder();
		$state  = Mage_Sales_Model_Order::STATE_PROCESSING;
		$status = true;
		if ($isOnline) {
			// invoke authorization on gateway
			$this->getMethodInstance()->setStore($order->getStoreId())->authorize($this, $amount);
		}
	
		// similar logic of "payment review" order as in capturing
		if ($this->getIsTransactionPending()) {
			$message = Mage::helper('sales')->__('Authorizing amount of %s is pending approval on gateway.', $this->_formatPrice($amount));
			$state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
			if ($this->getIsFraudDetected()) {
				$status = Mage_Sales_Model_Order::STATUS_FRAUD;
			}
		} else {
			if ($this->getIsFraudDetected()) {
				$state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
				$message = Mage::helper('sales')->__('Order is suspended as its authorizing amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
				$status = Mage_Sales_Model_Order::STATUS_FRAUD;
			} else {
				$message = Mage::helper('sales')->__('Authorized amount of %s.', $this->_formatPrice($amount));
			}
		}
	
		// update transactions, order state and add comments
		$transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
		if ($order->isNominal()) {
			$message = $this->_prependMessage(Mage::helper('sales')->__('Nominal order registered.'));
		} else {
			$message = $this->_prependMessage($message);
			$message = $this->_appendTransactionToMessage($transaction, $message);
		}
		$order->setState($state, $status, $message);
	
		return $this;
	}
}