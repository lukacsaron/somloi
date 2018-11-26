angular.module(angAppName)
	.factory( "customFilterService", function($http, capfAPI){

		var isWpml = ( '1' === lscfShortcodeData[0].wpml ? 1 : 0 ),
			lang = ( false !== isWpml ? lscfShortcodeData[0].lang : null  );

		function getFilterFields( ID ){
			
			return $http({
				method:"post",
				url:capfAPI.restApi.getSidebar,
				data:{
					filter_id:ID
				}
			});
			
		}

		function getSidebar(  ){
			
			return $http({
				method: 'post',
				url: capfAPI.uri,
				data: {
					section: 'getSidebar'
				}
			});
			
		}

		function getPosts(postType, postsPerPage, page, q, shortcodeSettings ) {
			
			return $http({
				method: 'post',
				url: capfAPI.restApi.filterPosts,
				data: {
					post_type: postType,
					featured_label: shortcodeSettings.featuredLabel,
					limit: postsPerPage,
					page: page,
					q: q,
					is_wpml: isWpml,
					lang: lang,
					filter_id: shortcodeSettings.ID
				}
			});
			
		}
		function getAllPosts( postType, postsPerPage, page, q, shortcodeSettings ){
			return $http({
				method: 'post',
				url: capfAPI.restApi.filterPosts,
				data: {
					post_type: postType,
					featured_label: shortcodeSettings.featuredLabel,
					limit: postsPerPage,
					page: page,
					q: q,
					is_wpml: isWpml,
					lang: lang,
					filter_id: shortcodeSettings.ID
				}
			});
		}

		return{
			getAllPosts: getAllPosts,
			getFilterFields: getFilterFields,
			getPosts: getPosts,
			getSidebar: getSidebar
		};

	});