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
class Altima_Lookbookslider_Block_Adminhtml_Slide_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'lookbookslider/adminhtml_slide_edit_form';
        $this->_controller = 'adminhtml_slide';
        $slider_id = $this->getRequest()->getParam('slider_id');
        if (!$slider_id) {
            $slider_id = Mage::registry('slide_data')->getData('lookbookslider_id');            
        }

        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/', array('slider_id' => $slider_id)) . '\')');
        $this->_updateButton('save', 'label', Mage::helper('lookbookslider')->__('Save Slide'));
        $this->_updateButton('delete', 'label', Mage::helper('lookbookslider')->__('Delete Slide')); 
        $this->_updateButton('delete', 'onclick', 'deleteConfirm(\''. Mage::helper('adminhtml')->__('Are you sure you want to do this?')
                    .'\', \'' . $this->getUrl('*/*/delete', array($this->_objectId => $this->getRequest()->getParam($this->_objectId),'slider_id' => $slider_id)). '\')');

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
                
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/slider_id/". $slider_id ."');
            }
            ";
    }

    public function getHeaderText()
    {
       $slider_id = $this->getRequest()->getParam('slider_id');
        if (!$slider_id) {
            $slider_id = Mage::registry('slide_data')->getData('lookbookslider_id');            
        }
        $slider_name = Mage::getModel('lookbookslider/lookbookslider')->load($slider_id)->getName();
        
        if( Mage::registry('slide_data') && Mage::registry('slide_data')->getId() ) {
            return Mage::helper('lookbookslider')->__("Slider &quot%s&quot. Slide &quot%s&quot.", $slider_name, $this->htmlEscape(Mage::registry('slide_data')->getName()));
        } else {
            return Mage::helper('lookbookslider')->__("Slider &quot%s&quot. New Slide.", $slider_name);
        }
    }
}