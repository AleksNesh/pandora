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
class Altima_Lookbookslider_Block_Adminhtml_Lookbookslider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
          
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'lookbookslider';
        $this->_controller = 'adminhtml_lookbookslider';
        
        $this->_updateButton('save', 'label', Mage::helper('lookbookslider')->__('Save Slider'));
        $this->_updateButton('delete', 'label', Mage::helper('lookbookslider')->__('Delete Slider'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            Event.observe(window, 'load', function() {
                var userAgent = navigator.userAgent.toLowerCase();
                is_mozilla = /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent );
                if (is_mozilla) setTimeout('resize_contentbefore()', 2000);                        
            });
       
            function resize_contentbefore(){
                obj = $('contentafter_ifr');                                   
                    var height = obj.getHeight()+1;                    
                    $('contentbefore_ifr').setStyle({height: height + 'px'});                 
            }
                                                             
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('lookbookslider_data') && Mage::registry('lookbookslider_data')->getId() ) {
            return Mage::helper('lookbookslider')->__("Edit Slider '%s'", $this->htmlEscape(Mage::registry('lookbookslider_data')->getName()));
        } else {
            return Mage::helper('lookbookslider')->__('Add Slider');
        }
    }
}