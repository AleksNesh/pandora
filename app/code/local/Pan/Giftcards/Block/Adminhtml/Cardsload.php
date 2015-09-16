<?php
/**
 * Simple module for custom override of giftcard imports.
 *
 * @category    Pan
 * @package     Pan_Giftcards
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pan_Giftcards_Block_Adminhtml_Cardsload extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'pan_giftcards';
        $this->_controller = 'adminhtml_cardsload';

        $this->_updateButton('save', 'label', Mage::helper('giftcards')->__('Import Gift Cards'));
    }

    public function getHeaderText()
    {
        return Mage::helper('giftcards')->__('Import Gift Cards');
    }
}
