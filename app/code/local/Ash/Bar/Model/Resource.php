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
 * Resource model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Model_Resource extends Mage_Core_Model_Resource
{
    /**
     * Creates a connection to resource whenever needed
     *
     * @param  string $name
     * @return Varien_Db_Adapter_Interface
     */
    public function getConnection($name)
    {
        $connection = parent::getConnection($name);

        /*
         * Make sure the profiler is enabled for all requests
         */
        $connection->getProfiler()->setEnabled(true);
        return $connection;
    }
}
