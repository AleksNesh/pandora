<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('accountGrid');
        $this->setDefaultSort('account_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('upslabel/account')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('account_id', array(
            'header' => Mage::helper('upslabel')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'account_id',
        ));

        $this->addColumn('companyname', array(
            'header' => Mage::helper('upslabel')->__('Company name'),
            'align' => 'left',
            'index' => 'companyname',
        ));

        $this->addColumn('accountnumber', array(
            'header' => Mage::helper('upslabel')->__('UPS Acct #'),
            'align' => 'left',
            'index' => 'accountnumber',
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
        $this->setMassactionIdField('account_id');
        $this->getMassactionBlock()->setFormFieldName('account');

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