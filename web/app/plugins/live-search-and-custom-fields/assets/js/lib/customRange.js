function customRange(){
    var $j = jQuery,
        self = this;
    this.init = function(){
        
		$j(".customRange").each(function(){
          
          var _this = $j(this);
          
          self.defaultPosition(_this);
          
          _this.find(".draggablePoint").draggable({
              drag:function(){
                  var x = $j(this).position().left+15,
                      rangeVal = 0;
                  _this.find(".range_draggable").css({
                      "width":parseInt(x)+"px"
                  });
                  rangeVal = self.calculateCurrentRangeValue(_this, x);
              },
              axis:"x",
              containment: _this
          });


        });    
    };
    
    this.calculateCurrentRangeValue = function(rangeElement, position){
        var _this = rangeElement;
        var containerWidth = _this.width();
        var maxValue = _this.data('maxval');
        
        var rangeValue = Math.round(position*maxValue/containerWidth);
        
        rangeValue = rangeValue>maxValue?maxValue:rangeValue;
        
        _this.find(".rangeVal").text(rangeValue);
        _this.find('input[type="hidden"]').val(rangeValue);
        
        return rangeValue;
    };

    this.defaultPosition = function(_this){
        var percentage = _this.data('defaultpos'),
            x = 0;
        _this.find(".range_draggable").css({"width":percentage+"%"});
        
        x = parseInt(_this.find(".draggablePoint").position().left);
        self.calculateCurrentRangeValue(_this, x);
    }
    
}
(function(){
   $j = jQuery;
   $j(function(){
        var rangeField = new customRange();
        rangeField.init();     
   })
   
})();