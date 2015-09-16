<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Bar
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Model_Observer
{
    /**
     * Initialize toolbar interface
     *
     * @param   Varien_Event_Observer $observer
     * @return  void
     */
    public function toolbarInit(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('ash_bar')->isEnabled()) {
            return;
        }

        // grab response object from observer event
        $response = $observer->getResponse();

        // render toolbar assets and inject into response body
        $assets   = $this->_getLayout()
            ->createBlock('ash_bar/template')
            ->setTemplate('assets.phtml')
            ->toHtml();
        $this->_appendToHtmlHead($response, $assets);

        // render toolbar HTML and inject into response body
        $toolbar  = $this->_getLayout()
            ->createBlock('ash_bar/toolbar')
            ->setTemplate('toolbar.phtml')
            ->toHtml();
        $this->_prependToHtmlBody($response, $toolbar);

        // pass in collected data
        $this->_appendToHtmlBody($response, $this->_getCollectedData());
    }

    /**
     * Check if Toolbar is enabled and that IP restrictions aren't in place.
     *
     * @return  boolean
     */
    protected function _isEnabled()
    {
        $allow = true;

        // check module setting
        if (!Mage::helper('ash_bar')->isEnabled()) {
            $allow = false;
        }

        // check IP restrictions
        $allowedIps = Mage::getStoreConfig(Ash_Bar_Helper_Data::XML_PATH_ALLOWED_IPS);
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search($remoteAddr, $allowedIps) === false
                && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {
                $allow = false;
            }
        }

        return $allow;
    }

    /**
     * Get collected data and render as JSON variable. Most of the toolbar is
     * dependent on this data.
     *
     * @return  string
     */
    protected function _getCollectedData()
    {
        // retrieve all the system data
        $collector = Mage::getSingleton('ash_bar/collector');

        // returned as a string to avoid sending raw JavaScript to browser
        $html  = '';
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "var collectedData = '"
               . str_replace("\\", "\\\\", $collector->toJson()) ."';\n";
        $html .= "</script>\n";

        return $html;
    }

    /**
     * Prepend arbitrary content to the DOM's <body> element.
     *
     * @param   Mage_Core_Controller_Response_Http $response
     * @param   string $content
     * @return  void
     */
    protected function _prependToHtmlBody(Mage_Core_Controller_Response_Http $response, $content)
    {
        // using string replacment in case document is poorly formed
        $response->setBody(
            preg_replace('{(</head>\s*?<body.*?>)}i', '$1' . $content, $response->getBody())
        );
    }

    /**
     * Append arbitrary content to the DOM's <body> element.
     *
     * @param   Mage_Core_Controller_Response_Http $response
     * @param   string $content
     * @return  void
     */
    protected function _appendToHtmlBody(Mage_Core_Controller_Response_Http $response, $content)
    {
        $this->_appendToHtmlTag('body', $response, $content);
    }

    /**
     * Append arbitrary content to the DOM's <head> element.
     *
     * @param   Mage_Core_Controller_Response_Http $response
     * @param   string $content
     * @return  void
     */
    protected function _appendToHtmlHead(Mage_Core_Controller_Response_Http $response, $content)
    {
        $this->_appendToHtmlTag('head', $response, $content);
    }

    /**
     * Append arbitrary content to the passed HTML element.
     *
     * @param   string $tag
     * @param   Mage_Core_Controller_Response_Http $response
     * @param   string $content
     * @return  void
     */
    protected function _appendToHtmlTag($tag, Mage_Core_Controller_Response_Http $response, $content)
    {
        $response->setBody(
            str_replace('</' . $tag . '>', $content . '</' . $tag . '>', $response->getBody(false))
        );
    }

    /**
     * Grab layout object
     *
     * @return  Mage_Core_Model_Layout
     */
    protected function _getLayout()
    {
        return Mage::getSingleton('core/layout');
    }
}
