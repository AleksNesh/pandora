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
class Altima_Lookbookslider_Block_Adminhtml_Lookbookslider_Edit_Tab_Widget extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $_model = Mage::registry('lookbookslider_data');
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('lookbookslider_form', array('legend'=>Mage::helper('lookbookslider')->__('Widget Information (Takes effect only if Lookbookslider included as widget)')));
      
      $fieldset->addField('include_jquery', 'select', array(
          'label'     => Mage::helper('lookbookslider')->__('Include jQuery'),
          'name'      => 'include_jquery',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('lookbookslider')->__('Yes'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('lookbookslider')->__('No'),
              ),
          ),
          'note'      => Mage::helper('lookbookslider')->__('If YES jQuery will be included before slider output. Set to No if you are sure, that jQuery already included before slider output.'),
      ));

      $fieldset->addField('include_slides_js', 'select', array(
          'label'     => Mage::helper('lookbookslider')->__('Include plugin javascripts'),
          'name'      => 'include_slides_js',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('lookbookslider')->__('Yes'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('lookbookslider')->__('No'),
              ),
          ),
          'note'      => Mage::helper('lookbookslider')->__("Set No if you exactly know that slider's JS were included already before slider output."),
      ));
      
      if ( Mage::getSingleton('adminhtml/session')->getLookbooksliderData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getLookbooksliderData());
          Mage::getSingleton('adminhtml/session')->setLookbooksliderData(null);
      } elseif ( Mage::registry('lookbookslider_data') ) {
          $form->setValues(Mage::registry('lookbookslider_data')->getData());
      }           
      return parent::_prepareForm();
  }
}