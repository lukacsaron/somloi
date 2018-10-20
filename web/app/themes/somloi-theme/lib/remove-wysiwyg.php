<?php
/**
 * Hide editor on specific pages.
 *
 */
function wpse_199918_wp_editor_settings( $settings, $editor_id ) {
  
  if ( get_the_ID() ) {
      $post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'] ;
      $template_file = get_post_meta($post_id, '_wp_page_template', true);

        if ( $editor_id === 'content' && $template_file === 'template-markup.php' ) {
            $settings['tinymce']   = false;
            $settings['quicktags'] = true;
            $settings['media_buttons'] = true;
        }

        return $settings;
    }
}
  

add_filter( 'wp_editor_settings', 'wpse_199918_wp_editor_settings', 10, 2 );