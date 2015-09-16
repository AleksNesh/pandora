<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Model_Mysql4_Conformity extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('upslabel/conformity', 'upslabelconformity_id');
    }
}