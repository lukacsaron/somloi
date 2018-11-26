
<?php
if ( isset( PluginMainController::$plugin_settings['options'] ) ) {
	$lscf_settings = PluginMainController::$plugin_settings['options'];
} else {
	$lscf_settings = null;
}

$reset_button = array(
	'status' => 0,
	'name' => 'Reset',
	'position' => 'top',
);


$main_color = '#011695';

if ( null !== $lscf_settings && isset( $lscf_settings['main-color']['color'] ) ) {
	$main_color = $lscf_settings['main-color']['color'];
}

$writing = array(
	'load_more' 	=> 'Load more',
	'view'			=> 'View',
	'any'			=> 'Any',
	'select'		=> 'Select',
	'filter'		=> 'Filter',
	'add_to_cart' 	=> 'Add to Cart',
	'see_more'		=> 'See More',
	'see_less'		=> 'See Less',
	'no_results'	=> 'No Results',
	'sort_by'		=> 'Sort By',
	'sort_asc'		=> 'ASC',
	'sort_desc'		=> 'DESC',
	'date'			=> 'Date',
	'title'			=> 'Title',
	'close'			=> 'Close Filter',
	'search'		=> 'Search',
	'price'			=> 'Price',
	);

$filter_posts_per_page = ( ! isset( $lscf_settings['posts_per_page']['filter'] ) ? 15 : (int) $lscf_settings['posts_per_page']['filter'] );

$posts_only_posts_per_page = ( ! isset( $lscf_settings['posts_per_page']['posts_only'] ) ? 16 : (int) $lscf_settings['posts_per_page']['posts_only'] );

$reset_button['status'] = ( ! isset( $lscf_settings['reset_button']['status'] ) ? 0 : (int) $lscf_settings['reset_button']['status'] );

$reset_button['name'] = ( isset( $lscf_settings['reset_button']['name'] ) ? $lscf_settings['reset_button']['name'] : $reset_button['name'] );

$reset_button['position'] = ( isset( $lscf_settings['reset_button']['position'] ) ? $lscf_settings['reset_button']['position'] : $reset_button['position'] );

$block_view_as_default = ( ! isset( $lscf_settings['reset_button'] ) || ! isset( $lscf_settings['block_view'] ) ? 0 : (int) $lscf_settings['block_view'] );

if ( isset( $lscf_settings['writing'] ) ) {

	foreach ( $lscf_settings['writing'] as $key => $word ) {
		$writing[ $key ] = $word;
	}
}

?>
<div class="lscf-settings">

	<form name="lscf-settings" id="lscf-settings">
		
		<div class="lscf-opt">
			<strong>Select main color</strong>
			<input type="text" class="lscf-colorpick colorpick-rgba" value="<?php echo esc_attr( $main_color ); ?>" name="main-color"/>
		</div>

		<div class="lscf-opt">
			<strong>Filter page - posts per page</strong>
			<input type="number" value="<?php echo (int) $filter_posts_per_page; ?>" id="px-filter-posts-count"/>
		</div>

		<div class="lscf-opt">
			<strong> The posts display only - posts per page </strong>
			<input type="number" value="<?php echo (int) $posts_only_posts_per_page; ?>" id="px-posts-page-count"/>
		</div>

		<div class="lscf-opt">
			<strong> Show reset button into Filter page </strong>
			
			<div class="check-btn">		
				<input id="px-reset-button" <?php echo ( '1' == $reset_button['status'] ? 'checked="checked"' : '' ) ?> type="checkbox" name="filter-reset-button" class="px-checkbox-slide green"  name="display-review-system" value="1"/>
				<label for="px-reset-button" id="reset-button-checkbox">
					<span class="px-checkbox-slide-tracker"></span>
					<span class="px-checkbox-slide-bullet"></span>
				</label>
			</div>


			<div class="lscf-extra-opt <?php echo ( 1 == $reset_button['status'] ? 'active' :'' );?>" id="reset-button-extra-options" >

				<i>Button name:</i>
				<input type="text" name="reset-button-name" value="<?php echo esc_attr( $reset_button['name'] ); ?>" placeholder="Reset"/>
				<br/>
				<br/>
				<i>Position:</i>
				<input class="reset-button-position" <?php echo ( 'top' == $reset_button['position'] ? 'checked="checked"' : '' ); ?> type="radio" name="reset-button-possition" value="top"/>Top
				<input class="reset-button-position" <?php echo ( 'bottom' == $reset_button['position'] ? 'checked="checked"' : '' ); ?> type="radio" name="reset-button-possition" value="bottom"/>Bottom
			</div>

		</div>

		<div class="lscf-opt">
			
			<strong>"See More" writing</strong>
			<input class="right" type="text" id="lscf-see-more-writing" value="<?php echo esc_attr( $writing['see_more'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"See Less" writing</strong>
			<input class="right" type="text" id="lscf-see-less-writing" value="<?php echo esc_attr( $writing['see_less'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Select" writing</strong>
			<input class="right" type="text" id="lscf-select-writing" value="<?php echo esc_attr( $writing['select'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Any" writing</strong>
			<input class="right" type="text" id="lscf-any-writing" value="<?php echo esc_attr( $writing['any'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"View" writing</strong>
			<input class="right" type="text" id="lscf-view-writing" value="<?php echo esc_attr( $writing['view'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Search" writing</strong>
			<input class="right" type="text" id="lscf-search-writing" value="<?php echo esc_attr( $writing['search'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Price" writing</strong>
			<input class="right" type="text" id="lscf-price-writing" value="<?php echo esc_attr( $writing['price'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Load more" writing</strong>
			<input class="right" type="text" id="lscf-load-more-writing" value="<?php echo esc_attr( $writing['load_more'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Add to Cart" writing</strong>
			<input class="right" type="text" id="lscf-add-to-cart-writing" value="<?php echo esc_attr( $writing['add_to_cart'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Filter" writing(mobile)</strong>
			<input class="right" type="text" id="lscf-filter-mobile-writing" value="<?php echo esc_attr( $writing['filter'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Close" writing</strong>
			<input class="right" type="text" id="lscf-close-mobile-writing" value="<?php echo esc_attr( $writing['close'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"No results"</strong>
			<input class="right" type="text" id="lscf-filter-no-results-writing" value="<?php echo esc_attr( $writing['no_results'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Sort By"</strong>
			<input class="right" type="text" id="lscf-filter-sort-by-writing" value="<?php echo esc_attr( $writing['sort_by'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Date"</strong>
			<input class="right" type="text" id="lscf-filter-date-writing" value="<?php echo esc_attr( $writing['date'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>"Title"</strong>
			<input class="right" type="text" id="lscf-filter-title-writing" value="<?php echo esc_attr( $writing['title'] );?>"/>

		</div>

		<div class="lscf-opt">
			
			<strong>Set Grid View as default</strong>
			
			<div class="check-btn">		
				<input id="px-grid-view" <?php echo ( '1' == $block_view_as_default ? 'checked="checked"' : '' ) ?> type="checkbox" name="grid_view" class="px-checkbox-slide green"  name="display-review-system" value="1"/>
				<label for="px-grid-view">
					<span class="px-checkbox-slide-tracker"></span>
					<span class="px-checkbox-slide-bullet"></span>
				</label>
			</div>

		</div>

		<div class="saving-status">
			<img width="20" src="<?php echo esc_url( LSCF_PLUGIN_URL . 'assets/images/loading_light.gif' ) ?>"/>
			<span class="saved">Saved!</span>
		</div>
		
		<button id="lscf-save-settings" type="button" class="button button-primary px-button">Save</button>

	</form>

</div>
