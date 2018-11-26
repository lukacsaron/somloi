angular.module(angAppName)
	.directive('viewmodeDefault', function( customFilterService ) {

		return{

			restrict:"AE",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-default.html',
			link: function( scope, elem, attrs ) {

				scope.actionSettings.initPostTheme = true;

				var lscfViewChangerPosts = new lscfPosts();
					shortcodeSettings = lscfShortcodeData[ scope.actionSettings.controllerAttributes.index ];

				scope.directiveInfo.ready = function(){

					scope.standartize_shortcodes_container_height();
					lscfViewChangerPosts.init();
					if ( 'undefined' !== typeof shortcodeSettings.settings && 'undefined' !== typeof shortcodeSettings.settings.theme ) {
						if ( 'undefined' === typeof shortcodeSettings.settings.theme.viewchanger || 'undefined' === typeof shortcodeSettings.settings.theme.viewchanger.list || 1 == shortcodeSettings.settings.theme.viewchanger.grid || 1 != shortcodeSettings.settings.theme.viewchanger.list ) {
							
							jQuery( '.lscf-posts-block' ).addClass('block-view');
							jQuery( '.viewMode > div' ).removeClass('active');
							jQuery( '.viewMode #blockView' ).addClass('active');

						}
					}

				};

				scope.directiveInfo.afterPostsLoadCallback = function(){
					scope.standartize_shortcodes_container_height();
				};

			}
		};
	});