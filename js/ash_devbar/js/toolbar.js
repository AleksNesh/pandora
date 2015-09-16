/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implemented as a jQuery plugin
 *
 * @param   {object} collectedJson
 * @return  void
 */
(function($) {

  var collectedJson;
  var domTarget;
  var domPrefix = 'devbar';

  $.fn.devbar = function(collectedJsonString) {

    collectedJson = $.parseJSON(collectedJsonString);
    domTarget     = this;

    // bind click events, load tabs, etc.
    initUi();
    initSystemTab();
  };

  /**
   * Handle toolbar operation events
   *
   * @return  {void}
   */
  function initUi() {
    // wire up the activate button
    $('#' + domPrefix + '-activator').toggleDevbar({
      menu: domTarget,
      cookieName: domPrefix + '_display'
    });

    // tab UI
    $('#' + domPrefix + '-menu').tabs({
      collapsible: true,
      active: ($.cookie(domPrefix + '_tabs') || false),
      activate: function(event, ui) {
        var newIndex = $('#' + domPrefix + '-menu').tabs('option', 'active');
        $.cookie(domPrefix + '_tabs', newIndex, { expires: 1 });
      }
    });
  }

  /**
   * Bind events to system tab buttons.
   *
   * @return  {void}
   */
  function initSystemTab() {
    // toggle cache
    $('#' + domPrefix + '-toggle-cache').bind('click', function(){
      var button = $(this).toggleButtonState();
      button.html('Loading...');
      $.getJSON(collectedJson.system.ajaxUri + 'togglecache', function(data){
        // handle button feedback
        button.html(data.label);
        // display results to user
        $('#' + domPrefix + '-system-results').html(data.html);
        location.reload();
      });
    });

    // clear cache
    $('#' + domPrefix + '-clean-cache').bind('click', function(){
      var button = $(this).toggleButtonState();
      button.html('Clearing...');
      $('#' + domPrefix + '-system-results').load(collectedJson.system.ajaxUri + 'cleancache', function(){
        // handle button feedback
        button.toggleButtonState();
        button.html('Clean Cache');
      });
    });

    // toggle logging
    $('#' + domPrefix + '-toggle-logs').bind('click', function(){
      var button = $(this).toggleButtonState();
      button.html('Loading...');
      $.getJSON(collectedJson.system.ajaxUri + 'togglelogs', function(data){
        // handle button feedback
        button.toggleButtonState();
        button.toggleClass('alert');
        button.html(data.label);
        // display results to user
        $('#' + domPrefix + '-system-results').html(data.html);
      });
    });

    // toggle template hints
    $('#' + domPrefix + '-toggle-hints').bind('click', function(){
      var button = $(this).toggleButtonState();
      button.html('Loading...');
      $.getJSON(collectedJson.system.ajaxUri + 'togglehints', function(data){
        // handle button feedback
        button.html(data.label);
        // display results to user
        $('#' + domPrefix + '-system-results').html(data.html);
        location.reload();
      });
    });

    // toggle template block names
    $('#' + domPrefix + '-toggle-blocks').bind('click', function(){
      var button = $(this).toggleButtonState();
      button.html('Loading...');
      $.getJSON(collectedJson.system.ajaxUri + 'toggleblocks', function(data){
        // handle button feedback
        button.html(data.label);
        // display results to user
        $('#' + domPrefix + '-system-results').html(data.html);
        location.reload();
      });
    });
  }

})(jQuery);


(function($) {

  $.fn.toggleDevbar = function(options) {

    var settings = $.extend({
      target:     'devbar-menu',
      cookieName: 'devbar_tabs'
    }, options);

    return this.each(function(){
      var self   = $(this);
      var status = $.cookie(settings.cookieName) || null;

      // if the bar was open on refresh, keep it open
      if (status !== null && status === 'block') {
        toggleBar(settings.target, settings.cookieName);
      }

      // when click the activator button, toggle the bar
      self.bind('click', function(){
        toggleBar(settings.target, settings.cookieName);
      });
    });
  };

  /**
   * Toggle toolbar open or closed. Sets a cookie to remember on page reload.
   *
   * @param   {string} target The menu element to toggle
   * @param   {string} cookieName The name of the cookie to persist status
   * @return  {void}
   */
  function toggleBar(target, cookieName) {
    $('#' + target).animate({opacity:'toggle'}, false, false, function(){
      $.cookie(cookieName, $(this).css('display'), { expires: 1 });
    });
  }

})(jQuery);

/**
 * Simple jQuery plugin for changing button states to give appropriate UI
 * feedback during AJAX requests.
 *
 * @return {void}
 */
(function($) {

  $.fn.toggleButtonState = function() {
    return this.each(function(){
      var self = $(this);
      if (self.hasClass('disabled')) {
        self.removeClass('disabled');
        self.prop('disabled', false);
      } else {
        self.addClass('disabled');
        self.prop('disabled', true);
      }
    });
  };

})(jQuery);
