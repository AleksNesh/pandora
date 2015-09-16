<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Block_Adminhtml_Catalog_Category_Tab_Product extends Mage_Adminhtml_Block_Catalog_Category_Tab_Product
{
    protected $_gridAttributes = array();
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $this->setChild('attributes_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ampgrid')->__('Grid Attribute Columns'),
                    'onclick'   => 'pAttribute.showConfig();',
                    'class'     => 'task'
                ))
        );

        $this->_gridAttributes = Mage::helper('ampgrid')->prepareGridAttributesCollection('categories');
    }
    
    protected function _parentPrepareCollection(){
        
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(array('in_category'=>1));
        }
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addStoreFilter($this->getRequest()->getParam('store'))
            ->joinField('position',
                'catalog/category_product',
                'position',
                'product_id=entity_id',
                'category_id='.(int) $this->getRequest()->getParam('id', 0),
                'left');
        $this->setCollection($collection);

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
        }

    }
    
    public function getAttributesButtonHtml()
    {
        return $this->getChildHtml('attributes_button');
    }
    
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html = $this->getAttributesButtonHtml() . $html;
        return $html;
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        
        $this->_parentPrepareCollection();
        
        $collection = $this->getCollection();

        if (Mage::getStoreConfig('ampgrid/category/thumb'))
        {
            $collection->joinAttribute('thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', $this->_getStore()->getId());
        }

        if ($this->_gridAttributes->getSize() > 0)
        {
            foreach ($this->_gridAttributes as $attribute)
            {
                $collection->joinAttribute($attribute->getAttributeCode(), 'catalog_product/' . $attribute->getAttributeCode(), 'entity_id', null, 'left', $store->getId());
            }
        }
        
        $this->setCollection($collection);
        
        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if (Mage::getStoreConfig('ampgrid/additional/thumb'))
        {
            if (method_exists($this, "addColumnAfter"))
            {
                $this->addColumnAfter('thumb',
                    array(
                        'header'    => Mage::helper('catalog')->__('Thumbnail'),
                        'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_thumb',
                        'index'		=> 'thumbnail',
                        'sortable'  => true,
                        'filter'    => false,
                        'width'     => 90,
                    ), 'entity_id');
            } else
            {
                // will add thumbnail column to be the first one
                $this->addColumn('thumb',
                    array(
                        'header'    => Mage::helper('catalog')->__('Thumbnail'),
                        'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_thumb',
                        'index'		=> 'thumbnail',
                        'sortable'  => true,
                        'filter'    => false,
                        'width'     => 90,
                    ));
            }
        }

        parent::_prepareColumns();
        
        if ($this->_gridAttributes->getSize() > 0)
        {
            Mage::helper('ampgrid')->attachGridColumns($this, $this->_gridAttributes, $this->_getStore());
        }

        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}