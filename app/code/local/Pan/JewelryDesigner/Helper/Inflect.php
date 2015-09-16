<?php
/**
 * Core module for providing common functionality between BraceletBuilder and other related submodules
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Pan_JewelryDesigner string inflector helper
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @author      August Ash Team <core@augustash.com>
 */
class Pan_JewelryDesigner_Helper_Inflect extends Ash_Inflect_Helper_Data
{
    /**
     * camelize
     *
     * Makes a camel cased word (e.g., camelCasedWord) with leading character
     * lowercase by default but can be capitalized by passing true for
     * $uppercase_first_letter.
     *
     * Inverse of underscore
     *
     * @param  string   $term
     * @param  boolean  $uppercase_first_letter
     * @return string
     */
    public function camelize($term, $uppercase_first_letter = false)
    {
        $result = str_replace(' ', '', ucwords(preg_replace('/[^a-z0-9]+/i',' ', $term)));

        if (!$uppercase_first_letter) {
            $result[0] = strtolower($result[0]);
        }

        return $result;
    }

    /**
     * underscore
     *
     * Makes an underscored, lowercase form from the expression in the string.
     *
     * Inverse of camelize
     *
     * @param  string   $camel_cased_word
     * @return string
     */
    public function underscore($camel_cased_word)
    {
        $camel_cased_word = preg_replace('#([A-Z\d]+)([A-Z][a-z])#','\1_\2', $camel_cased_word);
        $camel_cased_word = preg_replace('#([a-z\d])([A-Z])#', '\1_\2', $camel_cased_word);

        return strtolower(strtr($camel_cased_word, '-', '_'));
    }

    /**
     * slugify
     *
     * Replace a string's whitespace or non-alphanumeric characters with '-'
     *
     * Examples:
     *
     * + 'A Quick Brown Fox' => 'a-quick-brown-fox'
     * + 'aaaBBcc#$!12%3' => 'aaabbcc-12-3'
     *
     * @param   string  $text
     * @return  string
     */
    public function slugify($text, $glue = '-')
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', $glue, $text);
        // trim
        $text = trim($text, $glue);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // if no remaining text is left then return a random unique identifier
        if (empty($text)) {
            return $this->randomUUID();
        }

        return $text;
    }

    /**
     * Generates a random uuid string
     *
     * @param string $file_name
     * @return string
     */
    public function randomUUID()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
    }
}
