<?php
/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

class Ash_Slideshow_Model_Resource_Asset_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('ash_slideshow/asset');
    }
}
