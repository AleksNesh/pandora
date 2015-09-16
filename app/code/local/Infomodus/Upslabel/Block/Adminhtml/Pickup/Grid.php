<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Pickup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pickupGrid');
        $this->setDefaultSort('pickup_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('upslabel/pickup')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('pickup_id', array(
            'header' => Mage::helper('upslabel')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'pickup_id',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('upslabel')->__('Title'),
            'align' => 'left',
            'index' => 'PickupDateYear',
            'renderer' => 'Infomodus_Upslabel_Block_Adminhtml_Pickup_Edit_Render_Title',
        ));

        

        $this->addColumn('price', array(
            'header' => Mage::helper('upslabel')->__('Grand Total Of All Charge'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'price',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('upslabel')->__('Status'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'status',
        ));
        $this->addColumn('action',
            array(
                'header' => Mage::helper('upslabel')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('upslabel')->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('upslabel')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('upslabel')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('pickup_id');
        $this->getMassactionBlock()->setFormFieldName('pickup');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('upslabel')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('upslabel')->__('Are you sure?')
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}