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
 * AJAX Controller
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Cleaning Cache
     *
     * @return void
     */
    public function cleancacheAction()
    {
        Mage::helper('ash_bar/cache')->clean();
        $this->_sendHtml('<strong>Cache Cleared!</strong>');
    }

    /**
     * Enable/Disable Cache
     *
     * @return void
     */
    public function toggleCacheAction()
    {
        $status   = !(Mage::helper('ash_bar/cache')->isEnabled());
        $allTypes = Mage::app()->useCache();

        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            if ($status) {
                $allTypes[$type->getId()] = 1;
            } else {
                $allTypes[$type->getId()] = 0;
                $tags = Mage::app()->getCacheInstance()->cleanType($type->getId());
            }
        }
        Mage::app()->saveUseCache($allTypes);

        // reload page
        $this->_redirectReferer();
    }

    /**
     * Enable/Disable Logging
     *
     * @return void
     */
    public function toggleLogsAction()
    {
        // get and set the opposite of the current value
        $status = !((boolean)Mage::getStoreConfig('dev/log/active'));
        Mage::getConfig()->saveConfig('dev/log/active', $status);

        // send updated button text
        if ($status) {
            $this->_sendHtml('Disable Logging');
        } else {
            $this->_sendHtml('Enable Logging');
        }
    }

    /**
     * Enable/Disable Template Hints
     *
     * @return void
     */
    public function toggleHintsAction()
    {
        // get and set the opposite of the current value
        $status = !((boolean)Mage::getStoreConfig('dev/debug/template_hints'));
        Mage::getConfig()->saveConfig('dev/debug/template_hints',
            $status,
            'stores',
            Mage::app()->getStore()->getStoreId()
        );
        Mage::getConfig()->saveConfig('dev/debug/template_hints_blocks',
            $status,
            'stores',
            Mage::app()->getStore()->getStoreId()
        );
        Mage::helper('ash_bar/cache')->clean();

        // reload page
        $this->_redirectReferer();
    }

    /**
     * Send correct headers and formated data for HTML
     *
     * @param  string $html
     * @return void
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
     * @param  mixed $data
     * @return void
     */
    protected function _sendJson($data)
    {
        header('Content-Type: application/json');
        echo Zend_Json::encode($data);
        exit;
    }
}
