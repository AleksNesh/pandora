<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('upslabelprice'), 'type',
    'VARCHAR(20) DEFAULT \'shipment\''
);
$installer->endSetup();