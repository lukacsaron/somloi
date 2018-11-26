
jQuery(document).ready(function(){
	
	filterThemeSettings();

});

function filterThemeSettings(){
	
	var $j = jQuery,
		self = this;
	
	this.init = function (){

		self.initResetButtonExtraOpt();
		self.manageExtraThemeOptions();

		$j('.filter-main-color').colorPicker();

	};
	
	this.initResetButtonExtraOpt = function () {
		
		var isResetButtonActive =  ( $j('#px-reset-button:checked').length > 0 ? 1 : 0 );
		
		if ( 1 == isResetButtonActive ) {
			$j('#single-filter-reset-button-opt').fadeIn();
		}

		$j('#reset-button-checkbox').click(function() {

			var isResetButtonActive =  ( $j('#px-reset-button:checked').length > 0 ? 1 : 0 );
			
			if ( 1 == isResetButtonActive ) {
				$j('#single-filter-reset-button-opt').hide();
			} else {
				$j('#single-filter-reset-button-opt').fadeIn();
			}

		});
	};

	this.manageExtraThemeOptions = function(){

		$j('.theme-style-type').on('click', function(){

			var columnsData = parseInt( $j(this).attr('data-columns') ),
				viewChanger = parseInt( $j(this).attr('data-viewchanger') ),
				linkType	= parseInt( $j(this).attr('data-link-type') );

			if ( 1 == viewChanger ) {
				$j('.pxfilter-style-multiple-views').fadeIn();
			} else {
				$j('.pxfilter-style-multiple-views').hide();
			}

			if ( 0 == columnsData ) {
				$j('.theme-columns-opt').hide();
			} else {
				$j('.theme-columns-opt').fadeIn();
			}

			if ( 1 == linkType ) {
				$j('.pxfilter-style-link-type').fadeIn();
			} else {
				$j('.pxfilter-style-link-type').hide();
			}
		});	

	};

	return this.init();

};
