angular.module(angAppName)
.directive('viewmodeWoocommerceGrid', function( customFilterService ){
	
	return{
		
		restrict:"AE",
		scope:true,
		bindToController: true,
		controllerAs: 'vm',
		templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-woocommerce-grid-2.html',
		link:function(scope, elem, attrs){

			scope.gridColumns = 3;

			scope.changeGridType = function( element, column ) {
				var gridChangers = document.getElementsByClassName('lscf-woo-grid-type');

				for ( var i = 0; i < gridChangers.length; i++ ) {
					gridChangers[ i ].className = 'lscf-woo-grid-type';
				}

				element.currentTarget.className = 'lscf-woo-grid-type active';
				scope.gridColumns = column;
			};

			scope.actionSettings.initPostTheme = true;

			scope.directiveInfo.ready = function(){
				
			};					
				
			scope.directiveInfo.afterPostsLoadCallback = function(){
				
			};

		}
	};
});