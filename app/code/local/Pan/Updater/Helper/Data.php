<?php

/**
 * Simple module for synchronizing system configuration and database changes.
 *
 * @category    Pan
 * @package     Pan_Updater
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_Updater_Helper_Data extends Mage_Core_Helper_Data
{
    public function getConfig($path, $store = 0)
    {
        /**
         * Retrieve the read connection
         */
        $resource       = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $query = <<<SQL_QUERY
SELECT ccd.value FROM core_config_data ccd WHERE ccd.path = '{$path}' AND ccd.scope_id = {$store};
SQL_QUERY;

        $results = $readConnection->fetchOne($query);

        return $results;
    }
}
