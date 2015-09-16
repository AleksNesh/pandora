<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Upspackagecode
{
    public function toOptionArray()
    {
        /*return array(
          array('value' => 0, 'label' => 'First item'),
        );*/
        $c = array(
            array('label' => 'UPS Letter (Envelope)', 'value' => '01'),
            array('label' => 'Customer Supplied Package', 'value' => '02'),
            array('label' => 'Tube', 'value' => '03'),
            array('label' => 'PAK', 'value' => '04'),
            array('label' => 'UPS Express Box', 'value' => '21'),
            array('label' => 'UPS 25KG Box', 'value' => '24'),
            array('label' => 'UPS 10KG Box', 'value' => '25'),
            array('label' => 'Pallet', 'value' => '30'),
            array('label' => 'Small Express Box', 'value' => '2a'),
            array('label' => 'Medium Express Box', 'value' => '2b'),
            array('label' => 'Large Express Box', 'value' => '2c'),
        );
        return $c;
    }

    static public function getPackagingtypecode()
    {
        /*return array(
          array('value' => 0, 'label' => 'First item'),
        );*/
        $c = array(
            '01' => 'UPS Letter (Envelope)',
            '02' => 'Customer Supplied Package',
            '03' => 'Tube',
            '04' => 'PAK',
            '21' => 'UPS Express Box',
            '24' => 'UPS 25KG Box',
            '25' => 'UPS 10KG Box',
            '30' => 'Pallet',
            '2a' => 'Small Express Box',
            '2b' => 'Medium Express Box',
            '2c' => 'Large Express Box',
        );
        return $c;
    }
}