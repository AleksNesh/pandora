<?php
/**
 * Pan_JewelryDesigner Extension
 *
 * @category  Pan
 * @package   Pan_JewelryDesigner
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

class Pan_JewelryDesigner_Model_Observer
{
    /**
     * Initialize designer UI interface
     *
     * @param   Varien_Event_Observer $observer
     * @return  void
     */
    public function designerUiInit(Varien_Event_Observer $observer)
    {
        if (Mage::helper('pan_jewelrydesigner')->inAdminArea()) {
            // check if application enabled for admins
            if (!Mage::helper('pan_jewelrydesigner')->isEnabledInAdmin()) {
                return;
            }
        } else {
            // check for application enabled for frontend users
            if (!Mage::helper('pan_jewelrydesigner')->isEnabled()) {
                return;
            }
        }

        $request    = Mage::app()->getRequest();
        $routeName  = $request->getRouteName();
        $moduleName = $request->getModuleName();

        if ($routeName == 'pan_jewelrydesigner' || $moduleName == 'jewelrydesigner') {
            // grab response object from the Mage::aop()
            $response = Mage::app()->getResponse();

            // render designer assets and inject into response body
            $assets = $this->_getLayout()
                ->createBlock('pan_jewelrydesigner/template')
                ->setTemplate('assets.phtml')
                ->toHtml();
            $this->_appendToHtmlHead($response, $assets);
        }
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

