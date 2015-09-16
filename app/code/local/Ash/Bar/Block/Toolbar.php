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
 * Toolbar Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Toolbar extends Ash_Bar_Block_Template
{
    /**
     * Tab's configuration XML
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
     * Retrieve module's tab configuration
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
     * Retrieve an array of Tab blocks from config XML
     *
     * @return array
     */
    public function getTabs()
    {
        if (empty($this->_tabs)) {
            foreach ($this->getConfig()->xpath('*') as $_node) {
                // create an instance of the block and configure it
                $block = $this->getLayout()->createBlock((string)$_node->block);
                $block->setId($_node->getName());
                $block->setTitle((string)$_node->title);

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
}
