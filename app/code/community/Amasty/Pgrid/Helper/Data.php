<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected function _getStore()
    {
        $ret = NULL;
        
        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
//        var_dump($storeId);
        
        if ($storeId === 0)
            $ret = Mage::app()->getWebsite(true)->getDefaultStore();
        else
            $ret = Mage::app()->getStore($storeId);
        
        return $ret;
    }
    
    public function getColumnsProperties($json = true, $reloadAttributes = false)
    {
        $prop = array();
        
        if (Mage::getStoreConfig('ampgrid/cols/name'))
        {
            $prop['name'] = array(
                'type'      => 'text',
                'col'       => 'name',
            );
            
            $prop['custom_name'] = array(
                'type'      => 'text',
                'col'       => 'custom_name',
            );
        }

        if (Mage::getStoreConfig('ampgrid/cols/sku'))
        {
            $prop['sku'] = array(
                'type'      => 'text',
                'col'       => 'sku',
            );
        }
        
        if (Mage::getStoreConfig('ampgrid/cols/price'))
        {
            $prop['price'] = array(
                'type'      => 'price',
                'col'       => 'price',
                'format'    => 'numeric',
            );
        }
        
        if (Mage::getStoreConfig('ampgrid/cols/qty'))
        {
            $prop['qty'] = array(
                'type'      => 'text',
                'col'       => 'qty',
                'obj'       => 'stock_item',
                'format'    => 'numeric',
            );
        }
        
        if (Mage::getStoreConfig('ampgrid/additional/avail'))
        {
            $prop['is_in_stock'] = array(
                'type'      => 'select',
                'options'   => array(0 => $this->__('Out of stock'), 1 => $this->__('In stock')),
                'col'       => 'is_in_stock',
                'obj'       => 'stock_item',
            );
        }
        
        if (Mage::getStoreConfig('ampgrid/cols/vis'))
        {
            $visibilityOptions = Mage::getModel('catalog/product_visibility')->getOptionArray();
            $prop['visibility'] = array(
                'type'      => 'select',
                'options'   => $visibilityOptions,
                'col'       => 'visibility',
            );
        }
        
        if (Mage::getStoreConfig('ampgrid/cols/status'))
        {
            $statusOptions = Mage::getSingleton('catalog/product_status')->getOptionArray();
            $prop['status'] = array(
                'type'      => 'select',
                'options'   => $statusOptions,
                'col'       => 'status',
            );
        }
        
        if (Mage::getStoreConfig('ampgrid/additional/special_price'))
        {
            $prop['special_price'] = array(
                'type'      => 'price',
                'col'       => 'special_price',
                'format'    => 'numeric',
            );
        }
        
        if (Mage::getStoreConfig('ampgrid/additional/special_price_dates'))
        {
            $prop['special_from_date'] = array(
                'type'      => 'date',
                'col'       => 'special_from_date',
            );
            $prop['special_to_date'] = array(
                'type'      => 'date',
                'col'       => 'special_to_date',
            );
        }
        
        if (Mage::getStoreConfig('ampgrid/additional/cost'))
        {
            $prop['cost'] = array(
                'type'      => 'price',
                'col'       => 'cost',
                'format'    => 'numeric',
            );
        }

        if (Mage::getStoreConfig('ampgrid/attr/cols'))
        {
            if ($reloadAttributes)
            {
                $attributes = $this->prepareGridAttributesCollection();
                Mage::register('ampgrid_grid_attributes', $attributes);
            }
            
            // adding grid attributes to editable columns
            // @see Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid::_prepareColumns for registry param
            if ($attributes = Mage::registry('ampgrid_grid_attributes'))
            {
                foreach ($attributes as $attribute)
                {
                    $prop[$attribute->getAttributeCode()] = array(
                        'col'       => $attribute->getAttributeCode(),
                        'source'    => 'attribute', // will be used to make difference between default columns and attribute columns
                    );
                    if ('select' == $attribute->getFrontendInput() || 'multiselect' == $attribute->getFrontendInput() || 'boolean'  == $attribute->getFrontendInput())
                    {
                        if ('multiselect' == $attribute->getFrontendInput())
                        {
                            $prop[$attribute->getAttributeCode()]['type'] = 'multiselect';
                        } else 
                        {
                            $prop[$attribute->getAttributeCode()]['type'] = 'select';
                        }
                        $propOptions = array();
                        
                        if ('custom_design' == $attribute->getAttributeCode())
                        {
                            $allOptions = $attribute->getSource()->getAllOptions();
                            if (is_array($allOptions) && !empty($allOptions))
                            {
                                foreach ($allOptions as $option)
                                {
                                    if (!is_array($option['value']))
                                    {
                                        $propOptions[$option['value']] = $option['value'];
                                    } else 
                                    {
                                        foreach ($option['value'] as $option2)
                                        {
                                            if (isset($option2['value']))
                                            {
                                                $propOptions[$option2['value']] = $option2['value'];
                                            }
                                        }
                                    }
                                }
                            }
                        } else 
                        {
                            // getting attribute values with translation
                            $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                ->setAttributeFilter($attribute->getId())
                                ->setStoreFilter($this->getStore()->getId(), false)
                                ->load();
                            if ($valuesCollection->getSize() > 0)
                            {
                                $propOptions[''] = '';
                                foreach ($valuesCollection as $item) {
                                    $propOptions[$item->getId()] = $item->getValue();
                                }
                            } else 
                            {
                                $selectOptions = $attribute->getFrontend()->getSelectOptions();
                                if ($selectOptions)
                                {
                                    foreach ($selectOptions as $selectOption)
                                    {
                                        $propOptions[$selectOption['value']] = $selectOption['label'];
                                    }
                                }
                            }
                        }
                        
                        $prop[$attribute->getAttributeCode()]['options'] = $propOptions;
                        
                        if (!$propOptions)
                        {
                            unset($prop[$attribute->getAttributeCode()]); // we should not make attribute editable, if it has no options
                        }
                    } elseif ('textarea' == $attribute->getFrontendInput()) 
                    {
                        $prop[$attribute->getAttributeCode()]['type'] = 'textarea';
                    } elseif ('price' == $attribute->getFrontendInput())
                    {
                        $prop[$attribute->getAttributeCode()]['type']          = 'price';
                        $prop[$attribute->getAttributeCode()]['currency_code'] = $this->_getStore()->getBaseCurrency()->getCode();
                        $prop[$attribute->getAttributeCode()]['format']        = 'numeric';
                    }
                    else 
                    {
                        $prop[$attribute->getAttributeCode()]['type'] = 'text';
                    }
                }
            }
        }

        if (!$json)
        {
            return $prop;
        }

        return Mage::helper('core')->jsonEncode($prop);
    }
    
    public function getDefaultColumns()
    {
        return array('name', 'sku', 'price', 'qty', 'visibility', 'status');
    }
    
    public function attachGridColumns(&$grid, &$gridAttributes, $store){
        foreach ($gridAttributes as $attribute)
        {
            $props = array(
                'header'=> $attribute->getStoreLabel(),
                'index' => $attribute->getAttributeCode(),
                'filter_index' => 'am_attribute_'.$attribute->getAttributeCode()
            );
            if ('price' == $attribute->getFrontendInput())
            {
                $props['type']          = 'price';
                $props['currency_code'] = $store->getBaseCurrency()->getCode();
            }
            if ('select' == $attribute->getFrontendInput() || 'multiselect' == $attribute->getFrontendInput() || 'boolean' == $attribute->getFrontendInput())
            {
                $propOptions = array();

                if ('multiselect' == $attribute->getFrontendInput())
                {
                    $propOptions['null'] = $this->__('- No value specified -');
                }

                if ('custom_design' == $attribute->getAttributeCode())
                {
                    $allOptions = $attribute->getSource()->getAllOptions();
                    if (is_array($allOptions) && !empty($allOptions))
                    {
                        foreach ($allOptions as $option)
                        {
                            if (!is_array($option['value']))
                            {
                                if ($option['value'])
                                {
                                    $propOptions[$option['value']] = $option['value'];
                                }
                            } else 
                            {
                                foreach ($option['value'] as $option2)
                                {
                                    if (isset($option2['value']))
                                    {
                                        $propOptions[$option2['value']] = $option2['value'];
                                    }
                                }
                            }
                        }
                    }
                } else 
                {
                    // getting attribute values with translation
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter($store->getId(), false)
                        ->load();
                    if ($valuesCollection->getSize() > 0)
                    {
                        foreach ($valuesCollection as $item) {
                            $propOptions[$item->getId()] = $item->getValue();
                        }
                    } else 
                    {
                        $selectOptions = $attribute->getFrontend()->getSelectOptions();
                        if ($selectOptions)
                        {
                            foreach ($selectOptions as $selectOption)
                            {
                                $propOptions[$selectOption['value']] = $selectOption['label'];
                            }
                        }
                    }
                }

                if ($attribute->getFrontendInput() == 'boolean'){
                    $propOptions = array(
                        '1' => $this->__('Yes'),
                        '0' => $this->__('No')
                    );
                }

                if ('multiselect' == $attribute->getFrontendInput())
                {
                    $props['renderer'] = 'ampgrid/adminhtml_catalog_product_grid_renderer_multiselect';
                    $props['filter']   = 'ampgrid/adminhtml_catalog_product_grid_filter_multiselect';
                }

                $props['type'] = 'options';
                $props['options'] = $propOptions;
            }

            $grid->addColumn($attribute->getAttributeCode(), $props);
        }
    }
    
    public function getGridAttributes($attributesKey = '')
    {
        // will load columns by admin users, if necessary
        $extraKey = $attributesKey;
        if (Mage::getStoreConfig('ampgrid/attr/byadmin'))
        {
            $extraKey .= Mage::getSingleton('admin/session')->getUser()->getId();
        }
        
        $selected = (string) Mage::getStoreConfig('ampgrid/attributes/ongrid' . $extraKey);
        if ($selected)
        {
            return explode(',', $selected);
        }
        return array();
    }
    
    public function isCategoryColumnEnabled(){
        $extraKey = '';
        if (Mage::getStoreConfig('ampgrid/attr/byadmin'))
        {
            $extraKey .= Mage::getSingleton('admin/session')->getUser()->getId();
        }
        
        $category = Mage::getStoreConfig('ampgrid/attributes/category' . $extraKey);
        if ($category === NULL){
            //OLD OPTION BY DEFAULT
            $category = Mage::getStoreConfig('ampgrid/additional/category');
        }
        
        return $category == 1;
    }
    
    public function prepareGridAttributesCollection($attributesKey = '')
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                         ->addVisibleFilter()
                         ->addStoreLabel($this->getStore()->getId());
        $attributes->getSelect()->where(
            $attributes->getConnection()->quoteInto('main_table.attribute_id IN (?)', Mage::helper('ampgrid')->getGridAttributes($attributesKey))
        );
        return $attributes;
    }
    
    public function getStore()
    {
        return $this->_getStore();
//        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
//        return Mage::app()->getStore($storeId);
    }
    
    public function getGridThumbSize()
    {
        return 70;
    }
    
    public function getAllowedQtyMath()
    {
        return 'true';
    }
}