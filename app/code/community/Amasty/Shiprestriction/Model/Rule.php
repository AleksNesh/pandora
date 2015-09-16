<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Shiprestriction_Model_Rule extends Mage_Rule_Model_Rule
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('amshiprestriction/rule');
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('amshiprestriction/rule_condition_combine');
    }
    
    public function restrict($rate)
    {
        if (false !== strpos($this->getCarriers(), ',' . $rate->getCarrier(). ','))
            return true;
        
        $m = $this->getMethods();    
        $m = str_replace("\r\n", "\n", $m);
        $m = str_replace("\r", "\n", $m);
        $m = trim($m);
        if (!$m){
            return false;
        }
        
        $m = array_unique(explode("\n", $m));
        foreach ($m as $pattern){
            $pattern = '/' . trim($pattern) . '/i';
            if (preg_match($pattern, $rate->getMethodTitle())){
                return true;
            }
        }
        return false;
    }
    
    public function massChangeStatus($ids, $status)
    {
        return $this->getResource()->massChangeStatus($ids, $status);
    }
    
    protected function _afterSave()
    {
        //Saving attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
        if (count($ruleProductAttributes)) {
            $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
        } 
        
        return parent::_afterSave(); 
    } 
    
    /**
     * Return all product attributes used on serialized action or condition
     *
     * @param string $serializedString
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        $result = array();
        
        $pattern = '~s:32:"salesrule/rule_condition_product";s:9:"attribute";s:\d+:"(.*?)"~s';
        $matches = array();
        if (preg_match_all($pattern, $serializedString, $matches)){
            foreach ($matches[1] as $attributeCode) {
                $result[] = $attributeCode;
            }
        }
        
        return $result;
    }    
     
}