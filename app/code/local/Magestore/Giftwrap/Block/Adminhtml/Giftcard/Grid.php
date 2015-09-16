<?php

class Magestore_Giftwrap_Block_Adminhtml_Giftcard_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected function _construct() {
        $this->setEmptyText(Mage::helper('giftwrap')->__('No Gift Cards found'));
    }

    protected function _prepareCollection() {
        $store_id = $this->getRequest()->getParam('store', 0);
        $collection = Mage::getModel('giftwrap/giftcard')->getCollection();
        $collection->addFieldToFilter('store_id', $store_id);
        $this->setCollection($collection);        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('giftcard_id', array(
            'header' => Mage::helper('giftwrap')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'giftcard_id',
        ));

        $this->addColumn('image', array(
            'header' => Mage::helper('giftwrap')->__('Image'),
            'index' => 'image',
            'filter' => false,
            'align' => 'center',
            'width' => '100px',
            'renderer' => 'giftwrap/adminhtml_giftcard_renderer_image',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('giftwrap')->__('Card Name'),
            'align' => 'left',
            'width' => '300px',
            'index' => 'name',
        ));

        $store = $this->_getStore();
        $this->addColumn('price', array(
            'header' => Mage::helper('giftwrap')->__('Price'),
            'width' => '100px',
            'type' => 'price',
            'index' => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
        ));
        
        $this->addColumn('character', array(
            'header' => Mage::helper('giftwrap')->__('Max Message Length'),
            'align' => 'right',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterCharacter'),
            'index' => 'character',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('giftwrap')->__('Status'),
            'index' => 'status',
            'width' => '80',
            'type' => 'options',
            'options' => array(
                2 => Mage::helper('giftwrap')->__('Disabled'),
                1 => Mage::helper('giftwrap')->__('Enabled')
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('giftwrap')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('giftwrap')->__('Edit'),
                    'url' => array('base' => '*/*/edit/store/' . $this->getRequest()->getParam('store', 0),),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('giftcard_id');
        $this->getMassactionBlock()->setFormFieldName('giftcard');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('giftwrap')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('giftwrap')->__('Are you sure?')
        ));
        //loki add mass status
        $statuses = Mage::getSingleton('giftwrap/Status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('giftwrap')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('giftwrap')->__('Status'),
                    'values' => $statuses
                ))
        ));
        //end loki
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'store' => $this->getRequest()->getParam('store', 0)));
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    
     protected function _filterCharacter($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $collection->getSelect()->where('`character` LIKE "'.$filter.'"');
    }
}
