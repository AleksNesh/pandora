<?php
/**
 *
 * @category    Brainvire
 * @package     Brainvire_OrderComment
 * @copyright   Copyright (c) 2011-2012 Brainvire Infotech Pvt. Ltd. <www.brainvire.com>
 */
class Brainvire_OrderComment_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    /**
     * Columns, that become ambiguous after join
     *
     * @var array
     */
    protected $_ambiguousColumns = array(
        'status',
        'created_at',
    );

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'ordercomment/order_grid_collection';
    }

    /**
     * Prepare grid columns
     *
     * @return Brainvire_OrderComment_Block_Adminhtml_Sales_Order_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        // Add order comment to grid
        $this->addColumn('ordercomment', array(
            'header'       => Mage::helper('ordercomment')->__('Order Comment'),
            'index'        => 'ordercomment',
            'filter_index' => 'ordercomment_table.comment',
        ));

        // Fix integrity constraint violation in SELECT
        foreach ($this->_ambiguousColumns as $index) {
            if (isset($this->_columns[$index])) {
                $this->_columns[$index]->setFilterIndex('main_table.' . $index);
            }
        }

        return $this;
    }

    /**
     * Prepare grid massactions
     *
     * @return Brainvire_OrderComment_Block_Adminhtml_Sales_Order_Grid
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        // VERY dirty hack to resolve conflict with Seamless Delete Order
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (isset($modules['EM_DeleteOrder']) && $modules['EM_DeleteOrder']->is('active')) {
            $this->getMassactionBlock()->addItem('delete_order', array(
               'label'=> Mage::helper('sales')->__('Delete order'),
               'url'  => $this->getUrl('*/sales_order/deleteorder'),
            ));
        }
        return $this;
    }
}
