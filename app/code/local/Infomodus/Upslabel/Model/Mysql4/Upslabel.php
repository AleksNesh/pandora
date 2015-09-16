<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Model_Mysql4_Upslabel extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the upslabel_id refers to the key field in your database table.
        $this->_init('upslabel/upslabel', 'upslabel_id');
    }
}