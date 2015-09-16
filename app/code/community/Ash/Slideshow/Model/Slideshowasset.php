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

class Ash_Slideshow_Model_Slideshowasset extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ash_slideshow/slideshowasset');
    }

    /**
     * [update_asset_order description]
     * @param  [type] $assets_order [description]
     * @return [type]               [description]
     */
    public function update_asset_order($assets_order)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $resource  = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName($this->getResourceName());

        $slideId = $assets_order['slideid'];

        if(ctype_digit($slideId)) {
            // Delete all old ordering related with this slide
            // $delete_query = "DELETE FROM $tableName WHERE `slide_id` = '$slideId'";
            // $is_affected  = $write->query($delete_query);

            if(isset($assets_order['order'])) {
                foreach ($assets_order['order'] as $order => $assetId) {
                    if(ctype_digit($assetId)) {
                        // $insert_query = "INSERT INTO $tableName (`asset_id`, `slide_id`, `asorder`) VALUES ('$assetId', '$slideId', '$order')";
                        // $is_affected  = $write->query($insert_query);
                        $update_query = "UPDATE $tableName SET `asorder` = '$order' WHERE `asset_id` = '$assetId' and `slide_id` = '$slideId'";
                        $is_affected  = $write->query($update_query);
                    }
                }
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
