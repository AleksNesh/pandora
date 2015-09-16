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
class Altima_Lookbookslider_Block_Adminhtml_Slide extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_slide';
    $this->_blockGroup = 'lookbookslider/adminhtml_slide_grid';
    $slider_name = Mage::registry('slider_data')->getName();
    $slider_id = Mage::registry('slider_data')->getId();    
    $this->_headerText = Mage::helper('lookbookslider')->__("Manage %s slides",$slider_name);
        $this->_addButton('back', array(
            'label'     => Mage::helper('lookbookslider')->__("Back to slider list"),
            'onclick'   => 'setLocation(\'' . $this->getUrl('lookbookslider/adminhtml_lookbookslider') .'\')',
            'class'     => 'back',
    ));

    parent::__construct();
    
    $this->_addButton('add', array(
            'label'     => Mage::helper('lookbookslider')->__('Add Slide'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/new/', array('slider_id' => $slider_id)) .'\')',
            'class'     => 'add',
    ));
  }
}