<?php
/**
 *
 * @category   Directshop
 * @package    Directshop_FraudDetection
 * @author     Ben James
 * @copyright  Copyright (c) 2008-2010 Directshop Pty Ltd. (http://directshop.com.au)
 */
$installer = $this;
$installer->startSetup();

/*$installer->run("
DROP TABLE IF EXISTS {$this->getTable('frauddetection_data')};
CREATE TABLE {$this->getTable('frauddetection_data')} (
  `entity_id` int(10) NOT NULL auto_increment,
  `order_id` int(10) NOT NULL,
  `fraud_score` int(11) NULL DEFAULT '0',
  `fraud_data` text NULL,
  `sent_data` text NULL,
  PRIMARY KEY  (`entity_id`),
  KEY `order_id_idx` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

// transfer any old data across
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
if ($setup->getEntityType('order_payment') !== false)
{
	$responseAttribute = $setup->getAttribute('order_payment', 'maxmind_response');
	$scoreAttribute = $setup->getAttribute('order', 'fraud_score');
	if ($responseAttribute && $setup->tableExists('sales_order_entity_text'))
	{
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$existingData = $read->fetchAll("select * from {$this->getTable('sales_order_entity_text')} where attribute_id = ?", array($responseAttribute['attribute_id']));
		foreach ($existingData as $data)
		{
			$parentId = $read->fetchOne("select parent_id from {$this->getTable('sales_order_entity')} where entity_id = ?", array($data['entity_id']));
			$fraudScore = 0;
			if ($scoreAttribute)
			{
				$fraudScore = $read->fetchOne("select value from {$this->getTable('sales_order_int')} where entity_id = ? AND attribute_id = ?", array($parentId, $scoreAttribute['attribute_id']));
			}
			else
			{
				$fraudData = @unserialize($data['value']);
				if (isset($fraudData['ourscore']))
				{
					$fraudScore = $fraudData['ourscore'];
				}
			}
			
			if ($fraudScore !== FALSE && $parentId)
			{
				$result = Mage::getModel('frauddetection/result')->loadByOrderId($data['entity_id'])->addData(array(
					'order_id'  => $parentId,
					'fraud_score' => $fraudScore,
					'fraud_data' => $data['value']
				))->save();
				
				//$installer->run("INSERT INTO {$this->getTable('frauddetection_data')} (order_id, fraud_score, fraud_data) VALUES ($parentId, $fraudScore, '{$data['value']}');");
			}
		}
	}
}*/

$installer->endSetup();