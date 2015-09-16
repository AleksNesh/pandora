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
/**
 * @category    Brim
 * @package     Brim_Groupedoptions
 * @copyright   Copyright (c) 2011-2012 Brim LLC. (http://www.brimllc.com)
 */
class Brim_Groupedoptions_Model_Resource_Product_Indexer_Price_Grouped
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Grouped
    // extends Mage_Catalog_Model_Resource_Product_Indexer_Price_Grouped
{

    protected function _prepareGroupedProductPriceData($entityIds = null)
    {
        $write = $this->_getWriteAdapter();
        $table = $this->getIdxTable();

        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), 'entity_id')
            ->joinLeft(
                array('l' => $this->getTable('catalog/product_link')),
                'e.entity_id = l.product_id AND l.link_type_id=' . Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED,
                array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');

        /* BEGIN Brim Grouped-Options Customizations */
        // $minCheckSql = $write->getCheckSql('le.required_options = 0', 'i.min_price', 0);
        // $maxCheckSql = $write->getCheckSql('le.required_options = 0', 'i.max_price', 0);
        $minCheckSql = 'i.min_price';
        $maxCheckSql = 'i.max_price';
        /* END Brim Grouped-Options Customizations */

        $select->columns('website_id', 'cw')
            ->joinLeft(
                array('le' => $this->getTable('catalog/product')),
                'le.entity_id = l.linked_product_id',
                array())
            ->joinLeft(
                array('i' => $table),
                'i.entity_id = l.linked_product_id AND i.website_id = cw.website_id'
                    . ' AND i.customer_group_id = cg.customer_group_id',
                array(
                    'tax_class_id' => new Zend_Db_Expr('IF((MIN(i.tax_class_id) IS NULL), 0, MIN(i.tax_class_id))'),
                    'price'        => new Zend_Db_Expr('NULL'),
                    'final_price'  => new Zend_Db_Expr('NULL'),
                    'min_price'    => new Zend_Db_Expr('MIN(' . $minCheckSql . ')'),
                    'max_price'    => new Zend_Db_Expr('MAX(' . $maxCheckSql . ')'),
                    'tier_price'   => new Zend_Db_Expr('NULL'),
                ))
            ->group(array('e.entity_id', 'cg.customer_group_id', 'cw.website_id'))
            ->where('e.type_id=?', $this->getTypeId());

        /* BEGIN Brim Grouped-Options Customizations */
        if (version_compare(Mage::getVersion(), '1.7.0.0-rc1') >= 0) {
            $select->columns(array('group_price'  => new Zend_Db_Expr('NULL')));
        }
        /* END Brim Grouped-Options Customizations */

        if (!is_null($entityIds)) {
            $select->where('l.product_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('catalog_product_prepare_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }
}