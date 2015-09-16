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

class Ash_Slideshow_Model_Resource_Slideshowasset_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('ash_slideshow/slideshowasset');
    }

    public function delete($slide_id)
    {
        $this->addFieldToFilter('slide_id', $slide_id);
        foreach ($this->getItems() as $k => $item) {
            $item->delete();
        }
        return $this;
    }

    public function addAssets($slideId, $assets)
    {
        $justNow = date('Y-m-d H:i:s', time());

        foreach ($assets as $assetId) {
            $newAsset = Mage::getModel('ash_slideshow/slideshowasset');

            $newAsset->setData('slide_id',   $slideId);
            $newAsset->setData('asset_id',   0);
            $newAsset->setData('updated_at', $justNow);
            $newAsset->setData('created_at', $justNow);
            if(ctype_digit($assetId)) {
                $newAsset->setData('asset_id', $assetId);
                $newAsset->save();
            }
        }
    }
}
