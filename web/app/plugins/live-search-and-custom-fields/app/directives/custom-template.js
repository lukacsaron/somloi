angular.module(angAppName)
    .directive('viewmodeCustom', function( customFilterService ) {
		
        return{
            
            restrict:"AE",
            scope:true,
            bindToController: true,
            controllerAs: 'vm',
            link:function( scope, elem, attrs ) {

				var accordionPosts = new lscfAccordionPosts();
				scope.directiveInfo.ready = function(){

					setTimeout(function(){
						accordionPosts.init();
					}, 500);

					if ( 'undefined' !== typeof lscfOnCustomTemplateReady ) {
						lscfOnCustomTemplateReady();
					}

					setTimeout(function(){
						lscfEventListenerOnCustomTemplateReady();
					}, 300);
				};					
					
				scope.directiveInfo.afterPostsLoadCallback = function(){

					setTimeout(function(){
						accordionPosts.init();
					}, 500);

					if ( 'undefined' !== typeof lscfPostsLoadCallback ) {
						lscfPostsLoadCallback();
					}
					setTimeout(function(){
						lscfEventListenerPostsLoadCallback();					
					},400);
				};
				
				scope.changeGridType = function( element, column ) {
					var gridChangers = document.getElementsByClassName('lscf-woo-grid-type');
	
					for ( var i = 0; i < gridChangers.length; i++ ) {
						gridChangers[ i ].className = 'lscf-woo-grid-type';
					}
	
					element.currentTarget.className = 'lscf-woo-grid-type active';
					scope.gridColumns = column;
				};

			},
			template: '<div ng-include="pluginSettings.filterSettings.theme.custom_template.url">'
		};
	});



function lscfEventListenerOnCustomTemplateReady( state ) {
    
	var evt = new CustomEvent('lscf_on_custom_template_ready', { detail: state });

    window.dispatchEvent( evt );
}

function lscfEventListenerPostsLoadCallback( state ) {
    
	var evt = new CustomEvent('lscf_posts_load_callback', { detail: state });

    window.dispatchEvent( evt );
}

function lscfAccordionPosts(){
	
	var $j = jQuery,
		self = this;

	this.options = {
		"link_type":0
	};

	this.init = function(){
		
		if ( !$j('.lscf-custom-template-wrapper').hasClass('lscf-posts-accordion') ) {
			return;
		}

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