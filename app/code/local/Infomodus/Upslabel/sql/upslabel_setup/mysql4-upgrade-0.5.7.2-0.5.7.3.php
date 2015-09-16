<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('upslabelconformity'), 'country_hash',
    'varchar(32)'
);
$installer->getConnection()->addColumn($installer->getTable('upslabelconformity'), 'international',
    'tinyint(1)'
);
$tableName = $installer->getTable('upslabelconformity');
// Check if the table already exists
if ($installer->getConnection()->isTableExists($tableName)) {
    $table = $installer->getConnection();

    $table->addIndex(
        $tableName,
        "unqconformity",
        array(
            'method_id',
            'international',
            'country_hash',
            'store_id',
        ),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
}
$installer->endSetup();
