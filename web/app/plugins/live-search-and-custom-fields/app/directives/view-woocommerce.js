angular.module(angAppName)
    .directive('viewmodeWoocommerce', function( customFilterService ){
        
        return{
            
            restrict:"AE",
            scope:true,
            bindToController: true,
            controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-woocommerce.html',
            link:function(scope, elem, attrs){
				
				scope.actionSettings.initPostTheme = true;

				scope.directiveInfo.ready = function(){		
					
				};					
					
				scope.directiveInfo.afterPostsLoadCallback = function(){
					
				};

			}
		};
	});