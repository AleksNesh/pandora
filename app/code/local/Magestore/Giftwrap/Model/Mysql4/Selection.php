<?php
class Magestore_Giftwrap_Model_Mysql4_Selection extends Mage_Core_Model_Mysql4_Abstract {
	protected function _construct()	{		
		$this->_init('giftwrap/selection', 'id');
	}
	
	public function loadByQuoteId($selection, $quoteId, $itemId) {
		$read = $this->_getReadAdapter();
		if ($read) {
			$storeId = Mage::app()->getStore()->getId();
			$select = $read->select();
			$select->from($this->getTable('giftwrap/selection'))
					->where('quote_id = ?', $quoteId)
					->where('item_id = ?', $itemId)
					->limit(1);

			$data = $read->fetchRow($select);
			if ($data) {
				$selection->setData($data);
			}
		}
		$this->_afterLoad($selection);
		return $this;
	}
	
	public function removeAllSelection($quoteId) {
		$condition = $this->_getWriteAdapter()->quoteInto('quote_id=?', $quoteId);
		$this->_getWriteAdapter()->delete(
				$this->getTable('giftwrap/selection'),
				$condition
		);
		return $this;
	}
	
	
}