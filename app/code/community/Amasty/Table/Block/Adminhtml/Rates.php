<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Table_Block_Adminhtml_Rates extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amtableRates');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('id');
        
        $collection = Mage::getResourceModel('amtable/rate_collection')
            ->addFieldToFilter('method_id', $id);
   
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('country', array(
            'header'    => Mage::helper('amtable')->__('Country'),
            'index'     => 'country',
            'type'      => 'options', 
            'options'   => Mage::helper('amtable')->getCountries(),            
        ));

        $this->addColumn('state', array(
            'header'    => Mage::helper('amtable')->__('State'),
            'index'     => 'state',
            'type'      => 'options', 
            'options'   => Mage::helper('amtable')->getStates(),
        ));

        $this->addColumn('city', array(
            'header'    => Mage::helper('amtable')->__('City'),
            'index'     => 'city',
        ));
        
        $this->addColumn('zip_from', array(
            'header'    => Mage::helper('amtable')->__('Zip From'),
            'index'     => 'zip_from',
        ));

        $this->addColumn('zip_to', array(
            'header'    => Mage::helper('amtable')->__('Zip To'),
            'index'     => 'zip_to',
        ));

        $this->addColumn('price_from', array(
            'header'    => Mage::helper('amtable')->__('Price From'),
            'index'     => 'price_from',
        ));
        
        $this->addColumn('price_to', array(
            'header'    => Mage::helper('amtable')->__('Price To'),
            'index'     => 'price_to',
        ));
        
        $this->addColumn('weight_from', array(
            'header'    => Mage::helper('amtable')->__('Weight From'),
            'index'     => 'weight_from',
        ));
        
        $this->addColumn('weight_to', array(
            'header'    => Mage::helper('amtable')->__('Weight To'),
            'index'     => 'weight_to',
        ));         
        
        $this->addColumn('qty_from', array(
            'header'    => Mage::helper('amtable')->__('Qty From'),
            'index'     => 'qty_from',
        ));
        
        $this->addColumn('qty_to', array(
            'header'    => Mage::helper('amtable')->__('Qty To'),
            'index'     => 'qty_to',
        ));
        
        $this->addColumn('shipping_type', array(
            'header'    => Mage::helper('amtable')->__('Shipping Type'),
            'index'     => 'shipping_type',
            'type'      => 'options', 
            'options'   => Mage::helper('amtable')->getTypes(),            
        ));
                
       
        
        $this->addColumn('cost_base', array(
            'header'    => Mage::helper('amtable')->__('Rate'),
            'index'     => 'cost_base',
        ));

        $this->addColumn('cost_percent', array(
            'header'    => Mage::helper('amtable')->__('PPP'),
            'index'     => 'cost_percent',
        ));

        $this->addColumn('cost_product', array(
            'header'    => Mage::helper('amtable')->__('FRPP'),
            'index'     => 'cost_product',
        ));
        
        $this->addColumn('cost_weight', array(
            'header'    => Mage::helper('amtable')->__('FRPUW'),
            'index'     => 'cost_weight',
        ));        
        
        $this->addColumn('action', array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Delete'),
                        'url'     => array('base'=>'*/*/delete'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        )); 
        
        $this->addExportType('*/*/exportCsv', Mage::helper('amtable')->__('CSV'));
                
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_rate/edit', array('id' => $row->getId())); 
    }
      
}