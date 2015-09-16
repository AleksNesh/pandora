<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2009 Directshop Pty Ltd. (http://directshop.com.au)
 */

/**
 * Overriding Adminhtml sales order grid
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 */
class Directshop_FraudDetection_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
	
	/*
	 * Helper class to add a new column after an already existing column
	 * 
	 */
	public function addAfterColumn($columnId, $column, $indexColumn) 
	{
		$columns = array();
		foreach ($this->_columns as $gridColumnKey => $gridColumn) 
		{
		    $columns[$gridColumnKey] = $gridColumn;
		    if($gridColumnKey == $indexColumn) 
		    {
				$columns[$columnId] = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
					->setData($column)
					->setGrid($this);
				$columns[$columnId]->setId($columnId);         
		    }
		}
		$this->_columns = $columns;
		return $this;
	}
	
	protected function _prepareColumns()
	{
		$return = parent::_prepareColumns();
				
		$this->addAfterColumn('fraud_score', array(
		    'header'=> Mage::helper('sales')->__('Fraud<br/>Estimation (%)'),
		    'width' => '15px',
		    'type'  => 'number',
		    'index' => 'fraud_score',
		    'filter_condition_callback' => array($this, '_filterFraudScore'),
		    'align' => 'center',
		    'filter' => 'adminhtml/widget_grid_column_filter_range',
		    'renderer'  => 'Directshop_FraudDetection_Block_Adminhtml_Widget_Grid_Column_Renderer_Fraudscore',
		), 'status');

       return $return;		
    }
    
	public function setCollection($collection)
    {
    			
		// 1.6.1	
		if ($collection instanceof Mage_Sales_Model_Resource_Order_Grid_Collection){		
		 	$collection->getSelect()
    		->joinLeft(array('frauddetection_data' => $collection->getTable('frauddetection/result')), 'frauddetection_data.order_id=main_table.entity_id', 'fraud_score');
		
		}else if ($collection instanceof Mage_Core_Model_Mysql4_Collection_Abstract) // 1.4.1
    	{
    		$collection->getSelect()
    		->joinLeft(array('frauddetection_data' => $collection->getTable('frauddetection/result')), 'frauddetection_data.order_id=main_table.entity_id', 'fraud_score');
    	}
    	else if ($collection instanceof Mage_Eav_Model_Entity_Collection_Abstract)
    	{			
			$collection->joinTable('frauddetection/result', 'order_id=entity_id', array("fraud_score" => "fraud_score"), null, "left");
			
    	}	
		
    	return parent::setCollection($collection);
    }
    
	protected function _filterFraudScore($collection, $column)
    {
    
	   
	   // 1.6.1	
		if ($collection instanceof Mage_Sales_Model_Resource_Order_Grid_Collection){   
	   
	      // we have to change this so the join doesn't get reset
    		$collection->addFieldToFilter('`frauddetection_data`.fraud_score' , $column->getFilter()->getCondition());
	    // 1.4.1
    	}else if ($collection instanceof Mage_Core_Model_Mysql4_Collection_Abstract)
    	{
    		
			
			// we have to change this so the join doesn't get reset
    		$collection->addFieldToFilter('`frauddetection_data`.fraud_score' , $column->getFilter()->getCondition());
    	}
    	else
    	{
    		
		
			$collection->addFieldToFilter($column->getIndex() , $column->getFilter()->getCondition());
    	}
    }
}