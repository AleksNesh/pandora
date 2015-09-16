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
class Altima_Lookbookslider_Block_Adminhtml_Lookbookslider_Edit_Tab_Page extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_model = Mage::registry('lookbookslider_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('lookbookslider_form', array('legend'=>Mage::helper('lookbookslider')->__('CMS Pages')));
        $fieldset->addField('pages', 'multiselect', array(
            'label'     => Mage::helper('lookbookslider')->__('Visible In'),
            'required'  => false,
            'name'      => 'pages[]',
            'values'    => Mage::getSingleton('lookbookslider/page')->toOptionArray(),
            'value'     => $_model->getPageId()
        ));
        
        return parent::_prepareForm();
    }
}
