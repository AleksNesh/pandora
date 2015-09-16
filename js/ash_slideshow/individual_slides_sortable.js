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

varienGrid = Class.create(varienGrid, {
    initGrid: function ($super) {
        $super(); // Calling parent method functionality

        // Doing your customization
        jQuery(document).ready(function() {

            var slideId = jQuery("#id").val();

            jQuery("#slideshowAssetsGrid_table tbody").sortable({
                // Esse helper eh necessario
                // porque quando arastando
                // uma das table row a row fica
                // com o mesmo tamanho da tabela
                // helper : 'td.handle',
                helper : function(e, tr)
                {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index)
                    {
                        // Set helper cell sizes to match the original sizes
                        jQuery(this).width($originals.eq(index).width())
                    });
                    return $helper;
                },
                update: function(e, ui) {
                    var loc, href, sorted;

                    sorted  = new Array();
                    loc     = window.location;
                    href    = loc.origin + '/index.php/ash_slideshow/adminhtml_slideshows/slideassetsort/' + loc.pathname.match(/key\/\w+/) + '/';

                    jQuery('table tr td.handle input.checkbox').each(function(){
                        var title_id = jQuery.trim(jQuery(this).val());
                        sorted.push(title_id);
                    });

                    jQuery.ajax({
                        type: 'POST',
                        url: href,
                        data: {slideid: slideId, order: sorted, isAjax: true, ajax: true, form_key:FORM_KEY},
                        success: function(msg) {
                            // proccess response
                        }
                    });
                }
            });
        });
    }
});
