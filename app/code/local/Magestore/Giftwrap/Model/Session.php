<?php
class Magestore_Giftwrap_Model_Session extends Mage_Core_Model_Session_Abstract {      
	public function __construct() {
		$this->init('giftwrap');	
	}

	public function unsetAll() {
		parent::unsetAll();
		$this->_collections = null;
	}

	public function clear() {        
		$this->setData("items",null);            		
	}
    
	public function addItem($quoteId, $itemId, $item_data)	{
		$items = $this->getData("items");	
		$sessionQuoteId = $items['quoteId'];
		if ($sessionQuoteId == $quoteId) {
			$giftwrap_items = $items['giftwrap_items'];
			$giftwrap_items[$itemId] = $item_data;
			$items = array('quoteId' => $quoteId, 'giftwrap_items' => $giftwrap_items);			
		}
		else {
			$this->clear();
			$giftwrap_items = array();
			$giftwrap_items[$itemId] = $item_data;
			$items = array('quoteId' => $quoteId, 'giftwrap_items' => $giftwrap_items);
		}
		$this->setData("items",$items);
	}
	
	public function getAllItems()	{
		$items = $this->getData("items");
		$quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
		if ($quoteId == $items['quoteId']) {
			$giftwrap_items = $items['giftwrap_items'];
			return $giftwrap_items;
		}
		return null;
	}
	
	public function deleteItem($quoteId, $item_id) {
		$quotes = $this->getData("items");		
		if (is_array($quotes) && ($quotes['quoteId'] == $quoteId)) {				
			$giftwrap_items = $quotes['giftwrap_items'];
			if (is_array($giftwrap_items) && isset($giftwrap_items[$item_id])) {
				unset($giftwrap_items[$item_id]);
			}
			$quotes = array('quoteId' => $quoteId, 'giftwrap_items' => $giftwrap_items);
		}
		$this->setData("items",$quotes);				
	}
	
	public function giftwrapSelected($itemId) {
		$quotes = $this->getData('items');
		$quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
		if ($quoteId == $quotes['quoteId']) {		
			$giftwrap_items = $quotes['giftwrap_items'];		
			if (is_array($giftwrap_items) && isset($giftwrap_items[$itemId])) {
				return $giftwrap_items[$itemId];
			}
		}
		return false;
	}

	public function getItemCount() {
		$items = $this->getData("items");
		$giftwrap_items = $items['giftwrap_items'];
		if (is_array($giftwrap_items)) return count($giftwrap_items);
		return null;
	}
	
	public function chooseStyle($itemId, $styleId, $quoteId) {
		$items = $this->getData("items");
		if ($quoteId == $items['quoteId']) {
			$giftwrap_items = $items['giftwrap_items'];
			$giftwrap_items[$itemId] = array('itemId' => $itemId, 'styleId' => $styleId);
			$items = array('quoteId' => $quoteId, 'giftwrap_items' => $giftwrap_items);
		}
		else {
			$this->clear();
			$giftwrap_items = array();
			$giftwrap_items[$itemId] = array('itemId' => $itemId, 'styleId' => $styleId);
			$items = array('quoteId' => $quoteId, 'giftwrap_items' => $giftwrap_items);
		}
		$this->setData("items",$items);
	}

	public function setNewQuote($newQuoteId) {
		$items = $this->getData("items");
		if ($items['quoteId'] != $newQuoteId) {
			$this->clear();
			$giftwrap_items = $items['giftwrap_items'];
			$quotes = array('quoteId' => $newQuoteId, 'giftwrap_items' => $giftwrap_items);
			$this->setData("items", $quotes);
		}
	}
}