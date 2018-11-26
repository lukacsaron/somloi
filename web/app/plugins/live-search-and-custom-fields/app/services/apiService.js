angular.module(angAppName)
	.factory( "capfAPI", function($http){

		var URI = pxData.ajaxURL+"?action=px-ang-http",
			restApiUri = lscfShortcodeData[0].rest_api_uri + 'lscf_rest/';

		return{
			uri: URI,
			restApi: {
				uri: restApiUri,
				getSidebar: restApiUri + 'get_sidebar',
				filterPosts: restApiUri + 'filter_posts'
			},

		};

	});