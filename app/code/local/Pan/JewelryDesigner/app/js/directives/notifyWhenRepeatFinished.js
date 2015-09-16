'use strict';

angular.module('jewelryDesigner')
  .directive('notifyWhenRepeatFinished', ['$timeout',
    function($timeout){
      return {
        restrict: 'A',
        link: function (scope, element, attr) {
          if (scope.$last === true) {
            $timeout(function () {
              scope.$emit('repeatFinished');
            });
          }
        }
      };
    }
]);
