<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 10.02.15
 * Time: 22:13
 */

class Infomodus_Upslabel_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function getUpdate()
    {
        $path_update = Mage::getBaseDir('media') . DS . 'upslabel' . DS . "update" . DS;
        if(file_exists($path_update . 'description.txt') && file_exists($path_update . 'version.txt') && Mage::getConfig()->getNode('default/upslabel/myoption/version') != file_get_contents($path_update . 'version.txt')){
            $message = file_get_contents($path_update . 'description.txt');
            return $message;
        }
        else {
            return FALSE;
        }


    }
}