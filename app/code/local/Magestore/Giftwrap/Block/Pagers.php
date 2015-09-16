<?php
class Magestore_Giftwrap_Block_Pagers extends Mage_Core_Block_Template
{
	public function _prepareLayout() {
		return parent::_prepareLayout();
	}
    
	public function getPagers() {
		$pagers = array();
		$collection = $this->getCollection()
						//->addFieldToFilter('status',1)
						;
		foreach ($collection as $item) {
			$pagers[$item['giftwrap_id']] = array('id' => $item['giftwrap_id'],
												'title' => $item['title'],
												'price' => $item['price'], 
												'image' => $item['image'], 
												);
		}
		return $pagers;
	}	
	
	public function getCollection() {
		$collection = Mage::getModel('giftwrap/giftwrap')->getCollection();
		$collection->addFieldToFilter('store_id',Mage::app()->getStore(true)->getId());
		$collection->setOrder('sort_order', 'asc');
		$collection->load();
		return $collection;
	}
}