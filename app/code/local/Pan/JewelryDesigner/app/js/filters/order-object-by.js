'use strict';

// see this great article about how to do this:
// @see http://justinklemm.com/angularjs-filter-ordering-objects-ngrepeat/
angular.module('jewelryDesigner')
  .filter('orderObjectBy', function(){
  return function(items, field, reverse) {
    var filtered = [];
    angular.forEach(items, function(item) {
      filtered.push(item);
    });
    filtered.sort(function (a, b) {
      return (a[field] > b[field] ? 1 : -1);
    });
    if(reverse) filtered.reverse();
    return filtered;
  };
});
