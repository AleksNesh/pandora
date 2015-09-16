<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('upslabelconformity'), 'country_ids',
    'text'
);
$installer->endSetup();
