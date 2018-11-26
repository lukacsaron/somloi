angular.module(angAppName)
	.directive('viewmodeAccordion', function(customFilterService){

		return{
			
			restrict:"AE",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			templateUrl: lscfShortcodeData[0].plugin_url + 'app/views/posts-accordion.html',
			link:function(scope, elem, attrs){

				var accordionPosts = new lscfAccordionPosts();
				
				scope.actionSettings.initPostTheme = true;

				scope.directiveInfo.ready = function(){

					accordionPosts.options.link_type = ( 'undefined' !== typeof scope.pluginSettings.filterSettings.theme.link_type ? scope.pluginSettings.filterSettings.theme.link_type : 0 );

					setTimeout(function(){
						accordionPosts.init();
					}, 500);
					

				};					
					
				scope.directiveInfo.afterPostsLoadCallback = function(){

					setTimeout(function(){
						accordionPosts.init();
					}, 500);
				};


				function lscfAccordionPosts(){
	
					var $j = jQuery,
						self = this;
				
					this.options = {
						"link_type":0
					};
				
					this.init = function(){
						$j('.lscf-posts-accordion .lscf-title').unbind('click');
				
						if ( 'link-only' === self.options.link_type ) {
							return false;
						 }
						
						$j('.lscf-posts-accordion .lscf-title').bind( 'click', function(event){
				
							var parentContainer = $j(this).closest('.lscf-accordion-post');
				
							if ( parentContainer.hasClass('active') ) {
								parentContainer.find('.post-caption').animate({
									height:0
								}, 400);	
								parentContainer.removeClass('active');
								parentContainer.addClass('inactive');
								return false;
							}
				
							$j('.lscf-accordion-post').removeClass('active');
							$j('.lscf-accordion-post').addClass('inactive');
				
							
							parentContainer.addClass('active');
							parentContainer.removeClass('inactive');
				
							var animateHeight = parentContainer.find('.caption').height()+40;
				
							parentContainer.find('.post-caption').animate({
								height:animateHeight
							}, 400);
				
							$j('.lscf-accordion-post.inactive').find('.post-caption').animate({
								height:0
							}, 300);
				
							event.preventDefault();
							event.stopPropagation();
				
							return false;
						});
				
					};
				
				}
			}
		};
	});
