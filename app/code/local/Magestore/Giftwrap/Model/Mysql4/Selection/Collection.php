<?php
class Magestore_Giftwrap_Model_Mysql4_Selection_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct()	{
			parent::_construct();
			$this->_init('giftwrap/selection');
	}
}