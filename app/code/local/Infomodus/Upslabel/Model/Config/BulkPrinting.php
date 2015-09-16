<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
class Infomodus_Upslabel_Model_Config_BulkPrinting
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => 'All labels', 'value' => 0),
            array('label' => 'Unprinted labels only', 'value' => 1),
        );
        return $c;
    }
}