var PAN = PAN || {};
(function(){
  function Designer() {
      this.init();
  }

  Designer.prototype.init = function() {
    PAN.DesignerWorkspace.init();
  };

  PAN.Designer = Designer;

})(PAN);
