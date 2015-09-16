<?php
class TinyBrick_Authorizenetcim_Adminhtml_AdminController extends Mage_Core_Controller_Front_Action
{

	public function deleteAction()
	{

		$params = $this->getRequest()->getParams();
		$paymentId = $params['paymentId'];
		$profileId = $params['profileId'];
		$storeId = $params['storeId'];
		
		$xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml');
		/**
		 * This checks if the profile exists
		 */
		$xml->getCustomerPaymentProfileRequest(array(
				'customerProfileId' => $profileId,
				'customerPaymentProfileId' => $paymentId,
				));
		
		$result = Mage::helper('authorizenetcim')->getResult($xml);
		/**
		 * This deletes the profile
		 */
		if($result){
			$xml->deleteCustomerPaymentProfileRequest(array(
				'customerProfileId' => $profileId,
				'customerPaymentProfileId' => $paymentId
				));
		
			Mage::helper('authorizenetcim')->response($xml);
		}
		
		
		/**
		 * Need to delete it foreach similar account
		 */
		$stores = Mage::helper('authorizenetcim')->getAllMatchingStores($xml->getLogin());
		foreach($stores as $store){
			$profileUploads = Mage::getModel('authorizenetcim/authorizenetcim')
				->getCollection()
				->addFieldToFilter('token_payment_profile_id', $paymentId)
				->addFieldToFilter('store_id', $store)
				;
				foreach($profileUploads as $profileUpload){
					$profileUpload->delete();
				}
		}
	
	}
	
	public function updateAction()
	{
		
		$params = $this->getRequest()->getParams();
		$paymentId = $params['paymentId'];
		$profileId = $params['profileId'];
		$expMonth = $params['expMonth'];
		$expYear = $params['expYear'];
		$ccType = $params['ccType'];
		$cc = $params['cc'];
		
		/**
		 * This deletes the profile
		 */
		$xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml');
		$xml->updateCustomerPaymentProfileRequest(array(
						        'customerProfileId' => $profileId,
						        'paymentProfile' => array(
						            'payment' => array(
						                'creditCard' => array(
						                	'cardNumber' => $cc,
						                    'expirationDate' => $expYear . '-' . $expMonth
						                )
						            ),
						            'customerPaymentProfileId' => $paymentId
						        )
						    ));
		
		Mage::helper('authorizenetcim')->response($xml);
		
		$profileUpload = Mage::getModel('authorizenetcim/authorizenetcim');
		$profileUpload->load($paymentId, 'token_payment_profile_id'); 
		$profileUpload->setCcExpMonth($expMonth);
		$profileUpload->setCcExpYear($expYear);
		if(isset($ccType)){
			$profileUpload->setCcType($ccType);
		}
		$profileUpload->save();
		
	}
	
}