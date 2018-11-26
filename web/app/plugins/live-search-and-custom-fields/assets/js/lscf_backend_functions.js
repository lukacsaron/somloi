
function lscf_methods() {

	var self = this,
		$j = jQuery,
		action = "px-plugin-ajax",
		ajaxURL = adminData.ajaxURL;

	this.get_lscf_custom_fields = function( postType, fieldType, callback ){

		$j.ajax({
            type:"POST",
            url:ajaxURL,
            data:{
                action:action,
                section:"getPostType_customFields",
                fieldType:fieldType,
                post_type:postType
            },
            success: function (data) {     
                callback( data );
            },
            dataType:"json"
        });

	};

}
