<?php

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer  = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('megamenupro/megamenupro'),
    'css_class',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'     => 200,
        'nullable'  => true,
        'comment'   => 'CSS Class'
    )
);
