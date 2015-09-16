'use strict';

angular.module('jewelryDesigner')
  .directive('bxSlider', [function(){
    return {
      restrict: 'A',
      link: function (scope, element, attrs) {
        scope.$on('repeatFinished', function () {
          // console.log("ngRepeat has finished");
          element.bxSlider(scope.$eval('{' + attrs.bxSlider + '}'));
        });
      }
    };
}]);
