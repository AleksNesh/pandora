<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
class Infomodus_Upslabel_Model_Config_ShippingSettingsLink
{
    public function getCommentText()
    {
        return '<a href="'.Mage::helper("adminhtml")->getUrl("upslabel/adminhtml_conformity/index").'" target="_blank">'.Mage::helper('adminhtml')->__("Settings").'</a>';
    }
}