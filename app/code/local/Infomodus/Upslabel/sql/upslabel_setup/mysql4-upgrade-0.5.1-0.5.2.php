<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('upslabel'), 'statustext',
    'TEXT'
);
$installer->endSetup();