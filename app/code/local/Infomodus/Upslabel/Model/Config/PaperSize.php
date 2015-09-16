<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
class Infomodus_Upslabel_Model_Config_PaperSize
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => 'A5', 'value' => 'A5'),
            array('label' => 'A4', 'value' => 'A4'),
            array('label' => 'Custom', 'value' => 'AC'),
        );
        return $c;
    }
}