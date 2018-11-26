(function(){
	var $ = jQuery;
	$( document ).ready( function(){ 
		$( 'select.px-custom-select' ).each( function(){ 

			var dataClass = $(this).attr('data-class');
			var $this = $(this),
				numberOfOptions=$(this).children('option').length;
			
			$this.addClass('s-hidden');
			$this.wrap('<div class="select ' + dataClass + '"></div>');
			$this.after('<div class="styledSelect"></div>');
			var $styledSelect = $this.next('div.styledSelect');
			$styledSelect.text( $this.children('option').eq(0).text() );
			var $list = $('<ul />',{'class':'pxselect-options'}).insertAfter( $styledSelect );
		
			for ( var i = 0; i < numberOfOptions; i++ ) { 

				if ( $this.children('option').eq( i ).is( ':selected' ) && '' != $this.children('option').eq( i ).val() && 0 != $this.children('option').eq( i ).val() ) {
					$styledSelect.text( $this.children('option').eq( i ).text() );
				}

				$('<li />',{ text:$this.children('option').eq( i ).text(), rel:$this.children('option').eq( i ).val() }).appendTo( $list );

			}

			var $listItems = $list.children('li');
			$styledSelect.click( function( e ){ e.stopPropagation();
				$('div.styledSelect.active').each( function(){ $(this).removeClass('active').next('ul.pxselect-options').hide();});
				$(this).toggleClass('active').next('ul.pxselect-options').toggle();
			});

			$listItems.click(function(e){
				e.stopPropagation();
				$styledSelect.text($(this).text()).removeClass('active');
				$this.val($(this).attr('rel'));$list.hide();
			});

			$(document).click(function(){
				$styledSelect.removeClass('active');
				$list.hide();
			});
		});
	})
})();
