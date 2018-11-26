angular.module(angAppName)
    .directive('sortBy', function(customFilterService){

		return{

			restrict:"AE",
			require: "?ngModel",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			link: function( scope, elem, attrs, ngModel ) {

				var field_exists = false,
					field,
					orderByID,
					dropdownTemplate,
					$j = jQuery,
					shortcodeSettings = lscfShortcodeData[ scope.actionSettings.controllerAttributes.index ];

				scope.$watch( 'pluginSettings.generalSettings.order_by.items', function( newVal, oldVal ){
					
						if ( 'undefined' !== typeof scope.pluginSettings.generalSettings.order_by && 
							'undefined' !== typeof scope.pluginSettings.generalSettings.order_by.items &&
							scope.pluginSettings.generalSettings.order_by.items.length > 0 ) {

								dropdownTemplate = '<select data-class="lscf-order-by-dropdown lscf-sorting-by" class="lscf-sorting-custom-dropdown">';
								dropdownTemplate += '<option value="0">' + scope.pluginSettings.generalSettings.writing.sort_by + '</option>';

								scope.pluginSettings.generalSettings.order_by.items.forEach( function( item ){
									var name = ( 'woo_price' === item.id ? scope.pluginSettings.generalSettings.writing.price : item.name );
									dropdownTemplate += '<option value="' + item.id + '">' + name + '</option>';
								});

								dropdownTemplate += '</select>';

								dropdownTemplate += '<div class="lscf-sorting-opt">';
								dropdownTemplate += '<div class="lscf-sort-up"><span class="glyphicon glyphicon-triangle-top"></span></div>';
								dropdownTemplate += '<div class="lscf-sort-down"><span class="glyphicon glyphicon-triangle-bottom"></span></div>';
								dropdownTemplate += '</div>';


								elem[0].innerHTML = dropdownTemplate;
								lscfSortingCustomDropddown();

								if ( 'undefined' !== typeof shortcodeSettings.settings.theme.posts_display_from && 
								'undefined' !== shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms && 
									shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms.length > 0 ) 
								{

									scope.actionSettings.activeQuery.push({
										"ID":"default_filter",
										"type":"default_filter",
										"default_filter":{
											"post_taxonomies":shortcodeSettings.settings.theme.posts_display_from.post_taxonomies
										} 
									});

								}


								$j('.px-capf-wrapper').addClass('lscf-active-sort-by');

								$j('.lscf-sort-up').click(function(){
									
									$j(this).addClass('active');
									$j('.lscf-sort-down').removeClass('active');

									var order = "DESC";	

									for ( var c = 0; c < scope.actionSettings.activeQuery.length; c++ ) {

										field = scope.actionSettings.activeQuery[ c ];
										
										if ( 'order-by' == field.ID ) {

											scope.actionSettings.activeQuery[ c ].order = order;
											field_exists = true;
											break;
										}
									}
									
									if ( false === field_exists ) {

										query = {
											"ID":"order-by",
											"filter_as":null,
											"type":"order-posts",
											"order":order,
											"value":"post_date"
										};

										scope.actionSettings.activeQuery.push( query );
									}

									scope.loadMoreBtn.postsLoading = true;

									customFilterService.getPosts( scope.postType, scope.actionSettings.postsPerPage, 1, scope.actionSettings.activeQuery, shortcodeSettings )
										.then(function( data ) {
											data = data.data;
											scope.actionSettings.postsCount = data.postsCount;
											scope.actionSettings.pagesCount = data.pages;
											scope.actionSettings.pxCurrentPage = 2;

											if ( scope.actionSettings.pxCurrentPage <= data.pages) scope.loadMoreBtn.morePostsAvailable = true;
											else scope.loadMoreBtn.morePostsAvailable = false;

											scope.actionSettings.filterPostsTemplate = data.posts;
											scope.filterPostsTemplate.posts = data.posts;
											scope.loadMoreBtn.postsLoading = false;

											scope.directiveInfo.afterPostsLoadCallback();

									});

								});

								$j('.lscf-sort-down').click(function(){
									
									$j(this).addClass('active');
									$j('.lscf-sort-up').removeClass('active');
									
									var order = 'ASC';	

									for ( var c = 0; c < scope.actionSettings.activeQuery.length; c++ ) {
						
										field = scope.actionSettings.activeQuery[ c ];
										
										if ( 'order-by' == field.ID ) {

											scope.actionSettings.activeQuery[ c ].order = order;
											
											field_exists = true;
											break;

										}
									}
									
									if ( false === field_exists ) {

										query = {
											"ID":"order-by",
											"filter_as":null,
											"type":"order-posts",
											"order":order,
											"value":"post_date"
										};

										scope.actionSettings.activeQuery.push( query );
									}

									scope.loadMoreBtn.postsLoading = true;

									customFilterService.getPosts( scope.postType, scope.actionSettings.postsPerPage, 1, scope.actionSettings.activeQuery, shortcodeSettings )
										.then(function( data ) {
											data = data.data;
											scope.actionSettings.postsCount = data.postsCount;
											scope.actionSettings.pagesCount = data.pages;
											scope.actionSettings.pxCurrentPage = 2;

											if ( scope.actionSettings.pxCurrentPage <= data.pages) scope.loadMoreBtn.morePostsAvailable = true;
											else scope.loadMoreBtn.morePostsAvailable = false;

											scope.actionSettings.filterPostsTemplate = data.posts;
											scope.filterPostsTemplate.posts = data.posts;
											scope.loadMoreBtn.postsLoading = false;

											scope.directiveInfo.afterPostsLoadCallback();

									});
								});
 
								$j('.lscf-sorting-by .lscf-dropdown-option').each(function() {
									
									$j(this).click(function(){
										
										orderByID = $j(this).attr('rel');	

										if ( '0' == orderByID ) {

											//filter default by post_date
											orderByID = 'post_date';

											$j('.lscf-sorting-by').removeClass('active');

											$j('.lscf-sort-down').removeClass('active');											
											$j('.lscf-sort-up').removeClass('active');											

										} else {
											$j('.lscf-sorting-by').addClass('active');
										}
										for ( var c = 0; c < scope.actionSettings.activeQuery.length; c++ ) {
							
											field = scope.actionSettings.activeQuery[ c ];
											
											if ( 'order-by' == field.ID ) {

												scope.actionSettings.activeQuery[ c ].value = orderByID;
												
												field_exists = true;

												break;

											}
										}
										
										if ( false === field_exists ) {

											query = {
												"ID":"order-by",
												"filter_as":null,
												"type":"order-posts",
												"order":"ASC",
												"value":orderByID
											};

											scope.actionSettings.activeQuery.push( query );
										}

										scope.loadMoreBtn.postsLoading = true;

										customFilterService.getPosts( scope.postType, scope.actionSettings.postsPerPage, 1, scope.actionSettings.activeQuery, shortcodeSettings )
											.then(function( data ) {
												data = data.data;
												scope.actionSettings.postsCount = data.postsCount;
												scope.actionSettings.pagesCount = data.pages;
												scope.actionSettings.pxCurrentPage = 2;

												if ( scope.actionSettings.pxCurrentPage <= data.pages) scope.loadMoreBtn.morePostsAvailable = true;
												else scope.loadMoreBtn.morePostsAvailable = false;

												scope.actionSettings.filterPostsTemplate = data.posts;
												scope.filterPostsTemplate.posts = data.posts;
												scope.loadMoreBtn.postsLoading = false;

												scope.directiveInfo.afterPostsLoadCallback();

										});

									});
								});
							}
					});
				}
        };
    });

function lscfSortingCustomDropddown() {
	
	var $j = jQuery;

	$j('.lscf-sorting-custom-dropdown').each(function(){
		var dataClass = $j(this).attr('data-class');
		var $this=$j(this),
			numberOfOptions=$j(this).children('option').length;
		$this.addClass('s-hidden');
		$this.wrap('<div class="select '+dataClass+'"></div>');
		$this.after('<div class="styledSelect"></div>');
		var $styledSelect=$this.next('div.styledSelect');
		$styledSelect.text($this.children('option').eq(0).text());
		var $list=$j('<div />',{'class':'options'}).insertAfter($styledSelect);

		for ( var i=0; i < numberOfOptions; i++ ) {
			
			var listClassName = ( 0 === i ? 'lscf-dropdown-option pxselect-hidden-list' : 'lscf-dropdown-option' );
		
			$j('<div />',{
				text:$this.children('option').eq(i).text(),
				rel:$this.children('option').eq(i).val(),
				'class':listClassName
			}).appendTo( $list );
		}
		var $listItems = $list.children('.lscf-dropdown-option');
		$styledSelect.click(function(e){
			e.stopPropagation();
			$j('div.styledSelect.active').each(function(){

				$j(this).removeClass('active').next('div.options').hide();
			});

			$j(this).toggleClass('active').next('div.options').toggle();
			$j(this).toggleClass('active').next('div.options').customScrollbar();
		});

		$listItems.click(function(e){
			$listItems.removeClass('pxselect-hidden-list');
			$j(this).addClass('pxselect-hidden-list');
			e.stopPropagation();
			$styledSelect.text($j(this).text()).removeClass('active');
			$this.val($j(this).attr('rel'));
			$list.hide();
		});
		
		$j(document).click(function(){
			$styledSelect.removeClass('active');
			$list.hide();
		});

	});
}