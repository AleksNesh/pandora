<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */

/**
 * Adminhtml system config array field renderer
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 */
class Directshop_FraudDetection_Block_Adminhtml_System_Config_Form_Field_Ipexceptions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('ipaddress', array(
            'label' => Mage::helper('adminhtml')->__('IP Address'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Exception');
        parent::__construct();
    }
}