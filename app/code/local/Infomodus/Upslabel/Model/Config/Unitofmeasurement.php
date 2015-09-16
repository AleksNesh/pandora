<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 07.02.12
 * Time: 10:49
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Unitofmeasurement
{
    public function toOptionArray()
    {
        $array = array(
            'IN' => 'Inches',
            'CM' => 'Centimeters',
            '00' => 'Metric Units of Measurement',
            '01' => 'English Units of Measurement',
        );
        return $array;
    }
}
