<?php
	class Magestore_Giftwrap_Block_Renderer_Shipping extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	
		public function getGiftwrapCollection(){
		$giftwrapCollection = Mage::getModel('giftwrap/selection')->getCollection();
		$giftwrapCollectionIds = array();
		$i = 0;
		foreach($giftwrapCollection as $selection){
			$giftwrapCollectionIds[$i] = $selection->getQuoteId();			
			$i++;
		}		
		$orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('quote_id', array('in'=> $giftwrapCollectionIds));
		$orderIds = array();
		foreach($orders as $order){
			$orderIds[$i] = $order->getId();
			$i++;
		} 
		return $orderIds;
	}
		
		public function render(Varien_Object $row){		    
			$collection = Mage::getResourceModel('sales/order_grid_collection')
						->addAttributeToFilter('entity_id',$row->getId());
			foreach($collection as $order){				
					$shippingName = $order->getShippingName();
			}			
			$icon_image = Mage::helper('giftwrap')->getGiftwrapIcon();	
			$icon_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'giftwrap/icon/' . $icon_image;	
			 if(in_array($row->getId(), $this->getGiftwrapCollection()))
				return $shippingName." <img style='max-width:16px; max-height:16px;' src='".$icon_url."' title='".$this->__('Giftwrap Order')."'>";
			else return $shippingName;
		}	
	}
?>