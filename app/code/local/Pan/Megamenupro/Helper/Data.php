<?php

/**
 * Simple module for extending core EM_Megamenupro module
 *
 * @category    Pan
 * @package     Pan_Megamenupro
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */
class Pan_Megamenupro_Helper_Data extends EM_Megamenupro_Helper_Data
{
    public function isSerialized($str)
    {
        return ($str == serialize(false) || @unserialize($str) !== false);
    }

}
