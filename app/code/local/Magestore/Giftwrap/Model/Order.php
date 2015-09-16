<?php
class Magestore_Giftwrap_Model_Order extends Mage_Core_Model_Abstract {
	public function _construct()	{
		parent::_construct();
		$this->_init('giftwrap/order');
	}

}