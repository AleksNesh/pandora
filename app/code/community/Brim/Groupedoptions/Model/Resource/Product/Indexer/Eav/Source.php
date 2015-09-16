<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Brim_Groupedoptions_Model_Resource_Product_Indexer_Eav_Source
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Eav_Source
    //extends Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
{


    /**
     * Uncomment method below to allow non-visible items to be included in layered nav for configurable items.
     *
     * @return Brim_Groupedoptions_Model_Resource_Product_Indexer_Eav_Source
     */
//    protected function _removeNotVisibleEntityFromIndex()
//    {
//        return $this;
//    }

    protected function _prepareRelationIndex($parentIds = null)
    {
        $write      = $this->_getWriteAdapter();
        $idxTable   = $this->getIdxTable();

        $select = $write->select()
            ->from(array('l' => $this->getTable('catalog/product_relation')), 'parent_id')
            ->join(
                array('cs' => $this->getTable('core/store')),
                '',
                array())
            ->join(
                array('i' => $idxTable),
                'l.child_id = i.entity_id AND cs.store_id = i.store_id',
                array('attribute_id', 'store_id', 'value'))
            ->group(array(
                'l.parent_id', 'i.attribute_id', 'i.store_id', 'i.value'
            ));
        if (!is_null($parentIds)) {
            $select->where('l.parent_id IN(?)', $parentIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('l.parent_id'),
            'website_field' => new Zend_Db_Expr('cs.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $query = $write->insertFromSelect($select, $idxTable, array(), Varien_Db_Adapter_Interface::INSERT_IGNORE);
        $write->query($query);

        /* BEGIN Brim Grouped-Options Customizations */
        if (Mage::getStoreConfigFlag('groupedoptions/frontend/enable_expanded_layerednav')) {
            /*
                Added better support for configurable products in layered navigation.  simple products associated with
                configurable products were not flowing into the grouped products causing grouped products to not be displayed
                when they should be.
             */
            $select->join(
                array('grouped_catalog' => $this->getTable('catalog/product')),
                'l.parent_id = grouped_catalog.entity_id',
                array()
            );
            $select->where("grouped_catalog.type_id = 'grouped'");

            $query = $write->insertFromSelect($select, $idxTable, array(), Varien_Db_Adapter_Interface::INSERT_IGNORE);
            $write->query($query);
        }
        /* END Brim Grouped-Options Customizations */

        return $this;
    }
}
