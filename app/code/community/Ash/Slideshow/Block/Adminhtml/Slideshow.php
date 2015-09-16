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

class Ash_Slideshow_Block_Adminhtml_Slideshow extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * $_helper
     * @var Ash_Slideshow_Helper_Data
     */
    protected $_helper;

    public function __construct()
    {
        $this->_helper          = Mage::helper('ash_slideshow');
        $this->_controller      = 'adminhtml_slideshow';
        $this->_blockGroup      = 'ash_slideshow';
        $this->_headerText      = $this->_helper->__('Slide Shows Manager');
        $this->_addButtonLabel  = $this->_helper->__('Add New Slide Show');
        parent::__construct();
    }
}
