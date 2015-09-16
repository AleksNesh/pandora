/**
 * Ash_Phonemask
 *
 * Custom phone mask for phone/fax fields
 *
 * @category    Ash
 * @package     Ash_Phonemask
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */
jQuery(document).ready(function() {
    var phone_format = "(999) 999-9999";
    if (jQuery("input[name*='telephone']").length > 0) {
      jQuery("input[name*='telephone']").mask(phone_format);
    }

    if (jQuery("input[name*='fax']").length > 0) {
      jQuery("input[name*='fax']").mask(phone_format);
    }

});
