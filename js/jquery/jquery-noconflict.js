/**
 * Add Jquery/Jquery UI support
 *
 * @category    Ash
 * @package     Ash_Jquery
 * @copyright   Copyright (c) 2013 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * The below replaces jQuery's "$" with a "$J".
 *
 * Example Usage:
 *
 * $J(document).ready(function() {
 *     $J('<div id="mydiv">Hello World!</div>').appendTo($J('body'));
 *     $J('#mydiv').dialog();
 * });
 */

var $J = jQuery.noConflict();
