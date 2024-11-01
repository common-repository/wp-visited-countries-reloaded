<?php
/**
 * Main class
 *
 * @package WPVC
 */
	

class WPVC_Master {
	
	/**
	 * Activates this plugin when it is first installed
	 *
	 * @access public
	 */
	public static function activate() {
		new WPVC_Settings( true );
		new WPVC_Countries( true );
	}
	
	public static function deactivate() {
	
	}

	/**
	 * Deactivates this plugin and removes all related option settings
	 *
	 * @access public
	 */
	public static function uninstall() {
		delete_option( WPVC_VERSION_KEY );	
		delete_option( WPVC_SETTINGS_KEY );	
		delete_option( WPVC_ADD_COUNTRIES_KEY );	
		unregister_widget( 'WPVC_Map_Widget' );					
	}
	
	public static function init() {
		add_option( WPVC_VERSION_KEY, WPVC_VERSION_NUM );
		// TODO: filter deprecated?
		//add_filter( 'plugin_action_links', array( 'WPVC_Master', 'add_action_links' ), 10, 2 );
		add_action( 'admin_menu',  array( 'WPVC_Master', 'add_pages' ) );
		add_shortcode( 'wp-visited-countries', array( 'WPVC_Master', 'handle_shortcode' ) );
		// TODO: add_filter( 'the_posts', array( 'WPVC_Master', 'enqueue_scripts' ) );
		
		//load the translated strings
		load_plugin_textdomain( 'wpvc-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		
		//add_action('admin_enqueue_scripts', array('WPVC_Master', 'admin_scripts'));
		add_action('admin_notices', array('WPVC_Master', 'admin_notice'));
		
		$option = get_option( WPVC_SETTINGS_KEY );
		$map_type = $option['bool_tiny_countries'] == 'true' ? 'worldHigh' : 'worldLow';
		wp_register_style('ammap', WPVC_URL.'ammap/ammap.css');
		wp_register_script('ammap', WPVC_URL.'ammap/ammap.js');
		wp_register_script('ammap_maps', WPVC_URL.'ammap/maps/js/'.$map_type.'.js', array('ammap'));
	}		
	
	/**
	 * Adds menu and sub-menu pages to the admin panel
	 *
	 * @access public
	 */
	public static function add_pages() {
		global $wpvc_settings_class, $wpvc_countries_class;
		
		//don't die without permission, just don't add the pages, jonas breuer, 13.11.2016
		if (function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
		
            if( !is_a( $wpvc_settings_class, 'WPVC_Settings' ) )
                $wpvc_settings_class = new WPVC_Settings();
        
            if( !is_a( $wpvc_countries_class, 'WPVC_Countries' ) )
                $wpvc_countries_class = new WPVC_Countries();
        
            add_menu_page( 'Visited Countries', 'Visited Countries', 'manage_options', 'wpvc-settings', '', 'dashicons-palmtree' );
            $s_page = add_submenu_page( 'wpvc-settings', 'Manage Settings', 'Settings', 'manage_options', 
                    'wpvc-settings', array( 'WPVC_Master', 'display_settings' ) );
            $c_page = add_submenu_page( 'wpvc-settings', 'Manage Countries', 'Countries', 'manage_options', 
                    'wpvc-countries', array( 'WPVC_Master', 'display_countries' ) );
        
            $wpvc_settings_class->add_actions( $s_page );
            $wpvc_countries_class->add_actions( $c_page );
            
        }
	}

	public static function display_settings() {
		require_once WPVC_PATH . 'inc/wpvc-settings.php';
	}

	public static function display_countries() {
		
		require_once WPVC_PATH . 'inc/class-wpvc-list-table.php';

		$wpvc_list_table = new WPVC_Country_List_Table();
		
		require_once WPVC_PATH . 'inc/wpvc-countries.php';
	
	}
	
	/**
	 * Handles shortcode [wp-visited-countries] to display a map with specified attributes
	 *
	 * @access public
	 *
	 * @param array $atts Attributes (width and height)
	 * @return string
	 */
	public static function handle_shortcode( $atts, $content = '' ) {
	
		extract( shortcode_atts( array(
			'width' => '',
			'height' => '',
			'id' => null,
		), $atts ) );
		
		wp_enqueue_style('ammap');
		wp_enqueue_script('ammap');
		wp_enqueue_script('ammap_maps');
		
		$option = get_option( WPVC_SETTINGS_KEY );
		
		$bgcolor = $option['hex_water'];
		
		if (empty($id)) $id = 'wpvc-jscontent';
		
		if (empty($bgcolor)) $bgcolor = WPVC_DEFAULT_MAP_WATER;
		
		if (substr($bgcolor, 0, 1) !== '#') $bgcolor = '#'.$bgcolor;
			
		if( empty( $width ) ) {
			if( ! empty( $option[ 'int_map_width' ] ) )
				$width = $option[ 'int_map_width' ];
			else
				$width = WPVC_DEFAULT_MAP_WIDTH;
		}
			
		if( empty( $height ) ) {
		
			if( ! empty( $option[ 'int_map_height' ] ) )
				$height = $option[ 'int_map_height' ];
			else
				$height = WPVC_DEFAULT_MAP_HEIGHT;
		}

		$areas = array();
		$countries = get_option( WPVC_ADD_COUNTRIES_KEY );
		foreach ($countries as $country_id => $country) {
		    if (!empty($country['hex_country'])) $color = $country['hex_country'];
		    elseif ($country['int_visited'] == 1) $color = $option['hex_visited'];
		    else $color = $option['hex_not_visited'];
		    $color = '#'.$color;
		    
		    $roll_over_color = !empty($country['hex_hover']) ? $country['hex_hover'] : $option['hex_hover'];
		    $roll_over_color = '#'.$roll_over_color;
		
		    $country_area = new StdClass();
		    $country_area->id = $country_id;
		    $country_area->color = $color;
		    $country_area->rollOverColor = $roll_over_color;
		    $country_area->selectedColor = $roll_over_color;
		    $country_area->balloonText = '<b>[[title]]</b><br>'.esc_html($country['txt_desc']);
		    if (!empty($country['url_country'])) $country_area->url = 'http://'.$country['url_country'];
		    $areas[] = $country_area;
		}
		$json_areas = json_encode($areas);
		
		$map_type = $option['bool_tiny_countries'] == 'true' ? 'worldHigh' : 'worldLow';
		$width_unit  = $width  > 100 ? 'px' : '%';
		$height_unit = $height > 100 ? 'px' : '%';
		
		$script = '
            AmCharts.makeChart( "' . $id . '", {
                
                "type": "map",
                "fontFamily":"'.$option['font_balloon_txt'].'",
                
                "dataProvider": {
                    "map": "'.$map_type.'",   
                    "getAreasFromMap": true,
                    "areas": '.$json_areas.'
                },

                "areasSettings": {
                    "autoZoom": true,
                    "rollOverOutlineAlpha": 0,
                    "balloonText": "",
                    "color": "#'.$option['hex_normal'].'",
                    "rollOverColor": "#'.$option['hex_hover'].'",
                    "selectedColor": "#'.$option['hex_hover'].'",
                },

                "smallMap": {
                    "enabled":'.$option['bool_smap'].',
                    "backgroundColor": "#'.$option['hex_smap_bg'].'",
                    "mapColor": "#'.$option['hex_smap'].'",
                    "borderColor": "#'.$option['hex_smap_border'].'",
                    "rectangleColor": "#'.$option['hex_smap_rectangle'].'"
                },
                
                "zoomControl": {
                    "zoomControlEnabled": '.$option['bool_zoom'].',
                    "buttonFillColor": "#'.$option['hex_zoom_bg'].'",
                    "buttonRollOverColor": "#'.$option['hex_zoom_hover'].'"
                },
                
                "balloon": {
                    "fontSize":"'.$option['int_balloon_txt'].'",
                    "color":"'.$option['hex_balloon_txt'].'",
                    "fillColor":"#'.$option['hex_balloon_bg'].'"
                }
                
            } );
        ';
        
        wp_add_inline_script('ammap_maps', $script);
		
		$description = self::parse_text( $content );
		if (!empty($description)) $description = '<div class="wpvc-description">' . $description . "</div>";
		
		$output = '<div id="' . esc_attr($id) . '" style="width: '. esc_attr($width . $width_unit) . '; height: ' . esc_attr($height . $height_unit) . '; background-color:#'.esc_attr($option['hex_water']).'"></div>'.$description;
		return $output;
	}
	
	/**
	 * Analyze input text. If the text contains {num}, {total}, and/or {percent}
	 * it will be changed to the corresponding numbers
	 *
	 * @access public
	 *
	 * @param string $txt
	 * @return string The modified text
	 */
	public static function parse_text( $txt ) {
		if( empty( $txt ) )
			return '';
		
		$txt = str_replace( '{total}', WPVC_TOTAL_COUNTRIES, $txt );
		
		if( strpos( $txt, '{num}' ) !== false || strpos( $txt, '{percent}' ) !== false ) {
			
			$option = get_option( WPVC_ADD_COUNTRIES_KEY );
			$num = 0;
			
			if( $option )
				$num = count( $option ) ;
			
			$percent = number_format( $num/WPVC_TOTAL_COUNTRIES * 100, 2 ) . '%';
			
			$txt = str_replace( '{num}', $num, $txt );
			$txt = str_replace( '{percent}', $percent, $txt );
		}
		
		return $txt;
	}
	

	/**
	 * TODO: this one only works for pages/posts. Not for plugin. So this function is not used yet 
	 * Based on: http://beerpla.net/2010/01/13/wordpress-plugin-development-how-to-include-css-and-javascript-conditionally-and-only-when-needed-by-the-posts/
	 */
	public static function enqueue_scripts( $posts ){
		if (empty($posts)) return $posts;
	 
		foreach ($posts as $post) {
			
			if( stripos( $post->post_content, '[wp-visited-countries' ) !== false ) {
				
				wp_enqueue_script( 'swfobject', WPVC_URL . 'ammap/swfobject.js' );
				wp_enqueue_script( 'ammap', WPVC_URL . 'ammap/ammap.js', array('swfobject') );
				
				break;
			}
		}
	 
		return $posts;
	}
	
	/**
	 * Adds a shortcut link in the plugin page to the main settings page
	 *
	 * @access public
	 *
	 * @return array
	 */
	public static function add_action_links($links, $file) {
		static $this_plugin;

		if (!$this_plugin) {
			$this_plugin = plugin_basename(__FILE__);
		}

		if ($file == $this_plugin) {
			// The "page" query string value must be equal to the slug
			// of the Settings admin page, i.e. wpvc-settings
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpvc-settings">Settings</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}
	
	
	public static function admin_scripts() {
	    wp_enqueue_style('wp-color-picker');
	    wp_enqueue_script('wp-color-picker'); 
	}
	
	
	
	public static function admin_notice() {
	    $current_screen = get_current_screen();
	    if ($current_screen->parent_base != "wpvc-settings") return;
	    
	    $settings = get_option(WPVC_META_OPTIONS_KEY);
	    if (!isset($settings['infotext']) || empty($settings['infotext'])) return;
	    
	    echo '<div class="notice notice-warning"><p>'.esc_html($settings['infotext']).'</p></div>';
	}
	
}
?>