<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */


if (Mage::getConfig()->getModuleConfig('Mage_Epay')->is('active', true)) {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Mage_Epay_Block_Adminhtml_Order_Grid {}
} else if (Mage::getConfig()->getModuleConfig('LucidPath_SalesRep')->is('active', true)) {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Mage_Adminhtml_Block_Sales_Order_Grid {
        public function setCollection($collection) {
            $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable('salesrep/salesrep')), 'salesrep.order_id=entity_id');
            return parent::setCollection($collection);
        }
        protected function _prepareSalesRepColumns() {
            if (Mage::getStoreConfig('salesrep/order_grid/commission_earner')) {
                $this->addColumn('admin_name', array(
                    'header' => Mage::helper('sales')->__('Comm. Earner'),
                    'index' => 'admin_name',
                    'align' => 'center',
                    'width' => '10px',
                    'renderer' => 'LucidPath_SalesRep_Block_Adminhtml_Order_Grid_Renderer_Earner',
                ));
            }

            if (Mage::getStoreConfig('salesrep/order_grid/commission_amount')) {
                $this->addColumn('commission_earned', array(
                    'header' => Mage::helper('sales')->__('Comm. Amount'),
                    'index' => 'commission_earned',
                    'align' => 'center',
                    'width' => '10px',
                    'renderer' => 'LucidPath_SalesRep_Block_Adminhtml_Order_Grid_Renderer_Amount',
                ));
            }

            if (Mage::getStoreConfig('salesrep/order_grid/commission_payment_status')) {
                $this->addColumn('commission_status', array(
                    'header' => Mage::helper('sales')->__('Comm. Status'),
                    'index' => 'commission_status',
                    'align' => 'center',
                    'width' => '10px',
                    'renderer' => 'LucidPath_SalesRep_Block_Adminhtml_Order_Grid_Renderer_PaymentStatus',
                ));
            }
        }        
    }
} else if (Mage::getConfig()->getModuleConfig('Mage_CanPostExport')->is('active', true)) {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Mage_CanPostExport_Block_Sales_Order_Grid {}
} else if ((string)Mage::getConfig()->getModuleConfig('Innoexts_Warehouse')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Innoexts_Warehouse_Block_Adminhtml_Sales_Order_Grid {
        protected function _prepareInnoextsWarehouseColumns() {
            $helper = $this->getWarehouseHelper();
            $stockOptions = $this->getStockOptions();
            $this->addColumnAfter('stocks', array(
                'header'        => $helper->__('Warehouses'), 
                'sortable'      => false, 
                'index'         => 'stocks', 
                'type'          => 'options', 
                'options'       => $stockOptions, 
            ), 'status');
        }
        protected function _getCollectionClass() {
            return 'sales/order_grid_collection';
        }
    }
} else if ((string)Mage::getConfig()->getModuleConfig('Extended_Ccsave')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Extended_Ccsave_Block_Adminhtml_Sales_Order_Grid {}
} else if ((string)Mage::getConfig()->getModuleConfig('Directshop_FraudDetection')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Directshop_FraudDetection_Block_Adminhtml_Sales_Order_Grid {
   	public function setCollection($collection) {
            $collection->getSelect()->joinLeft(array('frauddetection_data' => $collection->getTable('frauddetection/result')), 'frauddetection_data.order_id=main_table.entity_id', 'fraud_score');
            return parent::setCollection($collection);
        }
        protected function _prepareFraudDetectionColumns() {
            $this->addColumn('fraud_score', array(
                'header'=> Mage::helper('sales')->__('Fraud<br/>Score'),
                'width' => '15px',
                'type'  => 'number',
                'index' => 'fraud_score',
                'filter_condition_callback' => array($this, '_filterFraudScore'),
                'align' => 'center',
                'filter' => 'adminhtml/widget_grid_column_filter_range',
                'renderer'  => 'Directshop_FraudDetection_Block_Adminhtml_Widget_Grid_Column_Renderer_Fraudscore',
            ), 'status');
        }        
    }
} else if ((string)Mage::getConfig()->getModuleConfig('AW_Ordertags')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends AW_Ordertags_Block_Adminhtml_Sales_Order_Grid {
        protected function _prepareAWOrdertagsColumns() {
            if (!$this->exportFlag) {
                $this->addColumn('tag', array(
                    'header' => Mage::helper('ordertags')->__('Order Tags'),
                    'index' => 'tag',
                    'type' => 'options',
                    'width' => '70px',
                    'options' => $this->_returnOptionsList(),
                    'renderer' => 'ordertags/adminhtml_sales_order_grid_column_renderer_options',
                    'filter_condition_callback' => array($this, 'filter_tag_callback'),
                    'sortable' => false,
                ));
            }
            $this->exportFlag = false;
        }
    }
} else if ((string)Mage::getConfig()->getModuleConfig('AW_Deliverydate')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Mage_Adminhtml_Block_Sales_Order_Grid {
        protected function _prepareAWDeliverydateColumns() {
            $this->addColumn('aw_deliverydate_date', array(
                'header' => Mage::helper('deliverydate')->__('Delivery Date'),
                'index' => 'aw_deliverydate_date',
                'type' => 'date',
                'renderer' => 'deliverydate/adminhtml_sales_order_grid_renderer_deliverydate',
                'width' => '100px',
                'sortable' => $this->_sortableForVersion(),
                'filter_condition_callback' => array($this, 'filterDeliveryDate'),
            ));
        }
        
        protected function _sortableForVersion() {
            $rez = true;
            if (preg_match('/^1.4.0/', Mage::getVersion()) || preg_match('/^1.3/', Mage::getVersion())) {
                $rez = false;
            }
            return $rez;
        }
        
        protected function filterDeliveryDate($collection, $column) {
            $val = $column->getFilter()->getValue();

            if (!$val) {
                return $this;
            }

            $dateFrom = '0000-00-00 00:00:00';
            if (isset($val['from'])) {
                $dateFrom = $this->_getMysqlFormat($val['orig_from'], $val['locale']);
            }
            $dateTo = '9999-03-23 00:00:00';
            if (isset($val['to'])) {
                $dateTo = $this->_getMysqlFormat($val['orig_to'], $val['locale']);
            }

            $collection->getSelect()
                    ->joinleft(array('deliveryTable' => Mage::getSingleton('core/resource')->getTableName("deliverydate/delivery")), $this->_getSalesOrdersTableSyn() . '.entity_id = deliveryTable.order_id', array())
                    ->where("deliveryTable.delivery_date >= '{$dateFrom}' AND deliveryTable.delivery_date <= '{$dateTo}'");
        }
        private function _getMysqlFormat($date, $locale) {
            $date = new Zend_Date($date, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), $locale);
            $date->setTimezone('UTC');
            return $date->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        }
        
        protected function _getSalesOrdersTableSyn() {
            $syn = 'main_table';
            if (preg_match('/^1.4.0/', Mage::getVersion()) || preg_match('/^1.3/', Mage::getVersion())) {
                $syn = 'e';
            } elseif (preg_match('/^1.4.1/', Mage::getVersion())) {
                $syn = 'main_table';
            }
            return $syn;
        }
        
    }
} else if ((string)Mage::getConfig()->getModuleConfig('Amasty_Orderattr')->active=='true' && version_compare(Mage::getConfig()->getModuleConfig('Amasty_Orderattr')->version, '3.1.0', '<')) {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Mage_Adminhtml_Block_Sales_Order_Grid {
        protected function _prepareAmastyOrderattrColumns() {
            /**
             * Loading attributes collection
             */
            $collection = Mage::getModel('eav/entity_attribute')->getCollection();
            $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
            $collection->addFieldToFilter('show_on_grid', 1);
            $collection->getSelect()->order('sorting_order');
            $attributes = $collection->load();
            if ($attributes->getSize()) {
                foreach ($attributes as $attribute) {
                    if ($inputType = $attribute->getFrontend()->getInputType()) {
                        switch ($inputType) {
                            case 'date':
                                if ('time' == $attribute->getNote()) {
                                    $this->addColumn($attribute->getAttributeCode(), array(
                                        'header' => __($attribute->getFrontend()->getLabel()),
                                        'type' => 'datetime',
                                        'align' => 'center',
                                        'index' => $attribute->getAttributeCode(),
                                        'gmtoffset' => false,
                                        'renderer' => 'amorderattr/adminhtml_order_grid_renderer_datetime',
                                    ));
                                } else {
                                    $this->addColumn($attribute->getAttributeCode(), array(
                                        'header' => __($attribute->getFrontend()->getLabel()),
                                        'type' => 'date',
                                        'align' => 'center',
                                        'index' => $attribute->getAttributeCode(),
                                        'gmtoffset' => false,
                                    ));
                                }

                                break;
                            case 'text':
                            case 'textarea':
                                $this->addColumn($attribute->getAttributeCode(), array(
                                    'header' => __($attribute->getFrontend()->getLabel()),
                                    'index' => $attribute->getAttributeCode(),
                                    'filter' => 'adminhtml/widget_grid_column_filter_text',
                                    'sortable' => true,
                                ));
                                break;
                            case 'select':
                                $options = array();
                                foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
                                    $options[$option['value']] = $option['label'];
                                }
                                $this->addColumn($attribute->getAttributeCode(), array(
                                    'header' => __($attribute->getFrontend()->getLabel()),
                                    'index' => $attribute->getAttributeCode(),
                                    'type' => 'options',
                                    'options' => $options,
                                ));
                                break;
                            case 'multiselect':
                                $options = array();
                                foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
                                    $options[$option['value']] = $option['label'];
                                }
                                $this->addColumn($attribute->getAttributeCode(), array(
                                    'header' => __($attribute->getFrontend()->getLabel()),
                                    'index' => $attribute->getAttributeCode(),
                                    'type' => 'options',
                                    'options' => $options,
                                ));
                                break;
                            case 'checkboxes':
                                $options = array();
                                foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
                                    $options[$option['value']] = $option['label'];
                                }
                                $this->addColumn($attribute->getAttributeCode(), array(
                                    'header' => __($attribute->getFrontend()->getLabel()),
                                    'type' => 'options',
                                    'options' => $options,
                                    'index' => $attribute->getAttributeCode(),
                                    'filter' => 'amorderattr/adminhtml_order_grid_filter_checkboxes',
                                    'renderer' => 'amorderattr/adminhtml_order_grid_renderer_checkboxes',
                                ));
                                break;
                        }
                    }
                }
            }
        }
    }
} else if ((string)Mage::getConfig()->getModuleConfig('Amasty_Orderattach')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Mage_Adminhtml_Block_Sales_Order_Grid {
        protected function _prepareAmastyOrderattachColumns() {
            $attachments = Mage::getModel('amorderattach/field')->getCollection();
            $attachments->addFieldToFilter('show_on_grid', 1);
            $attachments->load();
            if ($attachments->getSize()) {
                foreach ($attachments as $attachment) {
                    switch ($attachment->getType()) {
                        case 'date':
                            $this->addColumn($attachment->getFieldname(), array(
                                'header' => $this->__($attachment->getLabel()),
                                'type' => 'date',
                                'align' => 'center',
                                'index' => $attachment->getFieldname(),
                                'gmtoffset' => false,
                            ));
                            break;
                        case 'text':
                        case 'string':
                            $this->addColumn($attachment->getFieldname(), array(
                                'header' => $this->__($attachment->getLabel()),
                                'index' => $attachment->getFieldname(),
                                'filter' => 'adminhtml/widget_grid_column_filter_text',
                                'sortable' => true,
                            ));
                            break;
                        case 'select':
                            $selectOptions = array();
                            $options = explode(',', $attachment->getOptions());
                            $options = array_map('trim', $options);
                            if ($options) {
                                foreach ($options as $option) {
                                    $selectOptions[$option] = $option;
                                }
                            }
                            $this->addColumn($attachment->getFieldname(), array(
                                'header' => $this->__($attachment->getLabel()),
                                'index' => $attachment->getFieldname(),
                                'type' => 'options',
                                'options' => $selectOptions,
                            ));
                            break;
                    }
                }
            }            
        }
    }
} else if ((string)Mage::getConfig()->getModuleConfig('Amasty_Flags')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Mage_Adminhtml_Block_Sales_Order_Grid {
        
        // for Amasty_Flags v3.0
        protected function _prepareAmastyFlagsColumns() {
            $columnCollection = Mage::getModel('amflags/column')->getCollection();
            $columnCollection->getSelect()->order('pos DESC');
            $flagCollection = Mage::getModel('amflags/flag')->getCollection();

            if ($columnCollection->getSize() > 0) {
                foreach ($columnCollection as $column) {
                    if (($column->getApplyFlag()) && ($flagCollection->getSize() > 0)) {
                        $flagFilterOptions = array();
                        $columnFlags = array();
                        $columnFlags = explode(',', $column->getApplyFlag());

                        foreach ($flagCollection as $flag) {
                            if (in_array($flag->getEntityId(), $columnFlags)) {
                                $flagFilterOptions[$flag->getPriority()] = $flag->getAlias();
                            }
                        }

                        $flagColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
                                ->setData(array(
                                    'header' => Mage::helper('amflags')->__($column->getAlias()),
                                    'index' => 'priority' . $column->getEntityId(),
                                    'filter_index' => 'f' . $column->getEntityId() . '.priority',
                                    'width' => '80px',
                                    'align' => 'center',
                                    'renderer' => 'amflags/adminhtml_renderer_flag',
                                    'type' => 'options',
                                    'options' => $flagFilterOptions,
                                        )
                                )
                                ->setGrid($this)
                                ->setId('flag_column_id' . $column->getEntityId());

                        // adding flag column to the beginning of the columns array
                        $flagColumnArray = array('flag_column_id' . $column->getEntityId() => $flagColumn);
                        $this->_columns = $flagColumnArray + $this->_columns;
                    }
                }
                //$this->setDefaultSort('created_at');
                //$this->setDefaultDir('DESC');
                //$this->sortColumnsByOrder();
            }
        }
        
        protected function _prepareCollection() {
            if (method_exists($this, '_getCollectionClass')) {
                // for 1.4.1.+
                $collection = Mage::getResourceModel('mageworx_orderspro/order_grid_collection');
                $mainAlias = 'main_table';
            } else {
                // for 1.4.0.x
                $collection = Mage::getResourceModel('mageworx_orderspro/order_grid_collection')
                        ->addAttributeToSelect('*')
                        ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                        ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                        ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                        ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                        ->addExpressionAttributeToSelect('billing_name', 'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})', array('billing_firstname', 'billing_lastname'))
                        ->addExpressionAttributeToSelect('shipping_name', 'CONCAT({{shipping_firstname}},  IFNULL(CONCAT(\' \', {{shipping_lastname}}), \'\'))', array('shipping_firstname', 'shipping_lastname'));
                $mainAlias = 'e';
            }
            // joining order flag priority by columns to the collection
            $columnCollection = Mage::getModel('amflags/column')->getCollection();
            if ($columnCollection->getSize() > 0) {
                foreach ($columnCollection as $column) {
                    if ($column->getApplyFlag()) {
                        $collection->getSelect(
                        )->joinLeft(
                                array('f2o' . $column->getEntityId() => Mage::getModel('amflags/order_flag')->getResource()->getMainTable()), 'f2o' . $column->getEntityId() . '.order_id = ' . $mainAlias . '.entity_id ' .
                                'AND f2o' . $column->getEntityId() . '.column_id = ' . $column->getEntityId(), array()
                        )->joinLeft(
                                array('f' . $column->getEntityId() => Mage::getModel('amflags/flag')->getResource()->getMainTable()), 'f' . $column->getEntityId() . '.entity_id = f2o' . $column->getEntityId() . '.flag_id', array('priority' . $column->getEntityId() => 'f' . $column->getEntityId() . '.priority')
                        );
                    }
                }
            }
            $this->setCollection($collection);
            return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        }        
        
        protected function _toHtml() {
            $html = parent::_toHtml();
            $html .= $this->getLayout()->createBlock('amflags/rewrite_adminhtml_order_grid_modifyJs')->toHtml();
            return $html;
        }
    }
} else if ((string)Mage::getConfig()->getModuleConfig('Amasty_Flags')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Amasty_Flags_Block_Rewrite_Adminhtml_Order_Grid {
        // for Amasty_Flags v1.0        
        protected function _prepareAmastyFlagsColumns() {
            $flagCollection = Mage::getModel('amflags/flag')->getCollection();
            $flagFilterOptions = array();
            if ($flagCollection->getSize() > 0) {
                foreach ($flagCollection as $flag) {
                    $flagFilterOptions[$flag->getPriority()] = $flag->getAlias();
                }
            }
            $flagColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
                    ->setData(array(
                        'header' => Mage::helper('amflags')->__('Flag'),
                        'index' => 'priority',
                        'width' => '80px',
                        'align' => 'center',
                        'renderer' => 'amflags/adminhtml_renderer_flag',
                        'type' => 'options',
                        'options' => $flagFilterOptions,
                    ))
                    ->setGrid($this)
                    ->setId('flag_id');
            // adding flag column to the beginning of the columns array
            $flagColumnArray = array('flag_id' => $flagColumn);
            $this->_columns = $flagColumnArray + $this->_columns;
        }
    }
} else if ((string)Mage::getConfig()->getModuleConfig('Amasty_Email')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Amasty_Email_Block_Adminhtml_Sales_Order_Grid {}
} else if ((string)Mage::getConfig()->getModuleConfig('AdjustWare_Deliverydate')->active=='true'){
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends AdjustWare_Deliverydate_Block_Rewrite_AdminhtmlSalesOrderGrid {
        protected function _prepareDeliveryColumn() {
             $this->addColumn('delivery_date', array(
                'header' => Mage::helper('adjdeliverydate')->__('Delivery Date'),
                //'type'   => 'text',
                'index' =>'delivery_date',
                'renderer' => 'adminhtml/widget_grid_column_renderer_date',
                'filter' => 'adjdeliverydate/adminhtml_filter_delivery', //AdjustWare_Deliverydate_Block_Adminhtml_Filter_Delivery
                'width'  => '100px', 
            ));
        }
    }
} else {
    class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Abstract extends Mage_Adminhtml_Block_Sales_Order_Grid {}
}