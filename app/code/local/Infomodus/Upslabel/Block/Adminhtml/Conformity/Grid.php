<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Conformity_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('conformityGrid');
        $this->setDefaultSort('upslabelconformity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('upslabel/conformity')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('upslabelconformity_id', array(
            'header' => Mage::helper('upslabel')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'upslabelconformity_id',
        ));

        $this->addColumn('method_id', array(
            'header' => Mage::helper('upslabel')->__('Shipping Method from'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'method_id',
            'type'  => 'options',
            'options' => Mage::getModel('upslabel/config_upsmethod')->getShippingMethodsSimple(),
        ));

        $this->addColumn('upsmethod_id', array(
            'header' => Mage::helper('upslabel')->__('Shipping Method to'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'upsmethod_id',
            'type'  => 'options',
            'options' => Mage::getModel('upslabel/config_upsmethod')->getUpsMethods(),
        ));
/*
        $this->addColumn('international', array(
            'header' => Mage::helper('upslabel')->__('International'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'international',
            'type'  => 'options',
            'options' => array(Mage::helper('adminhtml')->__('No'), Mage::helper('adminhtml')->__('Yes')),
        ));
*/
        

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
        $this->setMassactionIdField('upslabelconformity_id');
        $this->getMassactionBlock()->setFormFieldName('conformity');

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