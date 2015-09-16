<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */

/**
 * Fraud detection tab
 * Uses getGiftMessageHtml to insert itself as this is the only top level function available
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 */
class Directshop_FraudDetection_Block_Adminhtml_Sales_Order_View_Tab_Info
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    
	// for 1.6.1 getGiftOptionsHtml
	// for 1.4.1 getGiftMessageHtml
	
	public function getGiftMessageHtml()
    {
		
		
		$parentHTML = parent::getGiftMessageHtml();

		
		$order = $this->getOrder();
		$payment = $order->getPayment();
		$output = "";
		$outputlines = array();
		$billingCountry = Zend_Locale_Data::getContent(Mage::app()->getLocale()->getLocale(), 'country', $order->getBillingAddress()->getCountry());
		$remainingCredits = Mage::getResourceModel('frauddetection/stats')->getValue("remaining_maxmind_credits");
		
		$result = Mage::getModel('frauddetection/result')->loadByOrderId($order->getId());
	
		
		
		$res = @unserialize(utf8_decode($result->getFraudData()));
		
		if (empty($res))
		{
			if ($payment->getId())
			{
				$res = $this->helper('frauddetection')->normaliseMaxMindResponse($this->helper('frauddetection')->getMaxMindResponse($payment));
				if (empty($res['err']) || !in_array($res['err'], Directshop_FraudDetection_Model_Result::$fatalErrors))
				{
					Mage::helper('frauddetection')->saveFraudData($res, $order);
				}
			}
			else
			{
				$res = array(
					'errmsg' => 'This order has no payment data.'
				);
			}
		}

		// for backwards compatibility
   		if (!isset($res['ourscore']) && isset($res['score']))
		{
			$res['ourscore'] = $res['score'];
		}

		
		if (isset($res['err']) && (in_array($res['err'], Directshop_FraudDetection_Model_Result::$fatalErrors) || $res['ourscore'] == -1))
		{
			$output = isset($res['errmsg']) ? $res['errmsg'] : $res['err'];
		}
		else
		{
			$score = 0;
			if (isset($res['ourscore']))
			{
				$score = $res['ourscore'];
			}
			
			$colour = "auto";
			if ($res['ourscore'] >= Mage::getStoreConfig('frauddetection/general/threshold'))
			{
				$colour = "red";
			}
			$output = "<table class='form-list' cellspacing='0'><tbody><tr><td class='label' style='width: 135px;'><label style='width: 135px;'>Risk Score Estimate (%)</label></td><td><span style='color:$colour;font-weight:bold;display: block;margin-top: 5px;'>$score%  (Approximately $per of all orders fall in this range)</span></td></tr></tbody></table>";
			
		
		}
			
		$newHTML = <<<EOD
<div class="box-left">
	<div class="entry-edit-head"><h4 class="icon-head head-payment-method">Fraud Detection</h4></div>
	<fieldset>
		$output
		<small style="float:right;text-align:right;">Maxmind Credits Remaining: $remainingCredits<br/><a href="http://www.maxmind.com/app/ccfd_promo?promo=DIRECTSHOP3942">Purchase Additional Credits</a></small>
	</fieldset>
</div>
EOD;
				
		return $newHTML . $parentHTML;
    }
	
	
	
	
	
public function getGiftOptionsHtml()
    {
				
		$parentHTML = parent::getGiftOptionsHtml();
		
		$order = $this->getOrder();
		$payment = $order->getPayment();
		$output = "";
		$outputlines = array();
		$billingCountry = Zend_Locale_Data::getContent(Mage::app()->getLocale()->getLocale(), 'country', $order->getBillingAddress()->getCountry());
		$remainingCredits = Mage::getResourceModel('frauddetection/stats')->getValue("remaining_maxmind_credits");
		
		$result = Mage::getModel('frauddetection/result')->loadByOrderId($order->getId());
	
		
		
		$res = @unserialize(utf8_decode($result->getFraudData()));
		
		if (empty($res))
		{
			if ($payment->getId())
			{
				$res = $this->helper('frauddetection')->normaliseMaxMindResponse($this->helper('frauddetection')->getMaxMindResponse($payment));
				if (empty($res['err']) || !in_array($res['err'], Directshop_FraudDetection_Model_Result::$fatalErrors))
				{
					Mage::helper('frauddetection')->saveFraudData($res, $order);
				}
			}
			else
			{
				$res = array(
					'errmsg' => 'This order has no payment data.'
				);
			}
		}

		// for backwards compatibility
   		if (!isset($res['ourscore']) && isset($res['score']))
		{
			$res['ourscore'] = $res['score'];
		}

		
		if (isset($res['err']) && (in_array($res['err'], Directshop_FraudDetection_Model_Result::$fatalErrors) || $res['ourscore'] == -1))
		{
			$output = isset($res['errmsg']) ? $res['errmsg'] : $res['err'];
		}
		else
		{
			$score = 0;
			if (isset($res['ourscore']))
			{
				$score = $res['ourscore'];
			}
			
			$colour = "auto";
			if ($res['ourscore'] >= Mage::getStoreConfig('frauddetection/general/threshold'))
			{
				$colour = "red";
			}
			
			$per = "N/A";
			if(isset($score) && !empty($score)){						       
					 $riskScore = number_format($score, 2);								 
					 if($riskScore >= 0.1 && $riskScore <=4.99 ) {
							$per = "90%";
						} else if($riskScore >= 5 && $riskScore <=9.99 ) {
							$per =  "5%";
						} else if($riskScore >= 10 && $riskScore <=29.99 ) {
							$per =  "3%";
						} else if($riskScore >= 30 && $riskScore <=99.99 ) {
							$per =  "2%";
						}						   
			 }						
						
			
			
			$output = "<table class='form-list' cellspacing='0'><tbody><tr><td class='label' style='width: 135px;'><label style='width: 135px;'>Risk Score Estimate (%)</label></td><td><span style='color:$colour;font-weight:bold;display: block;margin-top: 5px;'>$score%  (Approximately $per of all orders fall in this range)</span></td></tr></tbody></table>";
			
			
		}
			
		$newHTML = <<<EOD
<div class="box-left">
	<div class="entry-edit-head"><h4 class="icon-head head-payment-method">Fraud Detection</h4></div>
	<fieldset>
		$output
		<small style="float:right;text-align:right;">Maxmind Credits Remaining: $remainingCredits<br/><a href="http://www.maxmind.com/app/ccfd_promo?promo=DIRECTSHOP3942">Purchase Additional Credits</a></small>
	</fieldset>
</div>
EOD;
				
		return $newHTML . $parentHTML;
    }
	
	
}
