<?php

class Fooman_Testing_Model_Session_Quote extends Mage_Adminhtml_Model_Session_Quote {


    /**
     * Resets all data
     *
     * @return void
     */
    public function resetAll()
    {
        $this->clear();
        $this->_quote   = null;
        $this->_customer= null;
        $this->_store   = null;
        $this->_order   = null;
    }
}