<?php
/**
 * Extend/Override Infomodus_Upslabel module
 *
 * @category    Pan_Infomodus
 * @package     Pan_Infomodus_Upslabel
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pan_Infomodusupslabel_Helper_Help extends Infomodus_Upslabel_Helper_Help
{
    /**
     * escapeXML
     * @param  string   $string
     * @param  string   $encoding # character encoding, best to leave it at 'UTF-8'
     * @return string
     */
    static public function escapeXML($string, $encoding = 'UTF-8')
    {
        $string = preg_replace('/&/is','&amp;',$string);
        $string = preg_replace('/</is','&lt;',$string);
        $string = preg_replace('/>/is','&gt;',$string);
        $string = preg_replace('/\'/is','&apos;',$string);
        $string = preg_replace('/"/is','&quot;',$string);
        $string = str_replace(array('ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', 'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ü'), array('a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z', 'u'),$string);

        /**
         * AAI HACK
         *
         * Alternative escaping strategy if the server doesn't
         * have the php mbstring extension installed/enabled
         */
        $value = htmlspecialchars($string, ENT_QUOTES);
        if (function_exists('mb_encode_numericentity')) {
            return mb_encode_numericentity(trim($value), array(0x80, 0xffff, 0, 0xffff), $encoding);
        } else {
            return $value;
        }
    }
}

