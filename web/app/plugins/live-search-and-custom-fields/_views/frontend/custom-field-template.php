<?php 
if ( isset( $custom_field['field_type'] ) ) :

	switch ( $custom_field['field_type'] ) :

		case 'px_text':
		?>
			<span
				class="lscf-text-cf lscf-shortcode-custom-field lscf-display-<?php echo esc_attr( $display_type )?>"
				id="<?php  echo esc_attr( $custom_field['ID'] ) ?>">
				
				<?php if ( 1 === $display_title ) : ?>
					<b class="lscf-custom-field-name"> <?php  echo esc_attr( __( $custom_field['name'], 'lscf' ) )?> </b>
				<?php endif;?>

				<label class="lscf-custom-field-value"><?php echo esc_attr( __( $custom_field['value'], 'lscf' ) ) ?></label>
			</span>
		<?php
		break;

		case 'px_date':

		?>
			<span 
				class="lscf-date-cf lscf-shortcode-custom-field lscf-display-<?php echo esc_attr( $display_type )?>" 
				id="<?php  echo esc_attr( $custom_field['ID'] ) ?>">

				<?php if ( 1 === $display_title ) : ?>
					<b class="lscf-custom-field-name"> <?php  echo esc_attr( __( $custom_field['name'], 'lscf' ) )?> </b>
				<?php endif;?>

				<label class="lscf-custom-field-value"><?php echo esc_attr( __( $custom_field['value'], 'lscf' ) ) ?></label>
			</span>
		<?php

		break;

		case 'px_icon_check_box':

		?>
			<span 
				class="lscf-icon-checkbox-cf lscf-shortcode-custom-field lscf-display-<?php echo esc_attr( $display_type )?>" 
				id="<?php  echo esc_attr( $custom_field['ID'] ) ?>">

				<?php if ( 1 === $display_title ) : ?>
					<b class="lscf-custom-field-name"> <?php  echo esc_attr( __( $custom_field['name'], 'lscf' ) )?> </b>
				<?php endif;?>

				<?php if ( isset( $custom_field['ivalue'] ) ) : ?>
					<?php foreach ( $custom_field['ivalue']  as $value ) : ?>
						<label class="lscf-custom-field-value">
							<span class="lscf-ic-icon" ><img src="<?php echo esc_url( $value['icon'] )?>"></span>
							<?php if ( isset( $icons_only ) && 0 === $icons_only ) :?>
								<span class="lscf-ic-value"><?php echo esc_attr( __( $value['opt'], 'lscf' ) )?></span>
							<?php endif;?>
						</label>
					<?php endforeach;?>
				<?php endif;?>

			</span>
		<?php

		break;

		case 'px_select_box':

		?>
			<span 
				class="lscf-dropdown-cf lscf-shortcode-custom-field lscf-display-<?php echo esc_attr( $display_type )?>" 
				id="<?php  echo esc_attr( $custom_field['ID'] ) ?>">

				<?php if ( 1 === $display_title ) : ?>
					<b class="lscf-custom-field-name"> <?php  echo esc_attr( __( $custom_field['name'], 'lscf' ) )?> </b>
				<?php endif;?>

				<label class="lscf-custom-field-value"><?php echo esc_attr( __( $custom_field['value'], 'lscf' ) )  ?></label>
			</span>
		<?php

		break;

		case 'px_check_box':
		?>
			<span 
				class="lscf-checkbox-cf lscf-shortcode-custom-field lscf-display-<?php echo esc_attr( $display_type )?>"
				id="<?php  echo esc_attr( $custom_field['ID'] ) ?>">
				
				<?php if ( 1 === $display_title ) : ?>
					<b class="lscf-custom-field-name"> <?php  echo esc_attr( __( $custom_field['name'], 'lscf' ) )?> </b>
				<?php endif;?>

				<?php foreach ( $custom_field['value'] as $value ) :?>
					<label class="lscf-custom-field-value"><?php echo esc_attr( __( $value, 'lscf' ) )  ?></label>
				<?php endforeach; ?>
			</span>
		<?php

		break;

		case 'px_radio_box':

		?>
			<span 
				class="lscf-radiobox-cf lscf-shortcode-custom-field lscf-display-<?php echo esc_attr( $display_type )?>"
				id="<?php  echo esc_attr( $custom_field['ID'] ) ?>">

				<?php if ( 1 === $display_title ) : ?>
					<b class="lscf-custom-field-name"> <?php  echo esc_attr( __( $custom_field['name'], 'lscf' ) )?> </b>
				<?php endif;?>

				<label class="lscf-custom-field-value"><?php echo esc_attr( __( $custom_field['value'], 'lscf' ) )  ?></label>
			</span>
		<?php

		break;

	endswitch;

endif;
