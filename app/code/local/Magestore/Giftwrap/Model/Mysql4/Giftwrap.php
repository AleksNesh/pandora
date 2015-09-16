<?php

class Magestore_Giftwrap_Model_Mysql4_Giftwrap extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the giftwrap_id refers to the key field in your database table.
        $this->_init('giftwrap/giftwrap', 'giftwrap_id');
    }
}