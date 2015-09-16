<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */

/**
 * Fraud Detection Stats table
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 */
class Directshop_FraudDetection_Model_Mysql4_Stats extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('frauddetection/stats', 'code');
    }

    public function getValue($code)
    {
        $read = $this->_getReadAdapter();
		$table = $this->getMainTable();
		$select = $read->select()->from($table);
		$select->where($read->quoteInto(" code=? ", $code));
		$value = $read->fetchRow($select);		
        return $value['value'];
    }
		
	public function setValue($code, $value)
    {
        $write = $this->_getWriteAdapter();
		$table = $this->getMainTable();			
		$write->update($table, array("value" => $value), $write->quoteInto("code=? ", $code));	
    }
}