<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('upslabelpickup'), 'store',
    'int(11) DEFAULT 1'
);
$installer->endSetup();