<?php the_content(); ?>
<span class="ekho"><?php echo LSCF_PLUGIN_PATH; ?></span><br>
<span class="ekho"><?php echo LSCF_PLUGIN_URL; ?></span><br>
<span class="ekho"><?php echo LSCF_MAIN_PATH  ; ?></span><br>
<span class="ekho"><?php echo ABSPATH  ; ?></span><br>

<?php wp_link_pages(['before' => '<nav class="page-nav"><p>' . __('Pages:', 'sage'), 'after' => '</p></nav>']); ?>
