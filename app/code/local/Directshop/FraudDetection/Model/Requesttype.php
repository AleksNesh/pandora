<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */


/**
 * FraudDetection Request Type Source
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 */
class Directshop_Frauddetection_Model_Requesttype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'standard', 'label' => Mage::helper('frauddetection')->__('Standard')),
            array('value' => 'premium', 'label' => Mage::helper('frauddetection')->__('Premium'))
        );
    }
}