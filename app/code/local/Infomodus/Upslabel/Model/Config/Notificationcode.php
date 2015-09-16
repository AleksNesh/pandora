<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Notificationcode
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => 'Return Notification or Label Creation Notification', 'value' => '2'),
            array('label' => 'QV In-transit Notification', 'value' => '5'),
            array('label' => 'QV Ship Notification', 'value' => '6'),
            array('label' => 'QV Exception Notification', 'value' => '7'),
            array('label' => 'QV Delivery Notification', 'value' => '8'),
        );
        return $c;
    }
}