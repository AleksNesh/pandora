<?php

/**
 * Simple module for adding ability to do string inflection
 * for pluralizing or singularizing strings.
 *
 * @category    Ash
 * @package     Ash_Inflect
 * @copyright   Copyright (c) 2013 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */
class Ash_Inflect_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_inflector;

    public function pluralize($string)
    {
        return  $this->getInflector()->pluralize($string);
    }

    public function singularize($string)
    {
        return  $this->getInflector()->singularize($string);
    }

    public function pluralize_if($count, $string)
    {
        return  $this->getInflector()->pluralize_if($count, $string);
    }

    public function getInflector()
    {
        if (is_null($this->_inflector)) {
            $this->_inflector = Mage::getSingleton('ash_inflect/inflect');
        }
        return $this->_inflector;
    }
}
