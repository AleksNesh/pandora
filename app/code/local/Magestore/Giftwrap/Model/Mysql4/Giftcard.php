<?php
class Magestore_Giftwrap_Model_Mysql4_Giftcard extends Mage_Core_Model_Mysql4_Abstract {
	protected function _construct()	{		
		$this->_init('giftwrap/giftcard', 'giftcard_id');
	}
}