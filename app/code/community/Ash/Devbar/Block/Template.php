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
 * Abstract Block
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Block_Template extends Mage_Core_Block_Template
{
    /**
     * Tab configuration XML
     *
     * @var Mage_Core_Model_Config_Element
     */
    protected $_config = null;

    /**
     * Internal array of loaded tabs
     *
     * @var array
     */
    protected $_tabs = array();

    /**
     * Retrieve the base URL for extension assets
     *
     * @return  string
     */
    public function getAssetBaseUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . 'ash_devbar/';
    }

    /**
     * Retrieve block view from file (template)
     *
     * @param   string $fileName
     * @return  string
     */
    public function fetchView($fileName)
    {
        $module_dir = Mage::getModuleDir('', 'Ash_Devbar');
        $this->setScriptPath($module_dir . '/Template');
        return parent::fetchView($this->getTemplate());
    }

    /**
     * Prevent template hints from showing on toolbar templates
     *
     * @return  boolean
     */
    public function getShowTemplateHints()
    {
        return false;
    }

    /**
     * Set template location directory
     *
     * @param   string $dir
     * @return  Ash_Devbar_Block_Abstract
     */
    public function setScriptPath($dir)
    {
        $scriptPath = realpath($dir);
        if ($this->_checkValidScriptPath($scriptPath)) {
            $this->_viewDir = $dir;
        } else {
            Mage::log('Not valid script path:' . $dir, Zend_Log::CRIT, null, null, true);
        }
        return $this;
    }

    /**
     * Validate script path
     *
     * @param   string $scriptPath
     * @return  boolean
     */
    protected function _checkValidScriptPath($scriptPath)
    {
        $paths = array(Mage::getBaseDir('design'), Mage::getModuleDir('', 'Ash_Devbar'));
        $valid = false;
        foreach($paths as $path) {
            if(strpos($scriptPath, realpath($path)) === 0 || $this->_getAllowSymlinks()) {
                $valid = true;
            }
        }
        return $valid;
    }

    /**
     * Retrieve toolbar tab configuration
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = Mage::getConfig()
                ->loadModulesConfiguration('toolbar.xml')
                ->getNode('tabs');
        }

        return $this->_config;
    }

    /**
     * Retrieve an array of tab blocks from config XML
     *
     * @return array
     */
    public function getTabs()
    {
        if (!$this->getConfig()) {
            return array();
        }

        if (empty($this->_tabs)) {
            $nodes = $this->getConfig()->xpath('*');
            foreach ($nodes as $_node) {
                // create an instance of the block and configure it
                $block = $this->getLayout()->createBlock((string)$_node->block);
                $block->setId($_node->getName());
                if ($_node->label) {
                    $block->setLabel((string)$_node->label);
                }

                // set any arbitrary options -- like Icon
                if ($_node->options) {
                    foreach($_node->options->asArray() as $id => $value) {
                        $block->setData($id, $value);
                    }
                }
                $this->_tabs[] = $block;
            }
        }

        return $this->_tabs;
    }

    /**
     * Retrieve an array of tab blocks in JSON format for use within front-end
     * JavaScript.
     *
     * @return  string
     */
    public function getTabsAsJson()
    {
        $data = array();
        foreach ($this->getTabs() as $_tab) {
            $obj        = new StdClass;
            $obj->id    = $_tab->getId();
            $obj->label = $_tab->renderLabel();
            $obj->html  = $_tab->toHtml();
            $obj->css   = $_tab->getCssClasses();
            $data[]     = $obj;
        }

        return Zend_Json::encode($data);
    }
}
