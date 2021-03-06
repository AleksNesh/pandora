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
 * Extension collection model
 *
 * @category    Ash
 * @package     Ash_Up
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Up_Model_Resource_Extension_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
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
     * Filter collection by installed
     *
     * @return Ash_Up_Model_Resource_Extension_Collection
     */
    public function addInstalledFilter()
    {
        $this->getSelect()->where('main_table.installed_flag = 1');
        return $this;
    }

    /**
     * Filter collection by not installed
     *
     * @return Ash_Up_Model_Resource_Extension_Collection
     */
    public function addAvailableFilter()
    {
        $this->getSelect()->where('main_table.installed_flag != 1');
        return $this;
    }
}
