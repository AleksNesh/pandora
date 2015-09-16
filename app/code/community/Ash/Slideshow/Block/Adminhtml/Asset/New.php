<?php
/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

class Ash_Slideshow_Block_Adminhtml_Asset_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * $_helper
     * @var Ash_Slideshow_Helper_Data
     */
    protected $_helper;

    public function __construct()
    {
        parent::__construct();

        $this->_helper      = Mage::helper('ash_slideshow');
        $this->_objectId    = 'id';
        $this->_blockGroup  = 'ash_slideshow';
        $this->_controller  = 'adminhtml_asset';

        $this->_updateButton('save', 'label', $this->_helper->__('Save Asset'));
        $this->_updateButton('delete', 'label', $this->_helper->__('Delete Asset'));
    }

    public function getHeaderText()
    {
        return $this->_helper->__('Add New Asset');
    }
}
