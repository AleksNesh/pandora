<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('upslabel'), 'type_print',
    'varchar(11) DEFAULT "GIF"'
);
$installer->endSetup();