<?php
/**
* Ash Slideshow Extension
*
* @category  Ash
* @package   Ash_Slideshow
* @copyright Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
* @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* @author    August Ash Team <core@augustash.com>
*
**/

class Ash_Slideshow_Model_Asset extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ash_slideshow/asset');
    }

    /**
     * [update_asset_order description]
     * @param  [type] $assets_order [description]
     * @return [type]               [description]
     */
    public function update_asset_order($assets_order)
    {
        $write      = Mage::getSingleton('core/resource')->getConnection('core_write');
        $resource   = Mage::getSingleton('core/resource');
        $tableName  = $resource->getTableName($this->getResourceName());

        if(isset($assets_order['order'])) {
            foreach ($assets_order['order'] as $order => $assetId) {
               $update_query = "UPDATE $tableName SET `sort_order` = '$order' WHERE `id` = '$assetId'";
               $is_affected  = $write->query($update_query);
            }
        }
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setData('created_at', $now);
        }
        $this->setData('updated_at', $now);
        return $this;
    }

    protected function _afterSave()
    {
        return parent::_afterSave();
    }
}
