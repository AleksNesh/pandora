<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer model
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Model_Observer
{
    /**
     * Initialize toolbar interface
     *
     * @param   Varien_Event_Observer $observer
     * @return  void
     */
    public function toolbarInit(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('ash_devbar')->isEnabled()) {
            return;
        }

        $response = $observer->getResponse();

        /*
         * Render toolbar assets and inject into response body
         *
         * This is done outside the normal Magento layout routine to avoid
         * template hints being applied and also so front-end and admin can easily
         * share the same front-end files.
         */
        $templates = array('styles.phtml', 'scripts.phtml');
        foreach ($templates as $_template) {
            $_block = Mage::getSingleton('core/layout')
                ->createBlock('ash_devbar/template')
                ->setTemplate($_template);
            $this->_appendToResponseHtmlTag('head', $response, $_block->toHtml());
        }

        // pass in collected data
        $this->_appendToResponseHtmlTag('body', $response, $this->_getCollectedData());
    }

    /**
     * Append arbitrary content to the passed HTML element within the response.
     *
     * @todo    May be problematic, consider using loadHTML of dom document
     *
     * @param   string $tag
     * @param   Mage_Core_Controller_Response_Http $response
     * @param   string $content
     * @return  void
     */
    protected function _appendToResponseHtmlTag($tag, Mage_Core_Controller_Response_Http $response, $content)
    {
        $response->setBody(
            str_replace('</' . $tag . '>', $content . '</' . $tag . '>', $response->getBody(false))
        );
    }

    /**
     * Get collected data and render as a JSON variable. Most of the toolbar is
     * dependent on this data. This is rendered at the body of the HTML page and
     * is passed into "toolbar.js" in "scripts.phtml".
     *
     * @return  string
     */
    protected function _getCollectedData()
    {
        // retrieve all the system data
        $collector = Mage::getSingleton('ash_devbar/collector');

        // returned as a string to avoid sending raw JavaScript to browser
        $html  = '';
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "var collectedJsonData = '"
               . str_replace("\\", "\\\\", $collector->toJson()) ."';\n";
        $html .= "</script>\n";

        return $html;
    }
}
