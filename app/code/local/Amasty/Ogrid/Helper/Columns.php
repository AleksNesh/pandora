<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Helper_Columns extends Mage_Core_Helper_Abstract
{
    
    protected static $_TYPE_CONFIGURABLE = 'configurable';
    protected static $_TYPE_DEFAULT = 'default';
    protected static $_TYPE_ATTRIBUTE= 'attribute';
    protected static $_TYPE_STATIC = 'static';

    protected $_orderTableAlias = 'am_order_item';
    protected $_extrOrderColumnPrefix = 'extra_col_';
    protected $_columns = NULL;
    protected $_staticColumns = NULL;
    
    protected $_configurableFields = NULL;
    protected $_defaultField = NULL;
    
    protected static $_collectionModified  = FALSE;    
    
    protected function _getColumns(){
        if (!$this->_columns){
            $sorted = array();
            $columns = Mage::helper('amogrid')->getColumns();
            
            foreach($columns as $column){
                if (isset($column['available']) && $column['available'] == 1){
                    
                    $position = $column['position'];
                    
                    while(isset($sorted[$position])){
                        $position++;
                    }
                    $sorted[$position] = $column;

                }
            }
            
            ksort($sorted);
            
            foreach($sorted as $column)
                $this->_columns[$column['key']] = $column;
        }
        return $this->_columns;
    }
    
    protected function _getStaticColumns(){
        if (!$this->_staticColumns){
            $columns = Mage::helper('amogrid')->getColumns();
            
            foreach($columns as $column){
                if ($column['type'] == 'static' && !empty($column['relation'])){
                    $this->_staticColumns[$column['key']] = $column;
                }
            }
        }
        return $this->_staticColumns;
    }
    
    protected function _getColumn($key, $def = NULL){
        $columns = $this->_getColumns();
        return isset($columns[$key]) ? $columns[$key] : $def;
    }
    
    protected function _isColumnAvailable($key){
        $ret = FALSE;
        $column = $this->_getColumn($key);
        if ($column){
            $ret = TRUE;
        }
        return $ret;
    }
    
    function prepareOrderCollectionJoins(&$collection, $orderItemsColumns = array()){
        if (self::$_collectionModified)
            return ;
        self::$_collectionModified = TRUE;
        
        $showShipping = $this->_isColumnAvailable('am_shipping_description');//Mage::getStoreConfig('amogrid/general/shipping');
        $showPayment = $this->_isColumnAvailable('am_method');//Mage::getStoreConfig('amogrid/general/payment');
        $showCoupon = $this->_isColumnAvailable('am_coupon_code');//Mage::getStoreConfig('amogrid/general/coupon');
        $showCustomerEmail = $this->_isColumnAvailable('am_customer_email');//Mage::getStoreConfig('amogrid/general/customer_email');
        
        $showShippingAddress = $this->_isColumnAvailable('am_shipping_address') ||
                $this->_isColumnAvailable('am_shipping_country_id') || 
                $this->_isColumnAvailable('am_shipping_region') ||
                $this->_isColumnAvailable('am_shipping_postcode') ||
                $this->_isColumnAvailable('am_shipping_street') ||
                $this->_isColumnAvailable('am_shipping_city')
                
                ;
        $showBillingAddress = $this->_isColumnAvailable('am_billing_address') ||
                $this->_isColumnAvailable('am_billing_country_id') || 
                $this->_isColumnAvailable('am_billing_region') ||
                $this->_isColumnAvailable('am_billing_postcode') ||
                $this->_isColumnAvailable('am_billing_street') ||
                $this->_isColumnAvailable('am_billing_city');
        
        
        $excludeStatuses = Mage::getStoreConfig('amogrid/general/exclude');
        $excludeStatuses = !empty($excludeStatuses) ? explode(',', $excludeStatuses) : array();

        $collection->getSelect()->join(
            array(
                'order_item' => $collection->getTable('sales/order_item')
            ),
            'main_table.entity_id = order_item.order_id', 
            array()
        );
        
        if ($showCoupon || $showShipping || $showCustomerEmail){
            $collection->getSelect()->join(
                array(
                    'order' => $collection->getTable('sales/order')
                ),
                'main_table.entity_id = order.entity_id', 
                array(
                    'order.coupon_code as am_coupon_code', 
                    'order.shipping_description as am_shipping_description',
                    'order.customer_email as am_customer_email')
            );
        }
        
        if ($showPayment){
            $collection->getSelect()->joinLeft(
                array(
                    'order_payment' => $collection->getTable('sales/order_payment')
                ),
                'main_table.entity_id = order_payment.parent_id', 
                array('order_payment.method as am_method')
            );
        }
        
        if ($showShippingAddress){
            $collection->getSelect()->joinLeft(
                array(
                    'shipping_order_address' => $collection->getTable('sales/order_address')
                ),
                'main_table.entity_id = shipping_order_address.parent_id and shipping_order_address.address_type = \'shipping\'', 
                array(
                    'shipping_order_address.country_id as am_shipping_country_id',
                    'shipping_order_address.region as am_shipping_region',
                    'shipping_order_address.postcode as am_shipping_postcode',
                    'shipping_order_address.street as am_shipping_street',
                    'shipping_order_address.city as am_shipping_city',
                )
            );
        }
        
        if ($showBillingAddress){
            $collection->getSelect()->joinLeft(
                array(
                    'billing_order_address' => $collection->getTable('sales/order_address')
                ),
                'main_table.entity_id = billing_order_address.parent_id and billing_order_address.address_type = \'billing\'', 
                array(
                    'billing_order_address.country_id as am_billing_country_id',
                    'billing_order_address.region as am_billing_region',
                    'billing_order_address.postcode as am_billing_postcode',
                    'billing_order_address.street as am_billing_street',
                    'billing_order_address.city as am_billing_city',
                )
            );
        }
        
        
        $collection->getSelect()->joinLeft(
            array(
                $this->_orderTableAlias => $collection->getTable('amogrid/order_item')
            ),
            'order_item.item_id = ' . $this->_orderTableAlias . '.item_id', 
            $orderItemsColumns
        );

        $collection->getSelect()->group('main_table.entity_id');
        if (count($excludeStatuses) > 0){
            $collection->getSelect()->where(
                $collection->getConnection()->quoteInto('main_table.status NOT IN (?)', $excludeStatuses)
            );
        }

//        $collection->setIsCustomerMode(TRUE);
        
    }
    
    function getConfigurableFields(){
        if (!$this->_configurableFields){
            $this->_configurableFields = array(
                'am_product_images' => array(
                    'header' => 'Images',
                    'index' => 'product_images',
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_images',
                    'width' => 80,
                    'filter' => false,
                    'sortable'  => false,
                ),
                'am_coupon_code' => array(
                    'header' => $this->__('Coupon Code'),
                    'index' => 'am_coupon_code',
                    'width' => 80,
                    'filter_index' => 'order.coupon_code'
                
                ),
                'am_shipping_description' => array(
                    'header' => $this->__('Shipping Method'),
                    'index' => 'am_shipping_description',
                    'width' => 80,
                    'filter_index' => 'order.shipping_description'
                
                ),
                'am_method' => array(
                    'header' => $this->__('Payment Method'),
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_payment',
                    'index' => 'am_method',
                    'width' => 80,
                    'type'  => 'options',
                    'options' => Mage::helper('payment')->getPaymentMethodList(),
                    'filter_index' => 'order_payment.method'
                ),
                'am_shipping_address' => array(
                    'header' => $this->__('Shipping Address'),
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_address_shipping',
                    'index' => 'am_order_item_address_id',
                    'width' => 80,
                    'sortable'  => false,
                    'filter_index' => 
                            ' CONCAT(shipping_order_address.country_id, 
                            shipping_order_address.region,
                            shipping_order_address.city,
                            shipping_order_address.street) ',
                    
                ),
                'am_shipping_country_id' => array(
                    'header' => $this->__('Shipping: Country'),
                    'index' => 'am_shipping_country_id',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.country_id'
                
                ),
                'am_shipping_region' => array(
                    'header' => $this->__('Shipping: Region'),
                    'index' => 'am_shipping_region',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.region'
                
                ),
                'am_shipping_city' => array(
                    'header' => $this->__('Shipping: City'),
                    'index' => 'am_shipping_city',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.city'
                
                ),
                'am_shipping_postcode' => array(
                    'header' => $this->__('Shipping: Postcode'),
                    'index' => 'am_shipping_postcode',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.postcode'
                
                ),
                'am_shipping_street' => array(
                    'header' => $this->__('Shipping: Street'),
                    'index' => 'am_shipping_street',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.street'
                
                ),
                'am_billing_address' => array(
                    'header' => $this->__('Billing Address'),
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_address_billing',
                    'index' => 'am_order_item_address_id',
                    'width' => 80,
                    'sortable'  => false,
                    'filter_index' => 
                            ' CONCAT(billing_order_address.country_id, 
                            billing_order_address.region,
                            billing_order_address.city,
                            billing_order_address.street) ',
                ),
                'am_billing_country_id' => array(
                    'header' => $this->__('Billing: Country'),
                    'index' => 'am_billing_country_id',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.country_id'
                
                ),
                'am_billing_region' => array(
                    'header' => $this->__('Billing: Region'),
                    'index' => 'am_billing_region',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.region'
                
                ),
                'am_billing_city' => array(
                    'header' => $this->__('Billing: City'),
                    'index' => 'am_billing_city',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.city'
                
                ),
                'am_billing_postcode' => array(
                    'header' => $this->__('Billing: Postcode'),
                    'index' => 'am_billing_postcode',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.postcode'
                
                ),
                'am_billing_street' => array(
                    'header' => $this->__('Billing: Street'),
                    'index' => 'am_billing_street',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.street'
                
                ),
                'am_customer_email' => array(
                    'header' => $this->__('Customer Email'),
                    'index' => 'am_customer_email',
                    'width' => 80,
                    'filter_index' => 'order.customer_email'
                
                )
            );
        }
        return $this->_configurableFields;
        }
        
    protected function _prepareConfigurableField(&$grid, $key){
        $config = $this->getConfigurableFields();
        
        if (isset($config[$key])){
            $grid->addColumn($key, $config[$key]);
        }
    }
    
    protected function _prepareDefaultField(&$grid, $key){
        $config = $this->getDefaultFields();
        
        if (isset($config[$key])){
            $grid->addColumn($key, $config[$key]);
        }
    }
    
    protected function _prepareAttributeField(&$grid, $column, $export = FALSE){

        $key = $this->_extrOrderColumnPrefix.$column['key'];

        $grid->addColumn($key, array(
            'header' => $column['name'],
            'index' => $this->_orderTableAlias.'.'.$column['key'],
                'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_'.($export ? 'export' : 'default'),
//                'width' => '150px',
            'filter_index' => $this->_orderTableAlias.'.'.$column['key']
        ));


        }
        
    function prepareGrid(&$grid, $export = FALSE){
        
        $columns = $this->_getColumns();
        $after = NULL;
        
        foreach($columns as $key => $column){
            switch ($column['type']){
                case "configurable":
                        $this->_prepareConfigurableField($grid, $key);
                    break;
                case "default":
                        $this->_prepareDefaultField($grid, $key);
                    break;
                case "attribute":
                        $this->_prepareAttributeField($grid, $column, $export);
                    break;
            }
            
            $after = $key;
        }
    }
    
    function getDefaultFields(){
        if (!$this->_defaultField){
            $this->_defaultField = array(
                'am_real_order_id' => array(
                    'header'=> Mage::helper('sales')->__('Order #'),
                    'width' => '80px',
                    'type'  => 'text',
                    'index' => 'increment_id',
                    'filter_index' => 'main_table.increment_id'
                ),
                'am_created_at' => array(
                    'header' => Mage::helper('sales')->__('Purchased On'),
                    'index' => 'created_at',
                    'type' => 'datetime',
                    'width' => '100px',
                    'filter_index' => 'main_table.created_at'
                ),
                'am_billing_name' => array(
                    'header' => Mage::helper('sales')->__('Bill to Name'),
                    'index' => 'billing_name',
                    'filter_index' => 'main_table.billing_name'
                ),
                'am_shipping_name' => array(
                    'header' => Mage::helper('sales')->__('Ship to Name'),
                    'index' => 'shipping_name',
                    'filter_index' => 'main_table.shipping_name'
                ),
                'am_base_grand_total' => array(
                    'header' => Mage::helper('sales')->__('G.T. (Base)'),
                    'index' => 'base_grand_total',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'filter_index' => 'main_table.base_grand_total'
                ),
                'am_grand_total' => array(
                    'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                    'index' => 'grand_total',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'filter_index' => 'main_table.grand_total'
                ),
                'am_status' => array(
                    'header' => Mage::helper('sales')->__('Status'),
                    'index' => 'status',
                    'type'  => 'options',
                    'width' => '70px',
                    'filter_index' => 'main_table.status',
                    'options' => array_merge(array(NULL => ""), Mage::getSingleton('sales/order_config')->getStatuses()),
                )

            );
            
            if (!Mage::app()->isSingleStoreMode()) {
                $this->_defaultField['am_store_id'] = array(
                    'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                    'index'     => 'store_id',
                    'type'      => 'store',
                    'store_view'=> true,
                    'display_deleted' => true,
                    'filter_index' => 'main_table.store_id'
                );
            }
            
            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
                $this->_defaultField['am_action'] = array(
                        'header'    => Mage::helper('sales')->__('Action'),
                        'width'     => '50px',
                        'type'      => 'action',
                        'getter'     => 'getId',
                        'actions'   => array(
                            array(
                                'caption' => Mage::helper('sales')->__('View'),
                                'url'     => array('base'=>'*/sales_order/view'),
                                'field'   => 'order_id'
                            )
                        ),
                        'filter'    => false,
                        'sortable'  => false,
                        'index'     => 'stores',
                        'is_system' => true,
                );
            }
        }
        return $this->_defaultField;
    }
    
//    protected function addDefaultColumns(&$grid){
//        $grid->addColumn('am_real_order_id', array(
//            'header'=> Mage::helper('sales')->__('Order #'),
//            'width' => '80px',
//            'type'  => 'text',
//            'index' => 'increment_id',
//            'filter_index' => 'main_table.increment_id'
//        ));
//
//        if (!Mage::app()->isSingleStoreMode()) {
//            $grid->addColumn('am_store_id', array(
//                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
//                'index'     => 'store_id',
//                'type'      => 'store',
//                'store_view'=> true,
//                'display_deleted' => true,
//                'filter_index' => 'main_table.store_id'
//            ));
//        }
//
//        $grid->addColumn('am_created_at', array(
//            'header' => Mage::helper('sales')->__('Purchased On'),
//            'index' => 'created_at',
//            'type' => 'datetime',
//            'width' => '100px',
//            'filter_index' => 'main_table.created_at'
//        ));
//
//        $grid->addColumn('am_billing_name', array(
//            'header' => Mage::helper('sales')->__('Bill to Name'),
//            'index' => 'billing_name',
//            'filter_index' => 'main_table.billing_name'
//        ));
//
//        $grid->addColumn('am_shipping_name', array(
//            'header' => Mage::helper('sales')->__('Ship to Name'),
//            'index' => 'shipping_name',
//            'filter_index' => 'main_table.shipping_name'
//        ));
//
//        $grid->addColumn('am_base_grand_total', array(
//            'header' => Mage::helper('sales')->__('G.T. (Base)'),
//            'index' => 'base_grand_total',
//            'type'  => 'currency',
//            'currency' => 'base_currency_code',
//            'filter_index' => 'main_table.base_grand_total'
//        ));
//
//        $grid->addColumn('am_grand_total', array(
//            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
//            'index' => 'grand_total',
//            'type'  => 'currency',
//            'currency' => 'order_currency_code',
//            'filter_index' => 'main_table.grand_total'
//        ));
//
//        $grid->addColumn('am_status', array(
//            'header' => Mage::helper('sales')->__('Status'),
//            'index' => 'status',
//            'type'  => 'options',
//            'width' => '70px',
//            'filter_index' => 'main_table.status',
//            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
//        ));
//
////        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
////            $grid->addColumn('am_action',
////                array(
////                    'header'    => Mage::helper('sales')->__('Action'),
////                    'width'     => '50px',
////                    'type'      => 'action',
////                    'getter'     => 'getId',
////                    'actions'   => array(
////                        array(
////                            'caption' => Mage::helper('sales')->__('View'),
////                            'url'     => array('base'=>'*/sales_order/view'),
////                            'field'   => 'order_id'
////                        )
////                    ),
////                    'filter'    => false,
////                    'sortable'  => false,
////                    'index'     => 'stores',
////                    'is_system' => true,
////            ));
////        }
//    }
    
    
    public function removeColumns($grid){
        $this->_removeDefaultColumns($grid);
        $this->_removeStaticColumns($grid);
    }
  
    protected function _removeStaticColumns($grid){
        $staticColumns = $this->_getStaticColumns();
                
        if (is_array($staticColumns)){
            foreach($staticColumns as $key => $column){
                $available = isset($column['available']) && $column['available'] == 1;
                if (!$available){
                    
                    $this->_removeColumn($grid, $column['relation']);
                }
            }
        }
    }
    
    protected function _removeDefaultColumns($grid){
        $mainTableColumns = array(
            'real_order_id', 'store_id',
            'created_at', 'billing_name', 'shipping_name', 'base_grand_total',
            'grand_total', 'status', 'action'
        );
        
        $columns = $grid->getColumns();

        foreach($columns as $column){

            $columnId = $column->getId();
            if (in_array($columnId, $mainTableColumns))
            {
                $this->_removeColumn($grid, $columnId);
            }
        }
    }
    
    protected function _removeColumn($grid, $columnId){
        if (method_exists($grid, 'removeColumn'))
            $grid->removeColumn($columnId);
        else
        $grid->addColumn($columnId, array(
            'header_css_class' => 'am_hidden',
            'column_css_class' => 'am_hidden',
            'filter'    => false,
            'sortable'  => false,
        ));   
    }
    
    protected function _getColumnKey($column){
        $key = $column['key'];

        switch ($column['type']){
            case self::$_TYPE_ATTRIBUTE:
                $key = $this->_extrOrderColumnPrefix.$column['key'];
                break;

            case self::$_TYPE_STATIC:
                $key = $column['relation'];
                break;
        };
        return $key;
    }
    
    function reorder($grid){
        if (method_exists($grid, 'addColumnsOrder')){
           $grid->sortColumnsByOrder();
        
            $columns = $this->_getColumns();
            $after = null;
            foreach($columns as $column){
                $key = $this->_getColumnKey($column);

                $gridColumn = $grid->getColumn($key);
                
                if ($gridColumn){
                $grid->addColumnsOrder($key, $after);//->sortColumnsByOrder();
                $after = $key;
            }
            }

            $grid->sortColumnsByOrder(); 
        } else {
            //SOME TO DO
        }
    }
    
    function restyle($grid){
        $columns = $this->_getColumns();
        foreach($columns as $column){
            $key = $this->_getColumnKey($column);
            
            $gridColumn = $grid->getColumn($key);
            
            if (!empty($column['width']) && $gridColumn){
                $gridColumn->setData('width', $column['width']);
            }
        }
    }
}
?>