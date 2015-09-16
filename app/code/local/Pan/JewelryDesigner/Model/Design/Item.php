<?php
/**
 * Pan_JewelryDesigner Extension
 *
 * @category  Pan
 * @package   Pan_JewelryDesigner
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

class Pan_JewelryDesigner_Model_Design_Item extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();

        // this refers to Pan_JewelryDesigner_Model_Resource_Item
        $this->_init('pan_jewelrydesigner/item');
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
