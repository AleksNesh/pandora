<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */

class Directshop_FraudDetection_Model_Mysql4_Result extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('frauddetection/result', 'entity_id');
    }
}