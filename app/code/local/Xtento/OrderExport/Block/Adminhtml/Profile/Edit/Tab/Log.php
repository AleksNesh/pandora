<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2012-12-21T17:52:57+01:00
 * File:          app/code/local/Xtento/OrderExport/Block/Adminhtml/Profile/Edit/Tab/Log.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Block_Adminhtml_Profile_Edit_Tab_Log extends Xtento_OrderExport_Block_Adminhtml_Log_Grid
{
    protected function _getProfile()
    {
        return Mage::registry('profile') ? Mage::registry('profile') : Mage::getModel('xtento_orderexport/profile')->load($this->getRequest()->getParam('id'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->getSelect()->joinLeft(array('profile' => $collection->getTable('xtento_orderexport/profile')), 'main_table.profile_id = profile.profile_id', array('concat(profile.name," (ID: ", profile.profile_id,")") as profile', 'profile.entity', 'profile.name'));
        $collection->addFieldToFilter('profile.profile_id', $this->_getProfile()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        unset($this->_columns['profile']);
        foreach ($this->_columns as $key => $column) {
            if ($key == 'log_id') {
                continue;
            }
            // Rename column IDs so they're not posted to the profile information
            $column->setId('col_' . $column->getId());
            $this->_columns['col_' . $key] = $column;
            unset($this->_columns[$key]);
        }
    }

    protected function _prepareMassaction()
    {
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/logGrid', array('_current' => true));
    }
}