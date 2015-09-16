<?php
class Magestore_Giftwrap_Model_Selectionitem extends Mage_Core_Model_Abstract {
	public function _construct()	{
		parent::_construct();
		$this->_init('giftwrap/selectionitem');
	}
	
	public function loadBySelectionAndItem($selectionId,$itemId){
		$collection = $this->getCollection()
						->addFieldToFilter('selection_id',$selectionId)
						->addFieldToFilter('item_id',$itemId)
						;
		if($collection->getSize())
			return $this->load($collection->getFirstItem()->getId());
		return $this;
	}
}