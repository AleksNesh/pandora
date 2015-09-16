<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-01-18T20:47:10+01:00
 * File:          app/code/local/Xtento/OrderExport/Block/Adminhtml/Profile/Edit.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Block_Adminhtml_Profile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'xtento_orderexport';
        $this->_controller = 'adminhtml_profile';

        if (Mage::registry('profile')->getId()) {
            $this->_addButton('duplicate_button', array(
                'label' => Mage::helper('xtento_orderexport')->__('Duplicate Profile'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/duplicate', array('_current' => true)) . '\')',
                'class' => 'add',
            ), 0);

            $this->_addButton('export_button', array(
                'label' => Mage::helper('xtento_orderexport')->__('Export Profile'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/orderexport_manual/index', array('profile_id' => Mage::registry('profile')->getId())) . '\')',
                'class' => 'go',
            ), 0);

            $this->_updateButton('save', 'label', Mage::helper('xtento_orderexport')->__('Save Profile'));
            $this->_updateButton('delete', 'label', Mage::helper('xtento_orderexport')->__('Delete Profile'));
            $this->_removeButton('reset');
        } else {
            $this->_removeButton('delete');
            $this->_removeButton('save');
        }

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('xtento_orderexport')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                if (editForm && editForm.validator.validate()) {
                    Element.show('loading-mask');
                    setLoaderPosition();
                    var tabsIdValue = profile_tabsJsTabs.activeTab.id;
                    var tabsBlockPrefix = 'profile_tabs_';
                    if (tabsIdValue.startsWith(tabsBlockPrefix)) {
                        tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
                    }
                }
                editForm.submit($('edit_form').action+'continue/edit/active_tab/'+tabsIdValue);
            }
            varienGlobalEvents.attachEventHandler('formSubmit', function(){
                if (editForm && editForm.validator.validate()) {
                    Element.show('loading-mask');
                    setLoaderPosition();
                }
            });
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('profile')->getId()) {
            return Mage::helper('xtento_orderexport')->__('Edit ' . ucfirst(Mage::registry('profile')->getEntity()) . ' Export Profile \'%s\'', Mage::helper('xtcore/core')->escapeHtml(Mage::registry('profile')->getName()));
        } else {
            return Mage::helper('xtento_orderexport')->__('New Profile');
        }
    }

    protected function _toHtml()
    {
        return $this->getLayout()->createBlock('xtento_orderexport/adminhtml_widget_menu')->setShowWarning(1)->toHtml() . parent::_toHtml();
    }
}