<?php

class Pan_Giftcards_Block_Adminhtml_Cardsload_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('giftcards')->__('Import Gift Cards')));

        $fieldset->addField('file', 'file', array(
                'name'      => 'file',
                'label'     => Mage::helper('giftcards')->__('Select file to import'),
                'title'     => Mage::helper('giftcards')->__('Select file to import'),
                'required'  => true
            )
        );

        $fieldset->addField('import_action', 'select', array(
            'label'     => Mage::helper('giftcards')->__('Import Action'),
            'name'      => 'import_action',
            'required'  => true,
            'values'    => array('update' => 'Update on duplicate', 'skip' => 'Skip on duplicate'),
            'value'     => 'update',
            'note'      => 'What should be the action to be taken when a duplicated is found?',
        ));

        $fieldset = $form->addFieldset('advanced_fieldset', array('legend' => Mage::helper('giftcards')->__('Advanced Options')));


        $fieldset->addField('delimiter', 'text', array(
            'name'      => 'delimiter',
            'label'     => Mage::helper('giftcards')->__('Value delimiter'),
            'title'     => Mage::helper('giftcards')->__('Value delimiter'),
            'value'     => ',',
            'required'  => true,
        ));

        $fieldset->addField('enclosure', 'text', array(
            'name'     => 'enclosure',
            'label'    => Mage::helper('giftcards')->__('Enclose Values In'),
            'title'    => Mage::helper('giftcards')->__('Enclose Values In'),
            'value'    => '""',
            'required' => true,
        ));


        $form->setAction($this->getUrl('*/adminhtml_cardsload/save'));
        $form->setMethod('post');
        $form->setId('edit_form');
        $form->setEnctype('multipart/form-data');
        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}