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
class Altima_Lookbookslider_Block_Adminhtml_Lookbookslider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $_model = Mage::registry('lookbookslider_data');
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('lookbookslider_form', array('legend'=>Mage::helper('lookbookslider')->__('Slider Information')));
      
      $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
            'add_widgets' => false,
            'add_variables' => false, 
            'add_images' => true,
            'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
            'files_browser_window_width' => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'),
            'files_browser_window_height'=> (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height'),
            'encode_directives' => true, 
            'directives_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
            'directives_url_quoted' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'), 
      )); 
      
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('lookbookslider')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));
		
       $fieldset->addField('position', 'select', array(
            'label'     => Mage::helper('lookbookslider')->__('Position'),
            'name'      => 'position',
            'values'    => Mage::getSingleton('lookbookslider/position')->toOptionArray(),
            'value'     => $_model->getPosition()
        ));
        
        $fieldset->addField('width', 'text', array(
            'label'     => Mage::helper('lookbookslider')->__('Slider Width (px)'),
            'name'      => 'width',
            'class'     => 'required-entry validate-digits validate-greater-than-zero',
            'required'  => true,
        ));
        
        $fieldset->addField('height', 'text', array(
            'label'     => Mage::helper('lookbookslider')->__('Slider Height (px)'),
            'name'      => 'height',
            'class'     => 'required-entry validate-digits validate-greater-than-zero',
            'required'  => true,
        ));

       $fieldset->addField('effect', 'multiselect', array(
            'label'     => Mage::helper('lookbookslider')->__('Transition effect'),
            'name'      => 'effect[]',
            'values'    => Mage::getSingleton('lookbookslider/config_source_slider_effect')->getAllOptions(),
            'value'     => $_model->getEffect(),
            'note'      => Mage::helper('lookbookslider')->__('You can use more than one effect or leave empty to use the random effect.'), 
        ));

      $fieldset->addField('navigation', 'select', array(
          'label'     => Mage::helper('lookbookslider')->__('Show navigation'),
          'name'      => 'navigation',
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
          'note'    => Mage::helper('lookbookslider')->__('If YES the navigation button (prev, next and play/stop buttons) will be visible, if NO they will be always hidden'),
      ));

      $fieldset->addField('navigation_hover', 'select', array(
          'label'     => Mage::helper('lookbookslider')->__('Navigation on hover state only'),
          'name'      => 'navigation_hover',
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
          'note'    => Mage::helper('lookbookslider')->__('If YES the navigation button (prev, next and play/stop buttons) will be visible on hover state only, if NO they will be visible always'),
      ));

      $fieldset->addField('thumbnails', 'select', array(
          'label'     => Mage::helper('lookbookslider')->__('Show thumbnails'),
          'name'      => 'thumbnails',
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
          'note'    => Mage::helper('lookbookslider')->__('If YES the thumbnails will be visible, if NO will show the pagination'),
      ));
      
      $fieldset->addField('time', 'text', array(
            'label'     => Mage::helper('lookbookslider')->__('Pause'),
            'name'      => 'time',
            'class'     => 'required-entry validate-digits validate-greater-than-zero',
            'required'  => true,
            'note'      => Mage::helper('lookbookslider')->__('Milliseconds between the end of the sliding effect and the start of the nex one'),
      ));      

      $fieldset->addField('trans_period', 'text', array(
            'label'     => Mage::helper('lookbookslider')->__('Transition duration'),
            'name'      => 'trans_period',
            'class'     => 'required-entry validate-digits validate-greater-than-zero',
            'required'  => true,
            'note'      => Mage::helper('lookbookslider')->__('Length of the sliding effect in milliseconds'),
      ));  
                                           
      $fieldset->addField('contentbefore', 'editor', array(
          'name'      => 'contentbefore',
          'label'     => Mage::helper('lookbookslider')->__('Content Before'),
          'style'     => 'width:600px; height:300px;',
          'wysiwyg'   => true,
          'config'    => $wysiwygConfig, 
          'required'  => false,
          'note'      => Mage::helper('lookbookslider')->__('This content will be shown before slider'),
      ));
         
      $fieldset->addField('contentafter', 'editor', array(
          'name'      => 'contentafter',
          'label'     => Mage::helper('lookbookslider')->__('Content After'),
          'style'     => 'width:600px; height:300px;',
          'wysiwyg'   => true,
          'config'    => $wysiwygConfig, 
          'required'  => false,
          'note'      => Mage::helper('lookbookslider')->__('This content will be shown after slider'),
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
      
      $fieldset->addField('showslidenames', 'select', array(
          'label'     => Mage::helper('lookbookslider')->__('Show Slide Caption'),
          'name'      => 'showslidenames',
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
          'note'      => Mage::helper('lookbookslider')->__('If YES will show slide caption at the slider bottom'),
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