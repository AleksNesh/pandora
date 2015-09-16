/* jshint laxcomma: true */

'use strict';

angular.module('jewelryDesigner').controller('WorkspaceCtrl', ['$scope', '$state', '$stateParams', '$rootScope', '$document',
  function($scope, $state, $stateParams, $rootScope, $document) {
    $scope.title = 'My New Bracelet';
    $scope.designId = $stateParams.designId;

    $scope.hideInstructions = false;
    $scope.closeInstructions = function() {
      $scope.hideInstructions = !$scope.hideInstructions;

      if ($scope.hideInstructions) {
        angular.element('.designer_app_wrapper').addClass('hide-instructions');
      }
    };

    $scope.shouldHideInstructions = function(){
      return jQuery('.designer_app_wrapper').hasClass('hide-instructions');
    };

    // take action on broadcast message from a $rootScope.$emit('design_loaded');
    $rootScope.$on('design_loaded', function(event, data){
      if (data.name !== undefined) {
        $scope.title = data.name;
      }
    });

    // $scope.title = 'My New Bracelet';
    $scope.editNameBtnLabel = 'Edit Name';

    $scope.showForm = false;
    $scope.toggleEditNameField = function() {
      $scope.showForm = !$scope.showForm;
      $scope.editNameBtnLabel = ($scope.showForm) ? 'Save Name' : 'Edit Name';

      if (!$scope.showForm) {
        $scope.saveDesign();
      }
    };


    $scope.isAvailable    = true;
    $scope.isInspiration  = false;

    $document.ready(function(){
      var width   = jQuery('.design-canvas').width()
        , height  = jQuery('.design-canvas').height()
        , center  = { x: Math.ceil(width / 2), y: Math.ceil(height / 2), rotate: true };
      $scope.braceletCenter = center;
    });

    // we will store our form data in this object
    $scope.formData = {
      attribute_options: {},
      is_already_owned: false
    };



    if ($scope.designId !== 'new') {
      // only set the current design id when it's an actual value (numeric)
      // this helps to avoid creating duplicate designs when switching between design steps
      PAN.DesignerWorkspace.setCurrentDesignId($scope.designId);
    }

    $scope.steps = [
      { name: 'bracelets', htmlstr: '<span class="choose">Choose</span> Bracelet', designId: $stateParams.designId },
      { name: 'charms', htmlstr: '<span class="choose">Choose</span> Charms', designId: $stateParams.designId},
      { name: 'clips', htmlstr: '<span class="choose">Choose</span> Clips', designId: $stateParams.designId },
      // { name: 'finish', htmlstr: 'Finishing Touches', designId = $stateParams.designId },
      { name: 'review', htmlstr: 'Review Bracelet', designId: $stateParams.designId },
    ];

    $scope.modalShown = false;
    $scope.toggleModal = function() {
      $scope.modalShown = !$scope.modalShown;
    };

    // update the DesignerWorkspace version of the bracelet
    $scope.handleBraceletSelect = function(bracelet, formData) {
      PAN.DesignerWorkspace.setBracelet(bracelet, formData);
      $scope.toggleModal();
    };

    // add a bead/charm to the workspace
    $scope.addToWorkspace = function(product, position, formData) {
      // console.log('hit WorkspaceCtrl.addToWorkspace()');

      if (position === undefined || position === {}) {
        position = { x: 287, y: 265, rotate: false, lock: true };
      }

      PAN.DesignerWorkspace.addBead(product, position, null, formData);
      PAN.DesignerWorkspace.showMessage('Charm added to workspace!');
    };

    // save the current design configuration and items to the database
    $scope.saveDesign = function() {
      var inspirationCheckBox       = jQuery('#inspiration_chkbox')
        , inspirationCheckBoxValue  = (inspirationCheckBox.length > 0) ? inspirationCheckBox.is(':checked') : false
        , availableCheckBox         = jQuery('#is_available_chkbox')
        , availableCheckBoxValue    = (availableCheckBox.length > 0) ? availableCheckBox.is(':checked') : true;

      PAN.DesignerWorkspace.saveDesign(availableCheckBoxValue, inspirationCheckBoxValue);

      if ($scope.isLoggedIn()) {
        PAN.DesignerWorkspace.showMessage('Bracelet has been updated!');
      }
    };

    // destroy the design and start over
    $scope.resetDesign = function() {
      PAN.DesignerWorkspace.startOver();
      $state.go('ui.bracelets');
      PAN.DesignerWorkspace.showMessage('Bracelet design reset!');
    };

    $scope.isAdmin = function() {
      return jQuery('.designer_app_wrapper').hasClass('admin');
    };

    $scope.isGuest = function() {
      return jQuery('.designer_app_wrapper').hasClass('guest');
    };

    $scope.isLoggedIn = function() {
      return jQuery('.designer_app_wrapper').hasClass('logged-in');
    };

  }
]);
