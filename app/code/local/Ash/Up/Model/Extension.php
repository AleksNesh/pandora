<?php
/**
 * Ash Up Extension
 *
 * Management interface for keeping Ash core extensions updated.
 *
 * @category    Ash
 * @package     Ash_Up
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extension model
 *
 * @category    Ash
 * @package     Ash_Up
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Up_Model_Extension extends Mage_Core_Model_Abstract
{
    /**
     * Internal constructor
     *
     * @return  void
     */
    protected function _construct()
    {
        $this->_init('ash_up/extension');
    }

    /**
     * Load extension model using name
     *
     * @param   string $name
     * @return  Ash_Up_Model_Extension
     */
    public function loadByName($name)
    {
        $this->setData('extension_name', $name);
        return $this->load($name, 'extension_name');
    }

    /**
     * Retrieve the locally installed extension's version number
     *
     * @return  string
     */
    public function getLocalVersion()
    {
        return (string)Mage::getConfig()->getNode("modules/{$this->getExtensionName()}/version");
    }
}
