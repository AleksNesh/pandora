<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
class Infomodus_Upslabel_Model_Config_ListsType
{
    public function getTypes()
    {
        $array = array(
            'shipment' => 'Shipment',
            'refund' => 'Refund',
        );
        return $array;
    }
}