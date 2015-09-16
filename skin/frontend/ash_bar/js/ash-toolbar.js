/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Bar
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
window.Ashbar = {};
Ashbar.Toolbar = function(){};
Ashbar.Toolbar.prototype = {
  /**
   * Calculated DOM prefix, used to reference DOM elements
   *
   * @type {string}
   */
  domPrefix: null,

  /**
   * Toolbar container element
   *
   * @type {Object}
   */
  domEl: null,

  /**
   * Registered callbacks
   *
   * @type {Array}
   */
  callbacks: [],

  /**
   * Collected data from Magento
   *
   * @type {Object}
   */
  collectedData: null,

  /**
   * Constructor
   *
   * @param  {string} el Toolbar container
   * @param  {string} collectedJSON
   */
  initialize: function(el, collectedJSON){
    this.domEl     = jQuery(el);
    this.domPrefix = jQuery(el).attr('id').split('-')[0];
    this.setData(collectedJSON);

    // bail if toolbar HTML doesn't exist
    if (!this.domEl) {
        return;
    }

    // bind click events, etc.
    this.initTabs();
    this.initUserEvents();
    this.initRequest();
    this.initModels();
    this.initCollections();
    this.initBlocks();

    // fire callbacks
    jQuery.each(Ashbar.Toolbar.callbacks, function(callback, func){
      func(Ashbar.Toolbar.getData());
    });

    // handle whether to start open or closed
    this.initDefaultDisplay();
  },

  /**
   * Toggle toolbar open or closed. Sets a cookie to remember on page reload.
   *
   * @return  {void}
   */
  toggleBar: function(){
    // run toggle animation
    jQuery(this.domEl).animate({opacity:'toggle'}, false, false, function(){
      // update cookie to remember chosen position
      Ashbar.Toolbar.setDefaultDisplay(jQuery(this).css('display'));
    });
  },

  /**
   * Bind events to elements so they respond to user interaction.
   * Example: open/close toggle button
   *
   * @return  {void}
   */
  initTabs: function(){
    // activate jQuery UI tabs
    jQuery('#' + this.domPrefix + '-menu').tabs({
        collapsible: true,
        selected: -1,
        beforeActivate: function(e, ui){
          // clears tab cookie on collapse
          if (0 === ui.newTab.length) {
            Ashbar.Toolbar.clearLastTab();
          }
        }
    });

    // activate last tab
    this.switchToLastTab();
  },

  /**
   * Bind events to elements so they respond to user interaction.
   * Example: open/close toggle button
   *
   * @return  {void}
   */
  initUserEvents: function(){
    var dataCollection = Ashbar.Toolbar.getData();

    // activate on/off toggle
    jQuery('#' + this.domPrefix + '-button').bind('click', function(){
      Ashbar.Toolbar.toggleBar();
    });

    // clear cache button
    jQuery('#' + this.domPrefix + '-clean-cache').bind('click', function(){
      var button = jQuery(this);
      var result = jQuery('#' + Ashbar.Toolbar.domPrefix + '-system-results');

      // tell user what's going on
      result.html('<strong>Cleaning system cache ...</strong>').attr('class',
        'alert alert-info');

      // call AJAX controller
      result.load(
        dataCollection.system.ajaxUri + 'cleancache', function(){
          // cache is cleaned
          result.attr('class', 'alert alert-success');
        }
      );
    });

    // toggle cache
    jQuery('#' + this.domPrefix + '-toggle-cache').bind('click', function(){
      jQuery(this).state('loading');
      window.location = dataCollection.system.ajaxUri + 'togglecache';
    });

    // toggle logging
    jQuery('#' + this.domPrefix + '-toggle-logs').bind('click', function(){
      var button = jQuery(this);
      button.state('loading');
      jQuery.get(dataCollection.system.ajaxUri + 'togglelogs', function(data){
        button.state('complete');
        button.html(data).toggleClass('btn-danger');
      });
    });

    // toggle template hints
    jQuery('#' + this.domPrefix + '-toggle-hints').bind('click', function(){
      jQuery(this).state('loading');
      window.location = dataCollection.system.ajaxUri + 'togglehints';
    });

    // save open tab cookie
    jQuery(this.domEl).bind('tabsshow', function(e, ui){
      Ashbar.Toolbar.setLastTab(ui.tab.href);
    });
  },

  /**
   * Populate request tab with collected data
   *
   * @return  {void}
   */
  initRequest: function(){
    var dataCollection = Ashbar.Toolbar.getData();

    if (dataCollection.controller && dataCollection.request) {
      jQuery('#' + this.domPrefix + '-request-classname').html(dataCollection.controller.className);
      jQuery('#' + this.domPrefix + '-request-filename').html(dataCollection.controller.fileName);
      jQuery('#' + this.domPrefix + '-request-fullactionname').html(dataCollection.controller.fullActionName);
      jQuery('#' + this.domPrefix + '-request-modulename').html(dataCollection.request.moduleName);
      jQuery('#' + this.domPrefix + '-request-controllername').html(dataCollection.request.controllerName);
      jQuery('#' + this.domPrefix + '-request-actionname').html(dataCollection.request.actionName);
      jQuery('#' + this.domPrefix + '-request-pathinfo').html(dataCollection.request.pathInfo);
      jQuery('#' + this.domPrefix + '-request-pageid').html(dataCollection.request.pageId);
    }
  },

  /**
   * Populate models tab with collected data
   *
   * @return  {void}
   */
  initModels: function(){
    var dataCollection = Ashbar.Toolbar.getData();

    // construct table rows
    if (dataCollection.models) {
      var rows = [];
      jQuery.each(dataCollection.models, function(key, value){
        rows.push('<tr>');
        rows.push('<td class="text-info">');
        rows.push(key);
        rows.push('<br />');
        rows.push('<span class="muted">' + dataCollection.modelFileNames[key] +
          '<\/span>');
        rows.push('<\/td>');
        rows.push('<td>');
        rows.push(value);
        rows.push('<\/td>');
        rows.push('<\/tr>');
      });

      // inject rows into table
      jQuery('#' + this.domPrefix + '-model-table-body').html(rows.join(''));
    }
  },

  /**
   * Populate collections tab with collected data
   *
   * @return  {void}
   */
  initCollections: function(){
    var dataCollection = Ashbar.Toolbar.getData();

    // construct table rows
    if (dataCollection.collections) {
      var rows = [];
      jQuery.each(dataCollection.collections, function(key, value){
        rows.push('<tr>');
        rows.push('<td class="text-info">');
        rows.push(key);
        rows.push('<br />');
        rows.push('<span class="muted">' + dataCollection.collectionFileNames[key] +
          '<\/span>');
        rows.push('<\/td>');
        rows.push('<td>');
        rows.push(value);
        rows.push('<\/td>');
        rows.push('<\/tr>');
      });

      // inject rows into table
      jQuery('#' + this.domPrefix + '-collection-table-body').html(rows.join(''));
    }
  },

  /**
   * Populate blocks tab with collected data
   *
   * @return  {void}
   */
  initBlocks: function(){
    var dataCollection = Ashbar.Toolbar.getData();

    // construct table rows
    if (dataCollection.blocks) {
      var rows = [];
      jQuery.each(dataCollection.blocks, function(key, value){
        var blockTemplatePair = key.split('::');
        rows.push('<tr>');
        rows.push('<td class="text-info">');
        rows.push(blockTemplatePair[0]);
        rows.push('<br />');
        rows.push('<span class="muted">' + dataCollection.blockFileNames[key] +
          '<\/span>');
        rows.push('<\/td>');
        rows.push('<td>');
        rows.push('<span class="text-warning">' + blockTemplatePair[1] + '<\/span>');
        rows.push('<\/td>');
        rows.push('<td>');
        rows.push(value);
        rows.push('<\/td>');
        rows.push('<\/tr>');
      });

      // inject rows into table
      jQuery('#' + this.domPrefix + '-block-table-body').html(rows.join(''));
    }
  },

  /**
   * Examine current CSS status and cookie and descide whether to render toolbar
   * as open or closed on page load.
   *
   * @return  {void}
   */
  initDefaultDisplay: function(){
    if (this.getDefaultDisplay() != 'none' && jQuery(this.domEl).css('display') != 'block') {
      this.toggleBar();
    }
  },

  /**
   * Save a cookie to facilitate remembering toolbar display status.
   *
   * @param  {string} cssValue
   */
  setDefaultDisplay: function(cssValue){
    // when animation is complete, set cookie
    var cookieName    = this.domPrefix + '_display';
    var cookieOptions = { path: '/', expires: 7 };
    jQuery.cookie(cookieName, cssValue, cookieOptions);
  },

  /**
   * Read a cookie value to facilitate remembering toolbar display status.
   *
   * @return  {string} Cookie value
   */
  getDefaultDisplay: function(){
    var cookieName  = this.domPrefix + '_display';
    if (jQuery.cookie(cookieName)) {
      return jQuery.cookie(cookieName);
    }

    return 'none';
  },

  /**
   * Save collected JSON data
   *
   * @param  {string} collectedJSON
   */
  setData: function(collectedJSON){
    this.collectedData = jQuery.parseJSON(collectedJSON);
  },

  /**
   * Get collected data
   *
   * @return  {Object} JSON object
   */
  getData: function(){
    return this.collectedData;
  },

  /**
   * Look for a selected tab cookie, and activate that tab
   *
   * @return  {void}
   */
  switchToLastTab: function(){
    var tab = this.getLastTab();
    if (false !== tab) {
      jQuery('#' + this.domPrefix + '-menu').tabs('select', tab);
    }
  },

  /**
   * Remove selected tab cookie when collapsed
   *
   * @return  {void}
   */
  clearLastTab: function(){
    jQuery.removeCookie(this.domPrefix + '_lasttab', { path: '/' });
  },

  /**
   * Retrieve last selected tab cookie if exists
   *
   * @return  {mixed}
   */
  getLastTab: function(){
    if(jQuery.cookie(this.domPrefix + '_lasttab')) {
      return '#' + jQuery.cookie(this.domPrefix + '_lasttab').split('#')[1];
    }
    return false;
  },

  /**
   * Save a cookie with the last selected tab
   *
   * @param  {string} tab
   * @return {void}
   */
  setLastTab: function(tab){
    // when tab is selected, set cookie
    var cookieName    = this.domPrefix + '_lasttab';
    var cookieOptions = { path: '/', expires: 7 };
    jQuery.cookie(cookieName, tab, cookieOptions);
  },

  /**
   * Register passed function as a callback
   *
   * @param  {function} func
   * @return {void}
   */
  registerCallback: function(func){
    Ashbar.Toolbar.callbacks.push(func);
  }
};

/**
 * Simple jQuery plugin for changing button states to give appropriate UI
 * feedback during AJAX requests.
 *
 * @param  {string} state
 * @return {void}
 */
jQuery.fn.state = function(state){
  var d = 'disabled';
  return this.each(function(){
    var $this = jQuery(this);
    $this[0].className = $this[0].className.replace(/\bstate-.*?\b/g, '');
    $this.html('Saving...');
    state == 'loading' ? $this.addClass(d+' state-'+state).attr(d,d) : $this.removeClass(d).removeAttr(d);
  });
};
