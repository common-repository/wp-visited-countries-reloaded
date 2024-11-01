<div class="wrap nosubsub">
	<div id="icon-tools" class="icon32"></div><h2><?php _e( 'General Settings', 'wpvc-countries' ) ?></h2>
	<?php _e('<p>Welcome to WP Visited Countries Reloaded. On this screen, you can change the appearance of the map. To add countries you have visited, click on "Countries" in the menu.</p><p>To add the map on a post, use this shortcode and change the text according to your needs: <code>[wp-visited-countries width="600" height="300"]I\'ve visited {num} out of {total} countries. That is {percent}.[/wp-visited-countries]</code> You can also add a widget to your sidebar.</p>', 'wpvc-countries'); ?>
	<form method="post" action="options.php">
		<?php 
		settings_fields( WPVC_SETTINGS_KEY ); 
		do_settings_sections( WPVC_SETTINGS_KEY ); 
		?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wpvc-countries' ) ?>" />
			<input type="submit" class="button-secondary" name="wpvc_settings[reset]" value="<?php _e( 'Reset Defaults', 'wpvc-countries' ) ?>" />
		</p>					
	</form>
</div>

<!--
<script>
    jQuery(function($) {
        $('input[id*="hex"]').wpColorPicker();
    });
</script>
-->