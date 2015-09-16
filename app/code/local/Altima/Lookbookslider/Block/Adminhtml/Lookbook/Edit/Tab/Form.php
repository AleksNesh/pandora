<?php
/**
 * Altima Lookbook Free Extension
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
 * @category   Altima
 * @package    Altima_LookbookFree
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Altima_Lookbook_Block_Adminhtml_Lookbook_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();

      $this->setForm($form);

      $fieldset = $form->addFieldset('lookbook_form', array('legend'=>Mage::helper('lookbook')->__('Lookbook slide information')));
     
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('lookbook')->__('Name'),
          'required'  => true,
          'name'      => 'name',
      ));
 
      $fieldset->addField('position', 'text', array(
          'label'     => Mage::helper('lookbook')->__('Order'),
          'required'  => false,
          'name'      => 'position',
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('lookbook')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('lookbook')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('lookbook')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addType('lookbookimage','Altima_Lookbook_Block_Adminhtml_Lookbook_Edit_Form_Element_Lookbookimage');
      $fieldset->addField('image', 'lookbookimage', array(
          'label'     => Mage::helper('lookbook')->__('Image'),
          'name'      => 'image',
          'required'  => true,       
      ));
      
      $fieldset->addType('hotspots','Altima_Lookbook_Block_Adminhtml_Lookbook_Edit_Form_Element_Hotspots');
      $fieldset->addField('hotspots', 'hotspots', array(
          'name'      => 'hotspots',        
      ));
      
      if ( Mage::getSingleton('adminhtml/session')->getLookbookData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getLookbookData());
          Mage::getSingleton('adminhtml/session')->setLookbookData(null);
      } elseif ( Mage::registry('lookbook_data') ) {
          $form->setValues(Mage::registry('lookbook_data')->getData());
      }
      return parent::_prepareForm();
  }
}