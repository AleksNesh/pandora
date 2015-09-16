<?php
/**
 * Ash Core Extension
 *
 * Maintains common settings and configuration for AAI-built Magento websites.
 *
 * @category    Ash
 * @package     Ash_Core
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System config information block
 *
 * @category    Ash
 * @package     Ash_Core
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Core_Block_System_Config_Info extends Mage_Adminhtml_Block_Abstract
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
        $html = '<div style="background:url(\'http://augustash.s3.amazonaws.com/logo.png\') no-repeat scroll 15px center #fff;border:1px solid #CCCCCC;margin-bottom:10px;padding:10px 5px 5px 235px;">
                    <h4>August Ash Support</h4>
                    <p>Contact our Client Services team at (952) 851-9400 or toll-free at (877) 734-4485.</p>
                    <p>Visit our website: <a href="http://www.augustash.com" target="_blank">www.augustash.com</a></p>
                    <p><a href="https://augustash.zendesk.com/anonymous_requests/new" target="_blank">Submit a support request</a></p>
                </div>';

        return $html;
    }
}
