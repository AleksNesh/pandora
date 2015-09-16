/**
 * jQuery DOM ready
 */
jQuery(document).ready(function(){
    initFitVids('related-vid');
});


/**
 * initFitVids
 *
 * Add wrapper div around the iframe videos and initiate fitvids
 *
 * @param  string   wrapperClass
 * @return void
 */
function initFitVids(wrapperClass) {
    if (wrapperClass === null || wrapperClass === undefined) {
        wrapperClass = 'related-vid';
    }
    jQuery('iframe').wrap('<div class="' + wrapperClass + '"></div>');
    jQuery("." + wrapperClass).fitVids();
}

/**
 * sizing pop-up message
 *
 * Pop Up a window to give details on how to size a bracelet / ring
 *
 */

function deselect(e) {
  $('.pop').slideFadeToggle(function() {
    e.removeClass('selected');
  });    
}

$(function() {
  $('#bracelet-size').on('click', function() {
    if($(this).hasClass('selected')) {
      deselect($(this));               
    } else {
      $(this).addClass('selected');
      $('.pop').slideFadeToggle();
    }
    return false;
  });

  $('.close').on('click', function() {
    deselect($('#bracelet-size'));
    return false;
  });
});

function slideFadeToggle(easing, callback) {
  return this.animate({ opacity: 'toggle', height: 'toggle' }, 'fast', easing, callback);
};