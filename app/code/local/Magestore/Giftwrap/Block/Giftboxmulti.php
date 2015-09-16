<?php
class Magestore_Giftwrap_Block_Giftboxmulti extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }


    public function getAddresses() {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }

    public function getAddress_id() {
        return $this->getRequest()->getParam('id');
    }

//    public function getGiftboxCollection($address_id = null){
//
//    	$storeId = Mage::app()->getStore()->getId();
//    	$quote = Mage::getSingleton('checkout/cart')->getQuote();
//        if($address_id == null){
//    	$collection=Mage::getModel('giftwrap/selection')->getCollection()
//    				->addFieldToFilter('quote_id',$quote->getId())
//    				;
//        }
//        if($address_id != null){
//    	$collection=Mage::getModel('giftwrap/selection')->getCollection()
//    				->addFieldToFilter('quote_id',$quote->getId())
//                                ->addFieldToFilter('addressgift_id',$address_id)
//    				;
//        }
//    	return $collection;
//    }
    public function getGiftboxInOrder($order){
    	
    	$itemcollection=$order->getItemsCollection()
								;
		$itemGiftvoucherRenderer = $this->getLayout()->getBlock('items')->getItemRenderer('giftvoucher');
		$itemDefaultRenderer = $this->getLayout()->getBlock('items')->getItemRenderer();
		$item_ids = array();
		if($itemGiftvoucherRenderer->getItem()){
			$itemGiftvoucher = $itemGiftvoucherRenderer->getItem();
			$item_ids[] = $itemGiftvoucher->getId();
		}
		if($itemDefaultRenderer->getItem()){
			$itemDefault = $itemDefaultRenderer->getItem();
			$item_ids[] = $itemDefault->getId();
		}
		
		//$product = $item->getProduct();
		//Zend_Debug::dump($item_ids);
		/* if($item->getOrderItemId()){
			$item_id = $item->getOrderItemId();
		}else{
			$item_id = $item->getId();
		} */
		$lastItem=$itemcollection->getLastItem();
		if($lastItem->getParentItemId()){
			$lastId=$lastItem->getParentItemId();
		}else{
			$lastId=$lastItem->getId();
		}
		if(count($item_ids)){
			if(!in_array($lastId,$item_ids)){
				return ;
			}
		}
		
		$quoteId=$order->getQuoteId();
		$orderAddress = Mage::getModel('sales/order_address')->getCollection()
			->addFieldToFilter('parent_id',$order_id)
			->addAttributeToSort('entity_id', 'DESC');
			;
			foreach($orderAddress as $address){
			$addressCutomer = $address->getData('customer_address_id');
			break;
		$giftwrapCollection = array();
		if ($quoteId) {
			$giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId,null,null,$addressCutomer);
		}
		return $giftwrapCollection;
		}
    }
	
	protected function _beforeToHtml()
	{
		if(!Mage::helper('magenotification')->checkLicenseKey('Giftwrap')){
			$this->setTemplate(null);
		}
		return parent::_beforeToHtml();
	}
}