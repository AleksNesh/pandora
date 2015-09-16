<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.2
 * @build     1230
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_SearchIndex_Model_Catalogsearch_Resource_Fulltext_Collection extends Mage_CatalogSearch_Model_Resource_Fulltext_Collection
{
    public function addSearchFilter($query)
    {
        $catalogIndex = Mage::helper('searchindex/index')->getIndex('mage_catalog_product');
        $catalogIndex->joinMatched($this);


        if (Mage::getSingleton('catalog/session')->getSortOrder() == ''
            && Mage::getSingleton('catalog/session')->getSortDirection() == '') {
            $this->setOrder('relevance');
        }

        $this->_addStockOrder($this);

        return $this;
    }

    protected function _addStockOrder($collection)
    {
        $index = Mage::helper('searchindex/index')->getIndex('mage_catalog_product');

        if ($index->getProperty('out_of_stock_to_end')) {
            $resource = Mage::getSingleton('core/resource');
            $select = $collection->getSelect();
            $select->joinLeft(
                array('_inventory_table' => $resource->getTableName('cataloginventory/stock_item')),
                '_inventory_table.product_id = e.entity_id',
                array()
            );
            $select->order('_inventory_table.is_in_stock DESC');
        }

        return $this;
    }
}
