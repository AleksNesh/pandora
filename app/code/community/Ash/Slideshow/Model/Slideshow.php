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

class Ash_Slideshow_Model_Slideshow extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ash_slideshow/slideshow');
    }

    public function getSlidesForSlideshow($slideshowId)
    {
        $collection = Mage::getModel('ash_slideshow/asset')->getCollection();
        $collection->addFieldToFilter('status', array('eq' => 1));

        // join slideshow assets mapped to slideshows
        $joinTableName = Mage::getSingleton('core/resource')->getTableName('ash_slideshow/slideshowasset');
        $collection->getSelect()->join(array('t2' => $joinTableName), 'main_table.id = t2.asset_id', array());

        // only get assets that belong to our selected slideshow (determined by the join table)
        $collection->addFieldToFilter('t2.slide_id', array('eq' => $slideshowId));

        // order the assets by the join table's 'asorder' column
        $collection->getSelect()->order('t2.asorder ASC');

        return $collection;
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
