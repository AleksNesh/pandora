<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('upslabel'), 'rva_printed',
    'int(11) DEFAULT 0'
);
$installer->endSetup();