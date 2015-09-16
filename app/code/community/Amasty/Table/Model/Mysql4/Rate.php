<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Table_Model_Mysql4_Rate extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amtable/rate', 'rate_id');
    }

    public function batchInsert($methodId, $data)
    {
        $err = '';
       
        $sql = '';
        for ($i=0, $n=count($data); $i<$n; ++$i){
            $sql .= ' (NULL,' . $methodId;
            foreach ($data[$i] as $v){
                $sql .= ', "'.$v.'"';
            }
            $sql .= '),';
        } 
        
        if ($sql){

            $sql = 'INSERT INTO `' . $this->getMainTable() . '` VALUES ' . substr($sql, 0, -1);
            try {
                $this->_getWriteAdapter()->raw_query($sql);
            } 
            catch (Exception $e) {
                $err = $e->getMessage();
            }
        }
            
        return $err;
    } 
    
    public function deleteBy($methodId)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), 'method_id=' . intVal($methodId)); 
    }     
       
}