angular.module(angAppName)
    .directive('liveSearch', function( $location, customFilterService){

		return{

			restrict:"AE",
			require: "?ngModel",
			scope:true,
			bindToController: true,
			controllerAs: 'vm',
			link: function( scope, elem, attrs, ngModel ) {

				var activePostsList = [],
					default_filter = null,
					postsFirstLoad = true,
					activeQuery = [],
					shortcodeSettings = lscfShortcodeData[ scope.actionSettings.controllerAttributes.index ];

				scope.postsHasLoaded = false;


				scope.buildLocationOnBlur = function(){

					var q = [];

					//when active query is null.
					//When first time is triggered the search and the defaul filter is empty as well.
					if ( null === activeQuery ) { 
						q.push({ ID: 'ajax-main-search', value: scope.pxsearch });
						scope.actionSettings.buildUrlLocation( q );
						return; 
					}

					var fieldExists = false;

					for ( var i = 0; i < activeQuery.length; i++ ) {

						if ( 'undefined' !== typeof activeQuery[ i ] ) {

							if ( 'undefined' !== typeof activeQuery[ i ].ID && 'ajax-main-search' === activeQuery[ i ].ID ) {
								if ( '' !== scope.pxsearch ) {
									activeQuery[ i ].value = scope.pxsearch;
								} else {
									delete activeQuery[ i ];
									activeQuery.length -= 1;
								}
								fieldExists = true;
							}
						}
					}

					q = activeQuery;
					if ( false === fieldExists && '' !== scope.pxsearch ) {
						q.push({ ID: 'ajax-main-search', value: scope.pxsearch });
					}

					scope.actionSettings.buildUrlLocation( q );
				};

				scope.loadPostsOnFocus = function() {
					
					if ( true === scope.actionSettings.lsLoadPosts ) {

						activeQuery = null;
	
						scope.postsHasLoaded = false;

						if ( 'undefined' !== typeof shortcodeSettings.settings.theme.posts_display_from && 
							'undefined' !== shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms && 
								shortcodeSettings.settings.theme.posts_display_from.post_taxonomies.active_terms.length > 0 ) 
						{
							default_filter = {
								"default_filter":{}
							};

							default_filter.default_filter.post_taxonomies = shortcodeSettings.settings.theme.posts_display_from.post_taxonomies;
							activeQuery = default_filter;
						}

						if ( scope.actionSettings.activeQuery.length > 0 ) {
							activeQuery = scope.actionSettings.activeQuery;
						}

						customFilterService.getAllPosts( scope.postType, 300, 1, activeQuery, shortcodeSettings )
							.then( function( data ) {
								data  = data.data;
								scope.postsHasLoaded = true;
								scope.actionSettings.lsLoadPosts = false;

								if ( true === postsFirstLoad ) { 
									lscfPostsList = new lscfPosts();
								}

								postsFirstLoad = false;
								posts = data.posts;

						});
					}
				};

				lscf_initLiveSearch( function() {

					if ( null !== scope.actionSettings.urlActiveFilters && scope.actionSettings.urlActiveFilters.hasGeneralSearch() ) {
						var generalSearches = scope.actionSettings.urlActiveFilters.activeFilters.generalSearch;
						var searchValue = generalSearches[0].value;
						scope.pxsearch = searchValue;
					}
					$j('.lscf-live-search-input').focus(function(){
						scope.loadPostsOnFocus();
					});

				});

				scope.$watch( "pxsearch", function( newVal, oldVal ) {

					if ( typeof newVal !== 'undefined' && newVal != oldVal ) {
						scope.loadMoreBtn.postsLoading = true;
					}

					scope.$watch( "postsHasLoaded", function( new_posts_status, old_posts_status ) {

						if ( true === new_posts_status ) {

							if ( typeof newVal !== 'undefined' && newVal != oldVal  ) {
								
								scope.$parent.loadMoreBtn.morePostsAvailable = false;
								var updatedPostList = [];



								if ( newVal !== '' && 'undefined' !== typeof posts ) {

									posts.forEach(function(post){
									
										var sTitleLong = pxDecodeEntities( post.title.long.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' ) );
										var sTitleShort = pxDecodeEntities( post.title.short.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' ) );
										var sContent = pxDecodeEntities ( post.content.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' ) );
							
										if ( newVal.toLowerCase() == sTitleShort.toLowerCase() ) {
											post.class_name = 'ls-matches-search';
										} else {
											post.class_name = '';
										}

										if ( sTitleLong.toLowerCase().indexOf(newVal.toLowerCase()) != -1 || post.full_content.toLowerCase().indexOf(newVal.toLowerCase()) != -1 ) {
												
												sTitleLong = sTitleLong.replace(new RegExp( '(' + newVal + ')', 'ig'), '<strong class="matched-word">' + "$1" + '</strong>');
												sTitleShort = sTitleShort.replace(new RegExp( '(' + newVal + ')', 'ig'), '<strong class="matched-word">' + "$1" + '</strong>');
												sContent = sContent.replace(new RegExp( '(' + newVal + ')', 'ig'), '<strong class="matched-word">' + "$1" + '</strong>');
												
												post.title.long = sTitleLong;
												post.title.short = sTitleShort;
												post.content = sContent;
												
												updatedPostList.push(post);
										}
										
									});
									
								}
								else {
									if ( 'undefined' !== typeof posts ) {

										scope.loadMoreBtn.postsLoading = true;

										posts.forEach(function(post){
										
											var sTitleLong = post.title.long.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' );
											var sTitleShort = post.title.short.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' );
											var sContent = post.content.replace( /(<strong(.*?)class\=\"matched-word\"\>)|(<\/strong\>)/ig, '' );
											
											post.title.long = sTitleLong;
											post.title.short = sTitleShort;
											post.content = sContent;
											
											updatedPostList.push(post);
												
										});
									}
									
								}
								
								scope.$parent.filterPostsTemplate.posts = updatedPostList;
								lscfPostsList.constructHover();
								scope.directiveInfo.afterPostsLoadCallback();
								scope.loadMoreBtn.postsLoading = false; 

							}
						}
					});
				});

				scope.$watch( "pxsearch_woo_sku", function( newVal, oldVal ) {

					if ( typeof newVal !== 'undefined' && newVal != oldVal ) {
						scope.loadMoreBtn.postsLoading = true;
					}

					scope.$watch( "postsHasLoaded", function( new_posts_status, old_posts_status ) {
						
						if ( true === new_posts_status ) {

							if ( typeof newVal !== 'undefined' && newVal != oldVal  ){
								
								scope.$parent.loadMoreBtn.morePostsAvailable = false;
								var updatedPostList = [];

								if (newVal !== '') {
									
									posts.forEach(function(post){
									
										if ( 'undefined' !== typeof post.woocommerce.sku && post.woocommerce.sku.toLowerCase().indexOf(newVal.toLowerCase()) != -1 ) {
												updatedPostList.push(post);
										}
									});
									
								}
								else{
									if ( 'undefined' !== posts ) {

										posts.forEach(function(post){
											updatedPostList.push(post);
										});
									}
								}

								scope.$parent.filterPostsTemplate.posts = updatedPostList;
								lscfPostsList.constructHover(); 

							}
						}
					});

				});
			}

		};

	});

	function lscf_initLiveSearch( callback ) {

		var $j = jQuery,
			self = this,
			checkInterval;

		this.ready = function(){

			if ( $j('.lscf-live-search-input').length && $j('.lscf-live-search-input').length > 0 ){
				clearInterval( checkInterval );
				callback();
			}
		};

		checkInterval = setInterval( self.ready(), 500 );

		setTimeout(function(){
			clearInterval( checkInterval );
		}, 3000);

	}