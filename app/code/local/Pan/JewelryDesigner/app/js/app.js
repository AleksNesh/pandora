/* jshint laxcomma: true */

(function(){
  'use strict';

  /**
   * Sneaky way of deriving the path to our Angular JS app/partials directory
   *
   * Note: this may break if another script tag is added,
   * so maybe just make an AJAX call fetch the url?
   */
  // var scripts           = document.getElementsByTagName("script")
  //   , currentScriptPath = scripts[scripts.length-1].src
  //   , partialsUrlPath   = currentScriptPath.replace('js/app.js', 'partials');

  var angularAppDir           = '/app/code/local/Pan/JewelryDesigner/app'
    , origin                  = window.location.protocol + '//' + window.location.host
    , viewsUrlPath            = origin + angularAppDir + '/views'
    , partialsUrlPath         = viewsUrlPath + '/partials'
    , appImgDir               = origin + angularAppDir + '/img'
    , appCssDir               = origin + angularAppDir + '/css'
    , appJsDir                = origin + angularAppDir + '/js'
    , appNavTemplateUrlPath   = partialsUrlPath + '/navbar.html'
    , skinUrlPrefix           = '//skin/frontend/shopper/pan/';


  // define AngularJS app
  var app = angular.module('jewelryDesigner', [
      'ui.router'
    , 'ngSanitize'
    , 'ngResource'
    , 'infinite-scroll'
  ])
  //========================================================
  //                       ROUTING
  //========================================================
  .config(['$stateProvider', '$urlRouterProvider', '$httpProvider',
    function($stateProvider, $urlRouterProvider, $httpProvider) {

      $httpProvider.defaults.headers.common['Cache-Control'] = 'no-cache';

      // default route
      $urlRouterProvider.otherwise('/');

      // states (aka, routes)
      $stateProvider
        .state('home', {
            url: '/'
          , templateUrl: viewsUrlPath + '/home.html'
          , controller: 'HomeCtrl'
        })

        ////////////////////////////////////////////////////////////////////////
        // Abstracts
        ////////////////////////////////////////////////////////////////////////
        .state('ui', {
            abstract: true
          , url: '/design/:designId'
          , views: {
              '': {
                  templateUrl: viewsUrlPath + '/ui.html'
                , controller: 'UiCtrl'
              }
            , 'workspace@ui': {
                  templateUrl: partialsUrlPath + '/ui/workspace.html'
                , controller: 'WorkspaceCtrl'
              }
            , 'navbar@ui': {
                  templateUrl: partialsUrlPath + '/ui/navbar.html'
                , controller: 'WorkspaceCtrl'
                , resolve: {
                    designId: [ '$stateParams', function($stateParams){
                      return $stateParams.designId;
                    }]
                }
            }
            , 'topnav@ui': {
                  // template: 'MILESTONE: topnav menu placeholder'
                  templateUrl: partialsUrlPath + '/ui/topnav.html'
                , controller: 'WorkspaceCtrl'
                , resolve: {
                    designId: [ '$stateParams', function($stateParams){
                      return $stateParams.designId;
                    }]
                }
              }
          }
        })

        ////////////////////////////////////////////////////////////////////////
        // Bracelet Builder Steps
        ////////////////////////////////////////////////////////////////////////
        .state('ui.bracelets', {
            url: '/bracelets'
          , views: {
              'sidebar@ui': {
                  templateUrl: partialsUrlPath + '/designersteps/bracelets.html'
                , controller: 'BraceletsCtrl'
                , resolve: {
                    products: ['Product', function(Product) {

                      if (angular.element('.loader').hasClass('hidden')) {
                        angular.element('.loader').removeClass('hidden');
                      }
                      // By returning the $promise property of query(),
                      // ui-router will make sure that the data is completely
                      // resolved before the controller is executed.
                      return Product.query({type: 'bracelets'}).$promise;
                    }]
                }
              }
          }
        })
        .state('ui.charms', {
            url: '/charms'
          , views: {
              'sidebar@ui': {
                  templateUrl: partialsUrlPath + '/designersteps/charms.html'
                , controller: 'CharmsCtrl'
                , resolve: {
                    products: ['Product', function(Product) {

                      if (angular.element('.loader').hasClass('hidden')) {
                        angular.element('.loader').removeClass('hidden');
                      }


                      // By returning the $promise property of query(),
                      // ui-router will make sure that the data is completely
                      // resolved before the controller is executed.
                      return Product.query({type: 'charms'}).$promise;
                    }]
                }
              }
          }
        })
        .state('ui.clips', {
            url: '/clips'
          , views: {
              'sidebar@ui': {
                  templateUrl: partialsUrlPath + '/designersteps/clips.html'
                , controller: 'ClipsCtrl'
                , resolve: {
                    products: ['Product', function(Product) {

                      if (angular.element('.loader').hasClass('hidden')) {
                        angular.element('.loader').removeClass('hidden');
                      }

                      // By returning the $promise property of query(),
                      // ui-router will make sure that the data is completely
                      // resolved before the controller is executed.
                      return Product.query({type: 'clips'}).$promise;
                    }]
                }
              }
          }
        })
        .state('ui.finish', {
            url: '/finish'
          , views: {
              'sidebar@ui': {
                  // template: 'ui.bracelets sidebar content goes here!'
                  templateUrl: partialsUrlPath + '/designersteps/finish.html'
                , controller: 'FinishCtrl'
              }
          }
        })
        .state('ui.review', {
            url: '/review'
          , views: {
              'sidebar@ui': {
                  templateUrl: partialsUrlPath + '/designersteps/review.html'
                , controller: 'ReviewCtrl'
                , resolve: {
                    wsBracelet: ['$rootScope', function($rootScope){

                      if (angular.element('.loader').hasClass('hidden')) {
                        angular.element('.loader').removeClass('hidden');
                      }

                      var bracelet = PAN.DesignerWorkspace.getCurrentBracelet();
                      return bracelet;
                    }]
                }
              }
          }
        })
        ////////////////////////////////////////////////////////////////////////
        // Design Sharing via Social Links
        ////////////////////////////////////////////////////////////////////////
        .state('ui.share', {
            url: '/share'
          , views: {
              'topnav@ui': {
                  templateUrl: partialsUrlPath + '/ui/share/topnav.html'
                , controller: 'ShareNavCtrl'
              }
              , 'navbar@ui': {
                  template: ''
                , controller: 'ShareNavCtrl'
              }
              , 'workspace@ui': {
                  // template: 'Share workspace'
                  templateUrl: partialsUrlPath + '/ui/share/workspace.html'
                , controller: 'ShareWorkspaceCtrl'
                , resolve: {
                    design: ['$stateParams', 'Design', function($stateParams, Design){
                      // By returning the $promise property of query(),
                      // ui-router will make sure that the data is completely
                      // resolved before the controller is executed.
                      return Design.get({id: $stateParams.designId, allow_anonymous: 1}).$promise;
                    }]
                  }
              }
              , 'sidebar@ui': {
                  // template: 'share sidebar'
                  templateUrl: partialsUrlPath + '/ui/share/sidebar.html'
                , controller: 'ShareSidebarCtrl'
                , resolve: {
                    design: ['$stateParams', 'Design', function($stateParams, Design){
                      // By returning the $promise property of query(),
                      // ui-router will make sure that the data is completely
                      // resolved before the controller is executed.
                      return Design.get({id: $stateParams.designId, allow_anonymous: 1}).$promise;
                    }]
                  }
              }
          }
        })
        .state('inspiration', {
            url: '/inspiration'
          , templateUrl: viewsUrlPath + '/inspiration.html'
          , controller: 'InspirationsCtrl'
        })
        .state('mydesigns', {
            url: '/my-designs'
          , templateUrl: viewsUrlPath + '/design-list.html'
          , controller: 'MyDesignsCtrl'
        });
    }
  ])
  //========================================================
  //                 BOOT STRAPPING
  //========================================================
  .run(function($rootScope){
    $rootScope.viewsUrlPath             = viewsUrlPath;
    $rootScope.partialsUrlPath          = partialsUrlPath;
    $rootScope.appImgDir                = appImgDir;
    $rootScope.appCssDir                = appCssDir;
    $rootScope.appJsDir                 = appJsDir;
    $rootScope.appNavTemplateUrlPath    = appNavTemplateUrlPath;
    $rootScope.skinUrlPrefix            = origin + skinUrlPrefix;
  });
})();
