<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Model_Mysql4_Lookbookslider extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the lookbookslider_id refers to the key field in your database table.
        $this->_init('lookbookslider/lookbookslider', 'lookbookslider_id');
    }
    
        /**
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        if (!$object->getIsMassDelete()) {
            $object = $this->__loadPage($object);
            $object = $this->__loadCategory($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($data = $object->getPageId()) {
            $select->join(
                    array('page' => $this->getTable('lookbookslider/page')), $this->getMainTable().'.lookbookslider_id = `page`.lookbookslider_id')
                    ->where('`page`.page_id in (?) ', $data);
        }
        if ($data = $object->getCategoryId()) {
            $select->join(
                    array('category' => $this->getTable('lookbookslider/category')), $this->getMainTable().'.lookbookslider_id = `category`.lookbookslider_id')
                    ->where('`category`.category_id in (?) ', $data);
        }
        //$select->order('name DESC')->limit(1);

        return $select;
    }

    /**
     * Call-back function
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        if (!$object->getIsMassStatus()) {
            $this->__saveToPageTable($object);
            $this->__saveToCategoryTable($object);
        }
        return parent::_afterSave($object);
    }

    /**
     * Call-back function
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if ($object->getEffect()) {
            $effect = implode(',',$object->getEffect());
        }
        else
        {
            $effect = 'random';
        }
        $object->setEffect($effect);
        return parent::_beforeSave($object);
    }
    
    /**
     * Call-back function
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
        // Cleanup stats on blog delete
        $adapter = $this->_getReadAdapter();
        // 1. Delete lookbookslider/slide
        $adapter->delete($this->getTable('lookbookslider/slide'), 'lookbookslider_id='.$object->getId());
        // 2. Delete lookbookslider/page
        $adapter->delete($this->getTable('lookbookslider/page'), 'lookbookslider_id='.$object->getId());
        // 3. Delete lookbookslider/category
        $adapter->delete($this->getTable('lookbookslider/category'), 'lookbookslider_id='.$object->getId());

        return parent::_beforeDelete($object);
    }

    /**
     * Load pages
     */
    private function __loadPage(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('lookbookslider/page'))
                ->where('lookbookslider_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['page_id'];
            }
            $object->setData('page_id', $array);
            $object->setData('pages', $array);
        }
        return $object;
    }

    /**
     * Load categories
     */
    private function __loadCategory(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('lookbookslider/category'))
                ->where('lookbookslider_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['category_id'];
            }
            $object->setData('category_id', $array);
            $object->setData('categories', implode(',',$array));
        }
        return $object;
    }

    /**
     * Save pages
     */
    private function __saveToPageTable(Mage_Core_Model_Abstract $object) {
        if ($data = $object->getData('pages')) {

            $this->_getWriteAdapter()->beginTransaction();
            try {
                $condition = $this->_getWriteAdapter()->quoteInto('lookbookslider_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('lookbookslider/page'), $condition);

                foreach ((array)$data as $page) {
                    $pageArray = array();
                    $pageArray['lookbookslider_id'] = $object->getId();
                    $pageArray['page_id'] = $page;
                    $this->_getWriteAdapter()->insert(
                            $this->getTable('lookbookslider/page'), $pageArray);
                }
                $this->_getWriteAdapter()->commit();
            } catch (Exception $e) {
                $this->_getWriteAdapter()->rollBack();
                echo $e->getMessage();
            }
            return true;
        }

        $condition = $this->_getWriteAdapter()->quoteInto('lookbookslider_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('lookbookslider/page'), $condition);
    }

    /**
     * Save categories
     */
    private function __saveToCategoryTable(Mage_Core_Model_Abstract $object) {
        if ($data = $object->getData('categories')) {

            $this->_getWriteAdapter()->beginTransaction();
            try {
                $condition = $this->_getWriteAdapter()->quoteInto('lookbookslider_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('lookbookslider/category'), $condition);
                $data = explode(',',$data);
                    if (is_array($data)) {
                        $data = array_unique($data);
                    }
                foreach ((array)$data as $category) {
                    $categoryArray = array();
                    $categoryArray['lookbookslider_id'] = $object->getId();
                    $categoryArray['category_id'] = $category;
                    $this->_getWriteAdapter()->insert(
                            $this->getTable('lookbookslider/category'), $categoryArray);
                }
                $this->_getWriteAdapter()->commit();
            } catch (Exception $e) {
                $this->_getWriteAdapter()->rollBack();
                echo $e->getMessage();
            }
            return true;
        }

        $condition = $this->_getWriteAdapter()->quoteInto('lookbookslider_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('lookbookslider/category'), $condition);
    }

}