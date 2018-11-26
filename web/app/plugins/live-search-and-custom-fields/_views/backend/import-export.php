<div class="px-plugin-wrapper">
	
	<div class="px-plugin-sidebar">

	</div>

	<div class="px-left-col">
		<div class="px-lfcf-logo">
			<img src="<?php echo esc_url( LSCF_PLUGIN_URL . 'assets/css/images/logo.jpg' ) ?>"/>
		</div>
	</div>
	<div class="clear"></div>

	<div class="px-plugin-container lscf-wrapper export-import-subpage">
		
		<h3 class="plugin-section-headline">Export Custom Posts list</h3>
		<label class="headline-label">Export all custom posts list to a json file</label>

		<div class="lscf-container white">
			<?php if ( is_array( $custom_posts_list ) && count( $custom_posts_list ) > 0 ) : ?>
				<form id="export-custom-posts-list" action="<?php echo esc_url( admin_url() . 'admin.php?page=lscf_export&pxact=export-cp' );?>" method="post">
					<?php wp_nonce_field( 'lscf-export-cpjson', 'export-custom-posts' ); ?>
					<button id="submit-export-custom-posts-list" class="button button-primary px-button">Export Custom Posts</button>
				</form>
			<?php else : ?>
				<strong>It seems like you don't have any custom post installed.</strong>
			<?php endif; ?>

		</div>

		<br/>
		<br/>
		<br/>

		<h3 class="plugin-section-headline">Import Custom Posts</h3>
		<label class="headline-label">Upload json file to import the custom posts</label>

		<div class="lscf-container white">
			
			<form name="lscf-import-cp" enctype="multipart/form-data" method="post" id="lscf-import-cp-form" action="<?php echo esc_url( admin_url() . 'admin.php?page=lscf_export&pxact=import-cp' );?>">

				<?php wp_nonce_field( 'lscf-importcp-json', 'lscf-import-cp-json' )?>
				<input type="file" name="import-json-custom-posts"/>
				<button id="lscf-submit-form-cp" class="button button-primary px-button">Import Custom Posts</button>

			</form>

		</div>

	</div>

	<div class="px-plugin-container lscf-wrapper export-import-subpage">
		
		<h3 class="plugin-section-headline">Export Custom fields for</h3>
		<label class="headline-label">Select the custom post type to export the custom fields</label>
		
		<div class="lscf-container white">
			
			<?php if ( is_array( $custom_posts_list ) && count( $custom_posts_list ) > 0 ) : ?>
				<form id="export-custom-fields-form" name="export-cf-tojson" action="<?php echo esc_url( admin_url() . 'admin.php?page=lscf_export&pxact=export-cf' );?>" method="post">
					<?php wp_nonce_field( 'lscf-export-cfjson', 'export-custom-fields' ); ?>
					<ul class="lscf-custom-posts">
						<li>
							<span>Select all custom posts</span>
							<input id="export-all-c_posts" type="checkbox" class="px-checkbox green" value="0"/>
							<label for="export-all-c_posts" class="px-checkbox-label"></label>
							<div class="clear"></div>
							<hr style="width:217px; float:left;"/>
						</li>
						<li></li>
						<?php foreach ( $custom_posts_list as $key => $title ) : ?>
							<li>
								<span><?php echo esc_attr( $title );?></span>

								<input id="<?php echo esc_attr( $key )?>" type="checkbox" class="px-checkbox green" name="custom-posts[]" value="<?php echo esc_attr( $key ) ?>"/>
								<label for="<?php echo esc_attr( $key )?>" class="px-checkbox-label"></label>

							</li>
						<?php endforeach;?>

					</ul>
					
					<button id="lscf-export-cf" class="button button-primary px-button">Export</button>
				</form>
			<?php else : ?>
				<strong>It seems like you don't have any custom post installed.</strong>
			<?php endif;?>
			
		</div>
		
		<br/>
		<br/>

		<h3 class="plugin-section-headline">Import Custom fields</h3>
		<label class="headline-label">Upload json file to import the custom fields</label>

		<div class="lscf-container white">
			
			<form name="lscf-import-cf" enctype="multipart/form-data" method="post" id="lscf-import-cf" action="<?php echo esc_url( admin_url() . 'admin.php?page=lscf_export&pxact=import-cf' );?>">

				<?php wp_nonce_field( 'lscf-importcf-json', 'lscf-import-json' )?>
				<input type="file" name="import-json-custom-fields"/>
				<button id="lscf-submit-import" class="button button-primary px-button">Import</button>

			</form>

		</div>

	</div>

</div>