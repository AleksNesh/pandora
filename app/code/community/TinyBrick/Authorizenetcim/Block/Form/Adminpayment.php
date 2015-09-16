<?php
class TinyBrick_Authorizenetcim_Block_Form_Adminpayment extends Mage_Payment_Block_Form_Cc
{
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('authorizenetcim/payment/form/cc.phtml');
	}	
	
	public function getSavedCreditCards($customerID)
	{
		$savedCreditCards = Mage::getModel('authorizenetcim/authorizenetcim')
			->getCollection()->addFieldToFilter('customer_id', $customerID);

		$ccSaved = array();	
		
		foreach ($savedCreditCards as $savedCreditCard)
		{
			$ccSaveId = $savedCreditCard->getTinybrickAuthorizenetcimCcsaveId();
			$ccType = $savedCreditCard->getCcType();
			$ccLast4 = $savedCreditCard->getCcLast4();
			$ccExpMonth = (int)$savedCreditCard->getCcExpMonth();
			$ccExpYear = $savedCreditCard->getCcExpYear();
			
			switch($ccType) {
				case 'AE':
					$ccFullLast4 = '37000000000'.$ccLast4;
					$ccFullLast4 = (int)$ccFullLast4;
					break;
				case 'VI':
					$ccFullLast4 = '411111111111'.$ccLast4;
					$ccFullLast4 = (int)$ccFullLast4;
					break;
				case 'MC':
					$ccFullLast4 = '555555555555'.$ccLast4;
					$ccFullLast4 = (int)$ccFullLast4;
					break;
				case 'DI':
					$ccFullLast4 = '601100000000'.$ccLast4;
					$ccFullLast4 = (int)$ccFullLast4;
					break;
			}
			
			$ccSaved[] = array(
				'id' => $ccSaveId,
				'type' => $ccType,
				'last4' => $ccLast4,
				'expmonth' => $ccExpMonth,
				'expyear' => $ccExpYear,	
				'fullcc' => $ccFullLast4,
			);
		}
		return $ccSaved;
	}
}