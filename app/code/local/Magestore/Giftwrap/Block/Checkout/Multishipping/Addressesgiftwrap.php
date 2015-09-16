<?php
class Magestore_Giftwrap_Block_Checkout_Multishipping_Addressesgiftwrap extends Mage_Checkout_Block_Multishipping_Shipping
{
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
     // add by Sally
     public function getAllPapers() {
        $papers = Mage::getModel('giftwrap/giftwrap')
                        ->getCollection()
                        ->addFieldToFilter(
                                'store_id', Mage::app()->getStore()->getId())
                        ->addFieldToFilter('status', 1)
        ;
        $list = array();
        foreach ($papers as $paper) {
            $list[] = $paper;
        }
        return $list;
    }
    
    public function getAllGiftcards() {
        $gifcards = Mage::getModel('giftwrap/giftcard')->getCollection()
                        ->addFieldToFilter(
                                'store_id', Mage::app()->getStore()
                                ->getId())
                        ->addFieldToFilter('status', 1);
        return $gifcards;
    }
    //end
    public function getBlockGiftwrapHtml(){
        return $this->getBlockHtml('giftwrap.giftwrap');
    }

      public function getCheckout()
    {
        return Mage::getSingleton('checkout/type_multishipping');
    }

    public function getAddresses()
    {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }

    public function getAddressCount()
    {
        $count = $this->getData('address_count');
        if (is_null($count)) {
            $count = count($this->getAddresses());
            $this->setData('address_count', $count);
        }
        return $count;
    }

    public function getAddressItems($address)
    {
        $items = array();
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $item->setQuoteItem($this->getCheckout()->getQuote()->getItemById($item->getQuoteItemId()));
            $items[] = $item;
        }
        $itemsFilter = new Varien_Filter_Object_Grid();
        $itemsFilter->addFilter(new Varien_Filter_Sprintf('%d'), 'qty');
        return $itemsFilter->filter($items);
    }

    public function getAddressShippingMethod($address)
    {
        return $address->getShippingMethod();
    }

    public function getShippingRates($address)
    {
        $groups = $address->getGroupedAllShippingRates();
        return $groups;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function getAddressEditUrl($address)
    {
        return $this->getUrl('*/multishipping_address/editShipping', array('id'=>$address->getCustomerAddressId()));
    }

    public function getItemsEditUrl()
    {
        return $this->getUrl('*/*/backToAddresses');
    }

    public function getPostActionUrl()
    {
        return $this->getUrl('checkout/multishipping/addressesPost',array('continue'=>'1'));
    }

    public function getBackUrl()
    {
        return $this->getUrl('giftwrap/multishipping/backtoaddresses');
    }

    public function getShippingPrice($address, $price, $flag)
    {
        return $address->getQuote()->getStore()->convertPrice($this->helper('tax')->getShippingPrice($price, $flag, $address), true);
    }

     public function getGiftboxCollection($address_id = null){

    	$storeId = Mage::app()->getStore()->getId();
    	$quote = Mage::getSingleton('checkout/cart')->getQuote();
        if($address_id == null){
    	$collection=Mage::getModel('giftwrap/selection')->getCollection()
    				->addFieldToFilter('quote_id',$quote->getId())
    				;
        }
        if($address_id != null){
    	$collection=Mage::getModel('giftwrap/selection')->getCollection()
    				->addFieldToFilter('quote_id',$quote->getId())
                                ->addFieldToFilter('addressgift_id',$address_id)
    				;
        }
    	return $collection;
    }

}