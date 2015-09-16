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

class Pan_JewelryDesigner_Model_Resource_Design extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        // this refers to pan_jewelrydesigner_resource->entities node in the config.xml
        $this->_init('pan_jewelrydesigner/design', 'id');
    }
}
