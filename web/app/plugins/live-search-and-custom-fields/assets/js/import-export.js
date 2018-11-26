jQuery(document).ready(function(){
	var importExportCF = new ImportExportCustomFields();
	importExportCF.init();
})
function ImportExportCustomFields(){
	
	var $j = jQuery,
		self = this,
		action = "px-plugin-ajax",
        ajaxURL = adminData.ajaxURL
	
	this.init = function(){
		
		$j("#lscf-export-cf").click(function(){
			$j('#export-custom-fields-form').submit();
		});
		
		$j("#submit-export-custom-posts-list").click(function(){
			$j('#export-custom-posts-list').submit();
		});
		
		$j("#lscf-submit-import").click(function(){
			$j('#lscf-import-cf').submit();
		});

		$j("#lscf-submit-form-cp").click(function(){

			$j('#lscf-import-cp-form').submit();
		});
		self.checkAllPostsInit();
	}
	
	this.serializeCustomPostsList = function(){

		var exportFieldsForm = document.forms["export-custom-fields"];
		var data = [];
		var hasFields = false;

		if ( typeof exportFieldsForm === 'undefined' ) { return false; }

		var formElements = exportFieldsForm.elements;
		for( key in formElements ) {
			if( self.isInt(key) ) {
				switch( formElements[key].type ) {

					case 'checkbox':
						
						if ( formElements[key].checked === true ) {
							data.push( formElements[key].value );
							hasFields = true;
						}

					break;
				}
			}
			
		}

		return hasFields ? data : false;
		
	}

	this.isInt = function(value){

		return !isNaN(value) && 
				parseInt(Number(value)) == value && 
				!isNaN(parseInt(value, 10));

	}

	this.exportToJson = function( data, callback ){
		
		$j.ajax({
			type:"POST",
			url:ajaxURL,
			data:{
				action:action,
				section:"export-custom-fields-to-json",
				postsList:data
			},
			success:function(data){
				callback( data );
			},
			dataType:"html"
		})	

	}

	this.checkAllPostsInit = function(){
		
		checked = false;
		$j("#export-all-c_posts").click(function(){
			checked = ! checked;
			if ( true === checked ) {
				$j('.lscf-custom-posts input[type="checkbox"]').each(function(){
					$j(this).attr({
						"checked":"checked"
					})
				})
			} else {
				$j('.lscf-custom-posts input[type="checkbox"]').each(function(){
					$j(this).removeAttr("checked");
				})
			}
		})
	}


}