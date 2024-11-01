<?php
/*
Plugin Name: WP Visited Countries Reloaded
Plugin URI: http://www.j-breuer.com/
Version: 3.1.2
Description: Creates and shows your visited countries map to your visitors
Author: Amalia S., Jonas Breuer
Author URI: http://www.j-breuer.com
Min WP Version: 3.5
Max WP Version: 5.8.2
Text Domain: wpvc-plugin
*/

/*
Copyright (C) 2018  Jonas Breuer

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define( 'WPVC_URL', plugin_dir_url( __FILE__ ) );
define( 'WPVC_PATH', WP_PLUGIN_DIR.'/wp-visited-countries-reloaded/' );

/* keys for options and/or Settings API */
define( 'WPVC_VERSION_KEY', 'wpvc_version' );
define( 'WPVC_SETTINGS_KEY', 'wpvc_settings' );
define( 'WPVC_ADD_COUNTRIES_KEY', 'wpvc_countries' );
define( 'WPVC_EDIT_COUNTRIES_KEY', 'wpvc_country' );
define( 'WPVC_META_OPTIONS_KEY', 'wpvc_meta_options' );

define( 'WPVC_VERSION_NUM', '3.1.2' );
define( 'WPVC_DEFAULT_MAP_WIDTH', 700 );
define( 'WPVC_DEFAULT_MAP_HEIGHT', 400 );
define( 'WPVC_DEFAULT_MAP_WATER', 'E0E0E0' );
define( 'WPVC_TOTAL_COUNTRIES', 217 );

if( is_admin() ) {
	require_once WPVC_PATH . 'inc/class-wpvc-base.php';
	require_once WPVC_PATH . 'inc/class-wpvc-settings.php';
	require_once WPVC_PATH . 'inc/class-wpvc-countries.php';

	$wpvc_settings_class;
	$wpvc_countries_class;
}

require_once WPVC_PATH . 'inc/class-wpvc-widget.php';
require_once WPVC_PATH . 'inc/class-wpvc-master.php';

register_activation_hook( __FILE__,  array( 'WPVC_Master', 'activate' ) );
register_deactivation_hook( __FILE__,  array( 'WPVC_Master', 'deactivate' ) );
register_uninstall_hook( __FILE__,  array( 'WPVC_Master', 'uninstall' ) );
add_action( 'init', array( 'WPVC_Master', 'init' ) );
add_action( 'widgets_init', array( 'WPVC_Map_Widget', 'load_widget' ) );
?>