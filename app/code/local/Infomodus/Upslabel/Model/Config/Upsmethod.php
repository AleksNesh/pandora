<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Upsmethod
{
    public function toOptionArray()
    {
        /*return array(
          array('value' => 0, 'label' => 'First item'),
        );*/
        $c = array(
            /*array('label' => 'Default', 'value' => ''),*/
            array('label' => 'UPS Next Day Air', 'value' => '01'),
            array('label' => 'UPS Second Day Air', 'value' => '02'),
            array('label' => 'UPS Ground', 'value' => '03'),
            array('label' => 'UPS Three-Day Select', 'value' => '12'),
            array('label' => 'UPS Next Day Air Saver', 'value' => '13'),
            array('label' => 'UPS Next Day Air Early A.M. SM', 'value' => '14'),
            array('label' => 'UPS Second Day Air A.M.', 'value' => '59'),
            array('label' => 'UPS Saver', 'value' => '65'),
            array('label' => 'UPS Worldwide ExpressSM', 'value' => '07'),
            array('label' => 'UPS Worldwide ExpeditedSM', 'value' => '08'),
            array('label' => 'UPS Standard', 'value' => '11'),
            array('label' => 'UPS Worldwide Express PlusSM', 'value' => '54'),
            array('label' => 'UPS Today StandardSM', 'value' => '82'),
            array('label' => 'UPS Today Dedicated CourrierSM', 'value' => '83'),
            array('label' => 'UPS Today Express', 'value' => '85'),
            array('label' => 'UPS Today Express Saver', 'value' => '86'),
        );
        return $c;
    }

    public function getUpsMethods()
    {
        $c = array(
            '01' => 'UPS Next Day Air',
            '02' => 'UPS Second Day Air',
            '03' => 'UPS Ground',
            '07' => 'UPS Worldwide ExpressSM',
            '08' => 'UPS Worldwide ExpeditedSM',
            '11' => 'UPS Standard',
            '12' => 'UPS Three-Day Select',
            '13' => 'UPS Next Day Air Saver',
            '14' => 'UPS Next Day Air Early A.M. SM',
            '54' => 'UPS Worldwide Express PlusSM',
            '59' => 'UPS Second Day Air A.M.',
            '65' => 'UPS Saver',
            '82' => 'UPS Today StandardSM',
            '83' => 'UPS Today Dedicated CourrierSM',
            '85' => 'UPS Today Express',
            '86' => 'UPS Today Express Saver',
        );

        return $c;
    }

    public function getUpsMethodName($code = '')
    {
        $c = array(
            '01' => 'UPS Next Day Air',
            '02' => 'UPS Second Day Air',
            '03' => 'UPS Ground',
            '07' => 'UPS Worldwide ExpressSM',
            '08' => 'UPS Worldwide ExpeditedSM',
            '11' => 'UPS Standard',
            '12' => 'UPS Three-Day Select',
            '13' => 'UPS Next Day Air Saver',
            '14' => 'UPS Next Day Air Early A.M. SM',
            '54' => 'UPS Worldwide Express PlusSM',
            '59' => 'UPS Second Day Air A.M.',
            '65' => 'UPS Saver',
            '82' => 'UPS Today StandardSM',
            '83' => 'UPS Today Dedicated CourrierSM',
            '85' => 'UPS Today Express',
            '86' => 'UPS Today Express Saver',
        );
        if (array_key_exists($code, $c)) {
            return $c[$code];
        }
        else {
            return false;
        }
    }

    public function getUpsMethodNumber($code = '')
    {
        $sercoD = array(
            '1DM' => '14',
            '1DA' => '01',
            '1DP' => '13',
            '2DM' => '59',
            '2DA' => '02',
            '3DS' => '12',
            'GND' => '03',
            'EP' => '54',
            'XDM' => '54',
            'XPD' => '8',
            'XPR' => '7',
            'ES' => '07',
            'SV' => '65',
            'EX' => '08',
            'ST' => '11',
            'ND' => '07',
            'WXS' => '65',
        );

        $sercoD2 = array(
            '14' => '14',
            '1' => '01',
            '13' => '13',
            '59' => '59',
            '2' => '02',
            '12' => '12',
            '3' => '03',
            '54' => '54',
            '7' => '07',
            '65' => '65',
            '8' => '08',
            '11' => '11',
            '7' => '07',
        );
        $code = array_key_exists($code, $sercoD) ? $sercoD[$code] : $code;
        $code = array_key_exists($code, $sercoD2) ? $sercoD2[$code] : $code;

        return $code;
    }

    function getShippingMethods(){
        $option = array();
        $_methods = Mage::getSingleton('shipping/config')->getActiveCarriers($store);
        foreach($_methods as $_carrierCode => $_carrier){
            if($_carrierCode !=="ups" && $_carrierCode !=="dhlint" && $_carrierCode !=="usps" && $_method = $_carrier->getAllowedMethods())  {
                if(!$_title = Mage::getStoreConfig('carriers/'.$_carrierCode.'/title', $store)) {
                    $_title = $_carrierCode;
                }
                foreach($_method as $_mcode => $_m){
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[] = array('label' => "(".$_title.")  ". $_m, 'value' => $_code);
                }
            }
        }
        return $option;
    }

    function getShippingMethodsSimple(){
        $option = array();
        $_methods = Mage::getSingleton('shipping/config')->getActiveCarriers($store);
        foreach($_methods as $_carrierCode => $_carrier){
            if($_carrierCode !=="ups" && $_carrierCode !=="dhlint" && $_carrierCode !=="usps" && $_method = $_carrier->getAllowedMethods())  {
                if(!$_title = Mage::getStoreConfig('carriers/'.$_carrierCode.'/title', $store)) {
                    $_title = $_carrierCode;
                }
                foreach($_method as $_mcode => $_m){
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[$_code] =  "(".$_title.")  ". $_m;
                }
            }
        }
        return $option;
    }
}