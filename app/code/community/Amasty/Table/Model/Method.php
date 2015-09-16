<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Table_Model_Method extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('amtable/method');
    }
    
    public function massChangeStatus ($ids, $status) {
        foreach ($ids as $id) {
                $model = Mage::getModel('amtable/method')->load($id);
                $model->setIsActive($status);
                $model->save();
            }
        return $this;
    }
}