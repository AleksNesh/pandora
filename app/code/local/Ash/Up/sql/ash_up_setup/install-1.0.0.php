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
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'ash_extension'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('ash_up/extension'))
    ->addColumn('extension_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Extension Id')
    ->addColumn('extension_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Extension Name')
    ->addColumn('download_uri', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Download URI')
    ->addColumn('last_checked', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Last Checked')
    ->addColumn('last_downloaded', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Last Downloaded')
    ->addColumn('last_stability', Varien_Db_Ddl_Table::TYPE_TEXT, 30, array(
        'nullable'  => true,
        ), 'Last Stability')
    ->addColumn('last_version', Varien_Db_Ddl_Table::TYPE_TEXT, 30, array(
        'nullable'  => true,
        ), 'Last Version')
    ->addColumn('remote_version', Varien_Db_Ddl_Table::TYPE_TEXT, 30, array(
        'nullable'  => true,
        ), 'Remote Version')
    ->addColumn('installed_flag', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
        ), 'Installed Flag')
    ->addIndex($installer->getIdxName(
            $installer->getTable('ash_up/extension'),
            array('extension_name'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        array('extension_name'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
    ->addIndex($installer->getIdxName(
            $installer->getTable('ash_up/extension'),
            array('installed_flag'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        array('installed_flag'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
    ->setComment('Ash Extensions');
$installer->getConnection()->createTable($table);

$installer->endSetup();
