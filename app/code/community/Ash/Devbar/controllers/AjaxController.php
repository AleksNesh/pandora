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
 * Ajax Controller
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Clean Cache
     *
     * @return  void
     */
    public function cleanCacheAction()
    {
        $this->_sendHtml(Mage::getModel('ash_devbar/ajax_cleancache')
             ->handleRequest());
    }

    /**
     * Enable/Disable Cache
     *
     * @return  void
     */
    public function toggleCacheAction()
    {
        $this->_sendJson(Mage::getModel('ash_devbar/ajax_togglecache')
             ->handleRequest());
    }

    /**
     * Enable/Disable Logging
     *
     * @return  void
     */
    public function toggleLogsAction()
    {
        $this->_sendJson(Mage::getModel('ash_devbar/ajax_togglelogs')
             ->handleRequest());
    }

    /**
     * Enable/Disable Template Hints
     *
     * @return  void
     */
    public function toggleHintsAction()
    {
        $this->_sendJson(Mage::getModel('ash_devbar/ajax_togglehints')
             ->handleRequest());
    }

    /**
     * Enable/Disable Template Block Names
     *
     * @return  void
     */
    public function toggleBlocksAction()
    {
        $this->_sendJson(Mage::getModel('ash_devbar/ajax_toggleblocks')
             ->handleRequest());
    }

    /**
     * Send correct headers and formated data for HTML
     *
     * @param   string $html
     * @return  void
     */
    protected function _sendHtml($html)
    {
        header('Content-Type: text/html');
        echo $html;
        exit;
    }

    /**
     * Send correct headers and formated data for JSON
     *
     * @param   mixed $data
     * @return  void
     */
    protected function _sendJson($data)
    {
        header('Content-Type: application/json');
        echo Zend_Json::encode($data);
        exit;
    }
}
