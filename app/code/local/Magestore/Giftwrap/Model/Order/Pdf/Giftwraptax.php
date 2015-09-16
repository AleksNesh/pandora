<?php

class Magestore_Giftwrap_Model_Order_Pdf_Giftwraptax 
		extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    public function getTotalsForDisplay()
    {
		$amount = $this->getAmount();
		$fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
		if(floatval($amount))
		{
			$amount = $this->getOrder()->formatPriceTxt($amount);
			if ($this->getAmountPrefix()) 
			{
				$amount = $this->getAmountPrefix().$amount;
			}		
			
			$totals = array(array(
						'label' => Mage::helper('giftwrap')->__('Giftwrap Tax'),
						'amount' => $amount,
						'font_size' => $fontSize,
						)
				);
				
			return $totals;
		}
	}
	
    public function getAmount()
    {
        return $this->getOrder()->getGiftwrapTax();
    }	
}