<?php
class Magestore_Giftwrap_Block_Styleselection extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
	
    public function getAllItems()
    {
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $items = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId(
        $quoteId);
        return $items;
    }
	
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
	
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }
	
    public function getProductThumbnail($itemId)
    {
        $item = $this->getQuote()->getItemById($itemId);
        if ($item) {
            return $this->helper('catalog/image')
                ->init($item->getProduct(), 'thumbnail')
                ->resize(75);
        }
        return null;
    }
	
    public function getProductUrl($itemId)
    {
        $item = $this->getQuote()->getItemById($itemId);
        if ($item) {
            return $item->getProduct()->getProductUrl();
        }
        return null;
    }
	
    public function getProductName ($itemId)
    {
        $item = $this->getQuote()->getItemById($itemId);
        if ($item) {
            return $item->getProduct()->getName();
        }
        return null;
    }
	
    public function getAllStyles()
    {
        $styles = array();
        $collection = $this->getCollection();
        if ($collection) {
            foreach ($collection as $item) {
                $styles[$item['giftwrap_id']] = array(
                'id' => $item['giftwrap_id'], 'title' => $item['title'], 
                'price' => $item['price'], 'image' => $item['image'], 
                'personal_message' => $item['personal_message'], 
                'character' => $item['character']);
            }
        }
        return $styles;
    }
	
    public function getCollection()
    {
        $collection = Mage::getModel('giftwrap/giftwrap')->getCollection();
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter('store_id', 
        Mage::app()->getStore(true)
            ->getId());
        $collection->setOrder('sort_order', 'asc');
        $collection->load();
        return $collection;
    }
	
    public function getGiftwrapBasePrice()
    {
        return Mage::getStoreConfig('giftwrap/general/base_price');
    }
	
    public function getGiftwrapMessage()
    {
        return Mage::getSingleton('giftwrap/session')->getData(
        'giftwrap_message');
    }
	
    public function getOptionList ($itemId)
    {
        $optionIds = array();
        $item = Mage::getModel('sales/quote_item')->load($itemId);
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        $optioncollection=$product->getProductOptionsCollection();
        foreach ($optioncollection as $op){
        }
        $opts = Mage::getModel('sales/quote_item_option')->getCollection()
            ->addFieldToFilter('code', 'option_ids')
            ->getOptionsByItem($item);
        foreach ($opts as $opt) {
            $value=$opt->getValue();
			$values=explode(',',$value);
			foreach($values as $v){
				$optionIds[] = $v;
			}
        }
        if ($optionIds) {
            $options = array();
            foreach ($optionIds as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                	
                    $quoteItemOption = Mage::getModel('sales/quote_item_option')
                    					->getCollection()
                    					->addFieldToFilter('code','option_' . $option->getId())
                    					->addFieldToFilter('item_id',$item->getId())
                    					->getFirstItem()
                    					;
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);
                    $options[] = array('label' => $option->getTitle(), 
                    'value' => $group->getFormattedOptionValue(
                    $quoteItemOption->getValue()), 
                    'print_value' => $group->getPrintableOptionValue(
                    $quoteItemOption->getValue()), 
                    'option_id' => $option->getId(), 
                    'option_type' => $option->getType(), 
                    'custom_view' => $group->isCustomizedView());
                }
            }
        }
        if ($addOptions = $item->getOptionByCode('additional_options')) {
            $options = array_merge($options, 
            unserialize($addOptions->getValue()));
        }
        return $options;
    }
	 
}