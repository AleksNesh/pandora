<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Table_Block_Adminhtml_Method_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('methodGrid');
      $this->setDefaultSort('pos');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amtable/method')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $hlp =  Mage::helper('amtable'); 
    $this->addColumn('method_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'method_id',
    ));

    $this->addColumn('name', array(
        'header'    => $hlp->__('Name'),
        'index'     => 'name',
    ));
    
    $this->addColumn('pos', array(
        'header'    => $hlp->__('Priority'),
        'index'     => 'pos',
    ));    
    
    $this->addColumn('is_active', array(
        'header'    => Mage::helper('salesrule')->__('Status'),
        'align'     => 'left',
        'width'     => '80px',
        'index'     => 'is_active',
        'type'      => 'options',
        'options'   => $hlp->getStatuses(),
    ));    
    
    
    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('method_id');
    $this->getMassactionBlock()->setFormFieldName('methods');
    
    $actions = array(
        'massActivate'   => 'Activate',
        'massInactivate' => 'Inactivate',
        'massDelete'     => 'Delete',
    );
    foreach ($actions as $code => $label){
        $this->getMassactionBlock()->addItem($code, array(
             'label'    => Mage::helper('amtable')->__($label),
             'url'      => $this->getUrl('*/*/' . $code),
             'confirm'  => ($code == 'massDelete' ? Mage::helper('amtable')->__('Are you sure?') : null),
        ));        
    }
    return $this; 
  }
}