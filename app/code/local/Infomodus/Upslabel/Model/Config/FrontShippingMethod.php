<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_FrontShippingMethod
{
    public function toOptionArray($isMultiSelect = false)
    {
        
        /*
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers($storeId);

        $options = array();

        foreach($methods as $_code => $_method)
        {
            if(!$_title = Mage::getStoreConfig("carriers/$_code/title")){
                $_title = $_code;
            }

            $options[] = array('value' => $_code, 'label' => $_title . " ($_code)");
        }

        if($isMultiSelect)
        {
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;*/

        $option = array(array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        $_methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach($_methods as $_carrierCode => $_carrier){
            if($_carrierCode !=="ups" && $_carrierCode !=="dhlint" && $_carrierCode !=="usps" && $_method = $_carrier->getAllowedMethods())  {
                /*if(!$_title = Mage::getStoreConfig('carriers/'.$_carrierCode.'/title')) {*/
                    $_title = $_carrierCode;
                /*}*/
                foreach($_method as $_mcode => $_m){
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[] = array('label' => "(".$_title.")  ". $_m, 'value' => $_code);
                }
            }
        }
        return $option;
    }
}