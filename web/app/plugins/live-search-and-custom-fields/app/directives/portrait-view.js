angular.module(angAppName)
	.directive('viewmodePortrait', function(customFilterService){

		return{

			restrict:"AE",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-portrait.html',
			link:function(scope, elem, attrs){

				scope.actionSettings.initPostTheme = true;

				scope.directiveInfo.ready = function(){

				};

				scope.directiveInfo.afterPostsLoadCallback = function(){
					
				};

			}
		};
	});