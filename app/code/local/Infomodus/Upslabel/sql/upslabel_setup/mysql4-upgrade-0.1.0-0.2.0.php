<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('upslabel'), 'shipment_id',
    'int(11) NULL DEFAULT \'0\''
);
$installer->getConnection()->addColumn($installer->getTable('upslabel'), 'type',
    'VARCHAR(20) DEFAULT \'shipment\''
);
$installer->endSetup();