<?php
/**
 * @copyright   Copyright (c) 2009-14 Amasty
 */
class Amasty_Promo_Model_CatalogInventory_Observer extends Mage_CatalogInventory_Model_Observer
{   
    /**
     * Check product inventory data when quote item quantity declaring
     *
     * @param  Varien_Event_Observer $observer
     * @return Mage_CatalogInventory_Model_Observer
     */
    public function checkQuoteItemQty($observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote() || $quoteItem->getQuote()->getIsSuperMode()) {
            return $this;
        }
        
		// added to skip double inventory check for promo item.
        if ($quoteItem->getPrice() === NULL){ 
            return $this; 
		}
            
        return parent::checkQuoteItemQty($observer);
    }
}
