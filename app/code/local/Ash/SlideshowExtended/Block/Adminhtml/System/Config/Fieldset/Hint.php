<?php
/**
 * Ash Validate Credit Card Extension
 *
 * Validate & recognize credit credit card type via IIN (issuer identification number)
 *
 * @category    Ash
 * @package     Ash_SlideshowExtended
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System config information block
 *
 * @category    Ash
 * @package     Ash_SlideshowExtended
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_SlideshowExtended_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render fieldset html
     *
     * @param   Varien_Data_Form_Element_Abstract $element
     * @return  string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<div style="background:url(\'http://augustash.s3.amazonaws.com/logo.png\') no-repeat scroll 15px center #fff;border:1px solid #CCCCCC;margin-bottom:10px;padding:10px 40px 5px 235px;">
                    <h4>Ash Slideshow (Extended)</h4>
                    <p>Client-specific customizations to the community version of Ash_Slideshow.</p>
                </div>';

        return $html;
    }
}
