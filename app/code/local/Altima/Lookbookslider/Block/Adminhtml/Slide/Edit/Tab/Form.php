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
class Altima_Lookbookslider_Block_Adminhtml_Slide_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      
      $_model = Mage::registry('slide_data');
      $this->setForm($form);

      $fieldset = $form->addFieldset('slide_form', array('legend'=>Mage::helper('lookbookslider')->__('Slide information')));
     
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('lookbookslider')->__('Name'),
          'required'  => true,
          'name'      => 'name',
      ));
 
      $fieldset->addField('lookbookslider_id', 'hidden', array(
          'name'      => 'lookbookslider_id',
          'required'  => true,
          'value'     => $_model->getlookbookslider_id(),
      ));

      $fieldset->addField('caption', 'textarea', array(
          'label'     => Mage::helper('lookbookslider')->__('Caption'),
          'style'     => 'height:100px;',
          'required'  => false,
          'name'      => 'caption',
      ));

      $fieldset->addField('position', 'text', array(
          'label'     => Mage::helper('lookbookslider')->__('Order'),
          'required'  => false,
          'name'      => 'position',
      ));
            
      $fieldset->addField('link', 'text', array(
          'label'     => Mage::helper('lookbookslider')->__('Link'),
          'required'  => false,
          'name'      => 'link',
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('lookbookslider')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('lookbookslider')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('lookbookslider')->__('Disabled'),
              ),
          ),
      ));
   
      $fieldset->addType('lookbookimage','Altima_Lookbookslider_Block_Adminhtml_Slide_Edit_Form_Element_Lookbookimage');
      $fieldset->addField('image_path', 'lookbookimage', array(
          'label'     => Mage::helper('lookbookslider')->__('Image'),
          'name'      => 'image_path',
          'required'  => true,       
      ));
      
      $fieldset->addType('hotspots','Altima_Lookbookslider_Block_Adminhtml_Slide_Edit_Form_Element_Hotspots');
      $fieldset->addField('hotspots', 'hotspots', array(
          'name'      => 'hotspots',        
      ));
      
      if ( Mage::getSingleton('adminhtml/session')->getLookbookData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getLookbookData());
          Mage::getSingleton('adminhtml/session')->setLookbookData(null);
      } elseif ( Mage::registry('slide_data') ) {
          $form->setValues(Mage::registry('slide_data')->getData());
      }
      return parent::_prepareForm();
  }
}