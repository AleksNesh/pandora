<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Weight
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => 'LBS', 'value' => 'LBS'),
            array('label' => 'KGS', 'value' => 'KGS'),
        );
        return $c;
    }
}