<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Lists_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('upslabelGrid');
        $this->setDefaultSort('created_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('upslabel/upslabel')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('upslabel_id', array(
            'header' => Mage::helper('upslabel')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'upslabel_id',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('upslabel')->__('Title'),
            'align' => 'left',
            'width' => '250px',
            'index' => 'title',
        ));

        $this->addColumn('order_id', array(
            'header' => Mage::helper('upslabel')->__('Order ID'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'order_id',
            'frame_callback' => array($this, 'callback_order_link'),
            'filter_condition_callback' => array($this, '_filterOrderId'),
        ));

        $this->addColumn('shipment_id', array(
            'header' => Mage::helper('upslabel')->__('Shipment or Credit memos ID'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'shipment_id',
            'frame_callback' => array($this, 'callback_ship_or_credit_link'),
            'filter_condition_callback' => array($this, '_filterShipmentId'),
        ));

        $this->addColumn('labelname', array(
            'header' => Mage::helper('upslabel')->__('Print'),
            'align' => 'left',
            'width' => '120px',
            'index' => 'labelname',
            'frame_callback' => array($this, 'callback_print'),
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('upslabel')->__('Type'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'type',
            'type' => 'options',
            'options' => Mage::getModel('upslabel/config_listsType')->getTypes(),
        ));

        $this->addColumn('statustext', array(
            'header' => Mage::helper('upslabel')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'statustext',
            'type' => 'options',
            'options' => Mage::getModel('upslabel/config_statuslabels')->getListsStatus(),
            'frame_callback' => array($this, 'callback_statustext'),
            'filter_condition_callback' => array($this, '_listsUpsStatusFilter'),
        ));

        $this->addColumn('rva_printed', array(
            'header' => Mage::helper('upslabel')->__('Print status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'rva_printed',
            'type' => 'options',
            'options' => array(Mage::helper('upslabel')->__("Unprinted"), Mage::helper('upslabel')->__("Printed")),
        ));

        $this->addColumn('created_time', array(
            'header' => Mage::helper('upslabel')->__('Created date'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'created_time',
        ));


        $this->addColumn('action',
            array(
                'header' => Mage::helper('upslabel')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('upslabel')->__('Delete'),
                        'url' => array('base' => '*/*/delete'),
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
        $this->setMassactionIdField('upslabel_id');
        $this->getMassactionBlock()->setFormFieldName('upslabel');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('upslabel')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('upslabel')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('upslabel_pdflabels', array(
            'label' => Mage::helper('sales')->__('Print Pdf Labels'),
            'url' => Mage::app()->getStore()->getUrl('upslabel/adminhtml_pdflabels', array('type' => 'lists')),
        ));
        return $this;
    }

    public function callback_print($value, $row, $column, $isExport)
    {
        if ($row->getStatus() == 1) {
            return;
        }
        $HVR = false;
        $Html = '';
        $Pdf = '';
        $Image = '';
        if (file_exists(Mage::getBaseDir('media') . DS . 'upslabel' . DS . 'label' . DS . "HVR" . $row->getTrackingnumber() . ".html")) {
            $HVR = ' / <a href="' . Mage::getBaseUrl('media') . 'upslabel/label/HVR' . $row->getTrackingnumber() . '.html" target="_blank">HVR</a>';
        }
        if ($row->getTypePrint() == "GIF") {
            $Pdf = '<a href="' . $this->getUrl('upslabel/adminhtml_pdflabels/onepdf/order_id/' . $row->getOrderId() . '/shipment_id/' . $row->getShipmentId() . '/type/' . $row->getType()) . '" target="_blank">PDF</a>';
            $Image = '<a href="' . $this->getUrl('upslabel/adminhtml_upslabel/print/imname/' . 'label' . $row->getTrackingnumber() . '.gif') . '" target="_blank">Image</a>';
        }
        else {
            echo '<a href="' . $this->getUrl('upslabel/adminhtml_upslabel/autoprint/order_id/'.$row->getOrderId().'/shipment_id/'.$row->getShipmentId.'/type/'.$row->getType) . '" target="_blank">' . Mage::helper('adminhtml')->__('Print Label') . '</a>';
        }
        if (file_exists(Mage::getBaseDir('media') . '/upslabel/label/' . $row->getTrackingnumber() . '.html')) {
            $Html = ' / <a href="' . Mage::getBaseUrl('media') . 'upslabel/label/' . $row->getTrackingnumber() . '.html" target="_blank">Html</a>';
        }
        return $Pdf .  $Html . ' / ' . $Image . $HVR;
    }

    public function callback_statustext($value, $row, $column, $isExport)
    {
        return $row->getStatustext();
    }

    public function callback_order_link($value, $row, $column, $isExport)
    {
        $order = Mage::getModel("sales/order")->load($row->getOrderId());
        return '<a href="' . $this->getUrl('adminhtml/sales_order/view/order_id/' . $row->getOrderId()) . '">' . $order->getIncrementId() . '</a>';
    }

    public function callback_ship_or_credit_link($value, $row, $column, $isExport)
    {
        $path = 'adminhtml/sales_order_shipment/view/shipment_id/';
        $shipment = Mage::getModel("sales/order_shipment")->load($row->getShipmentId());
        if ($row->getType() == 'refund') {
            $path = 'adminhtml/sales_order_creditmemo/view/creditmemo_id/';
            $shipment = Mage::getModel("sales/order_creditmemo")->load($row->getShipmentId());
        }

        return '<a href="' . $this->getUrl($path . $row->getShipmentId()) . '">' . $shipment->getIncrementId() . '</a>';
    }

    public function _listsUpsStatusFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $status = 0;
        switch ($value) {
            case "success":
                $status = 0;
                break;
            case "error":
                $status = 1;
                break;
        }
        $collection->addFieldToFilter('status', $status);
        return $this;
    }

    public function _filterOrderId($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $order = Mage::getModel("sales/order")->loadByIncrementId($value);
        if ($order == FALSE) {
            $order_id = $value;
        } else {
            $order_id = $order->getId();
        }
        $collection->addFieldToFilter('order_id', $order_id);
        return $this;
    }

    public function _filterShipmentId($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $shipment = Mage::getModel("sales/order_shipment")->loadByIncrementId($value);
        if ($shipment == FALSE) {
        } else {
            $shipment_id = $shipment->getId();
        }

        $creditmemo = Mage::getModel("sales/order_creditmemo")->load($value, "increment_id");
        if ($creditmemo == FALSE) {
            $creditmemo_id = $value;
        } else {
            $creditmemo_id = $creditmemo->getId();
        }
        $collection->addFieldToFilter('shipment_id', array(array('eq' => $shipment_id), array('eq' => $creditmemo_id)));
        return $this;
    }
}