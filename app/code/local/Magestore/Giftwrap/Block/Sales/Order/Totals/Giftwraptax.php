<?php
class Magestore_Giftwrap_Block_Sales_Order_Totals_Giftwraptax extends Mage_Sales_Block_Order_Totals
{
	public function initTotals()
    {
		if($this->giftwrapTax() > 0){
			$total = new Varien_Object();
			$total->setCode('giftwraptax');
			$total->setValue($this->giftwrapTax());
			$total->setBaseValue(0);
			$total->setLabel('Gift Wrap Tax');
			$parent = $this->getParentBlock();
			$parent->addTotal($total,'giftwrap');
		}
	}
	
	public function giftwrapTax() {
		$order = $this->getParentBlock()->getOrder();
		$giftwrapTax = $order->getGiftwrapTax();
		return $giftwrapTax;
	}
}