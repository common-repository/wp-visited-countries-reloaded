<?php
/**
 * Class for handling countries admin pages
 *
 * @package WPVC
 */

if ( ! class_exists( 'WPVC_Countries' ) && is_admin() ) {
	
	class WPVC_Countries extends WPVC_Base {
		
		function __construct( $activate  = false ) {
			
			parent::__construct( WPVC_ADD_COUNTRIES_KEY, array(), 'wpvc-countries', 'ammap_data', $activate );
			
			add_action( 'admin_init', array( &$this, 'init' ) );
		}
		
		private function generate_defaults() {
			//visited
			$new = array();
			$new['country_name'] = 'United States_US';
			$new['txt_desc'] = "Travelled around the East Coast in 2000";
			$new['int_visited'] = 1;
			$new['hex_country'] = '';
			$new['hex_hover'] = '';
			$new['url_country'] = '';
			
			$d['US'] = $new;
			
			//lived
			$new = array();
			$new['country_name'] = 'Australia_AU';
			$new['txt_desc'] = "Lived here from 2004 to 2007";
			$new['int_visited'] = 2;
			$new['hex_country'] = '001CEB';
			$new['hex_hover'] = '';
			$new['url_country'] = 'wordpress.org';

			$d['AU'] = $new;				
			
			return $d;
		}
		
		function init() {
			parent::init();
			
			//section for adding a new country
			add_settings_section( $this->option_name.'_add', __( 'Add New Country', 'wpvc-plugin' ), 
					array( &$this, 'print_content_add' ), $this->option_name );
			
			//section for editing a country
			add_settings_section( $this->option_name.'_edit', '', 
					array( &$this, 'print_content_edit' ), WPVC_EDIT_COUNTRIES_KEY );
			
			if( $this->is_delete() )
				update_option( WPVC_ADD_COUNTRIES_KEY, $this->get_option() );
		}
		
		public function print_content_add() {
			self::print_content_section('add');
		}
		
		public function print_content_edit() {
			self::print_content_section('edit');
		}
		
		/**
		 * Prints out hidden value & nonce
		 *
		 * @access public
		 *
		 * @param string $val Section type (add or edit)
		 */
		public function print_content_section( $val ) {
			echo '<input type="hidden" name="wpvc_section" value="' . esc_attr($val) . '" />';
			echo wp_nonce_field( $this->option_name.'_'.$val, $this->option_name.'_nonce' );
		}
		
		function is_delete() {
			return ( isset( $_REQUEST['country'] ) && ( $_REQUEST['action'] === 'delete' || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete') ) );
		}
		
		/**
		 * Validates and parses input fields upon submission. Empty values
		 * are replaced by default values if necessary
		 *
		 * @access public
		 *
		 * @param array $input
		 * @return array
		 */
		function validate( $input ) {
			$options = $this->get_option();
			
			if( isset( $_REQUEST['country'] ) && ( $_REQUEST['action'] === 'delete' || $_REQUEST['action2'] == 'delete' ) ) {
				// delete one or more countries
				
				$country = ctype_alpha($_REQUEST['country']) ? $_REQUEST['country'] : '';
				$count = 0;
				
				if (is_array($delete)) {
					// handle deletion for more than 1 country
					
					$this->verify_nonce( '_wpnonce', 'bulk-countries', false);
					
					foreach ($delete as $del) {
						unset( $options[$del] );
						$count++;
					}
				} else {
					// handle deletion for 1 country
					
					$this->verify_nonce( '_wpnonce', 'wpvc_nonce_list_table', false);
					unset( $options[ $_REQUEST['country'] ] );
					$count = 1;
				}
				unset($_REQUEST['country']);
				
				wp_redirect( add_query_arg('deleted', $count, admin_url( 'admin.php?page=wpvc-countries' ) ) );
				
			} else if( $_REQUEST['action'] == '-1' ) {
				
				die("[WPVC_Countries:validate] Something is wrong");
			
			} else {
				//edit or add a country
				
				$country_code = $this->get_country_key( $input['country_name'] );
				$country_name = $this->get_country_name( $input['country_name'] );
				
				//check if it attempts to add a  new country
				if(	$this->get_request('wpvc_section') === 'add' ) {
					//adding a new country
					
					$this->verify_nonce( $this->option_name.'_nonce', $this->option_name.'_add' );
					
					//check if duplication is detected
					if (is_array( $_REQUEST['wpvc_countries'] ) 
							&& isset ( $options[ $this->get_country_key( $_REQUEST['wpvc_countries']['country_name'] ) ] ) ) {
					
						//create an error because of duplication
						$this->add_error( 'country_duplicate', 
								sprintf( __( 'Country %s already exists. Unable to add the same country.', 'wpvc-plugin' ), $country_name ) );
						return $options;
					}
					
					//no duplication is detected, create a successful message
					$message = sprintf( __( 'Country %s has been added.', 'wpvc-plugin' ), $country_name );
				
				} else {
					//editing a country
					
					$this->verify_nonce( $this->option_name.'_nonce', $this->option_name.'_edit' );
					
					$message = sprintf( __( 'Country %s has been edited.', 'wpvc-plugin' ), $country_name );
				}
				
				// verifying and validating the inputs
				$input = $this->verify_type( $input, $message );
				
				if( !empty( $input['url_country'] )) {
					$input['url_country'] = esc_url( 'http://' . $input['url_country'], array( 'http', 'https' ) );
					$input['url_country'] = str_replace( 'http://', '', $input['url_country'] );
				}
				
				if( empty( $country_code ) )
					die( "[WPVC_Countries:validate] Country code is missing" );
					
				$options[ $country_code ] = $input;
			}
			
			// write changes to xml
			//$this->prepare_xml( $options );
			
			return $options;
		}
		
		/**
		 * Defines and adds fields
		 *
		 * @access public
		 */
		public function populate_fields() {
			
			$fields = array(
				array( 
					'id' => 'country_name',
					'type' => 'select_country',
					'title' => __( 'Country', 'wpvc-plugin' ),
					'size' => '',
					'tags' => '',
					'prefield' => '',
					'postfield' => '',
					'section' => ''
				),
				array( 
					'id' => 'int_visited',
					'type' => 'select_country_value',
					'title' => __( 'Visited/Lived?', 'wpvc-plugin' ),
					'size' => '4',
					'tags' => 'maxlength="4"',
					'prefield' => '',
					'postfield' => ' px',
					'section' => ''
				),
				array( 
					'id' => 'txt_desc',
					'type' => 'input',
					'title' => __( 'Description', 'wpvc-plugin' ),
					'size' => '',
					'tags' => '',
					'prefield' => '',
					'postfield' => $this->parse_desc( __( 'Description is shown when a country is hovered', 'wpvc-plugin' ) ),
					'section' => ''
				),
				array( 
					'id' => 'hex_country',
					'type' => 'input',
					'title' => __( 'Country Color', 'wpvc-plugin' ),
					'size' => self::field_color_size,
					'tags' => 'maxlength="'.self::field_color_size.'"',
					'prefield' => '# ',
					'postfield' => $this->parse_desc( 
							__( 'Hex color code for the country. Default color will be used if not set', 'wpvc-plugin' ) ),
					'section' => ''
				),
				array( 
					'id' => 'hex_hover',
					'type' => 'input',
					'title' => __( 'Hover Color', 'wpvc-plugin' ),
					'size' => self::field_color_size,
					'tags' => 'maxlength="'.self::field_color_size.'"',
					'prefield' => '# ',
					'postfield' => $this->parse_desc( 
							__( 'Hover color for the country. Default color will be used if not set', 'wpvc-plugin' ) ),
					'section' => ''
				),
				array( 
					'id' => 'url_country',
					'type' => 'input',
					'title' => __( 'URL', 'wpvc-plugin' ),
					'size' => 30,
					'tags' => '',
					'prefield' => 'http://',
					'postfield' => '<br />' . $this->parse_desc( __( 'The URL which can be accessed by clicking the country. '.
							'Example: <code>wordpress.org</code> &#8212; do NOT include the <code>http://</code>', 'wpvc-plugin' ) ),
					'section' => ''
				)
			);
			
			foreach( $fields as $f ) {
				$f['name'] = $this->option_name.'['.$f['id'].']';
				//$f['id'] = $class->option_name.$f['id'];
				
				$label = array( 'label_for' => $f['name'] );
				$args = array_merge($f);
				
				//need to display fields in both add and edit country pages
				if( $this->get_request( 'action' ) == 'edit' )
					$this->add_field( $f['id'], $f['title'], $this, 'print_fields', WPVC_EDIT_COUNTRIES_KEY, $this->option_name.'_edit', $args );
				else
					$this->add_field( $f['id'], $f['title'], $this, 'print_fields', $this->option_name, $this->option_name.'_add', $args );
			}
			
		}
		
		/**
		 * Print each field according to its type
		 *
		 * @access public
		 *
		 * @param array $field
		 */
		public function print_fields($field) {
			$action = $this->get_request('action');
			$delete_action = ( $action  == 'delete' );
			$add_action = ( !$delete_action && $action !== 'edit' );
			
			$options = $this->get_option();
			$data = null;
			
			if( !$delete_action && isset( $_REQUEST['country'] ) && !empty( $_REQUEST['country'] ) ) {
				// if we land in edit country page
				$country = ctype_alpha($_REQUEST['country']) ? $_REQUEST['country'] : '';
				$data = $options[$country];	
				$data['url_country'] = str_replace( 'http://', '', $data['url_country'] );
			} else {
				$country = '';
			}
			
			switch( $field['type'] ) {
				
				case 'input':
					$this->print_input($field, $data);
				break;
				
				case 'select_country_value':
					printf( 
						'<select name="%s" id="%s"><option value="1"%s>%s</option><option value="2"%s>%s</option></select>'
						, $field['name']																//input name
						, $field['name']																//input id
						, ( isset($data) && $data['int_visited'] == 1 ) ? ' selected="selected"' : ''	//selected tag for value "Visited"
						, __( 'Visited', 'wpvc-plugin' )
						, ( isset($data) && $data['int_visited'] == 2 ) ? ' selected="selected"' : ''	//selected tag for value "Lived"
						, __( 'Lived', 'wpvc-plugin' )
					);
				break;
				
				case 'select_country':
					printf( 
						'<select name="%s" id="%s"%s>', 
						$field['name'], $field['name'], 
						( $action  == 'edit' ? ' disabled="disabled"' : '' )
					);
					if( $add_action || $delete_action ) {
						foreach( $this->get_countries() as $key => $name ) {
							printf( '<option value="%s_%s">%s</option>', $name, $key, $name );
						}
						echo '</select>';
						
					} else {
						$key = $this->get_country_key( $data['country_name'] );
						$name = $this->get_country_name( $data['country_name'] );
						
						printf( '<option value="%s_%s">%s</option></select>', $name, $key, $name );
						printf( '<input type="hidden" name="%s" value="%s" />', $field['name'], $data['country_name']);
					}
					
					break;
			}
		}
		
		protected function get_request( $var ) {
			return( isset( $_REQUEST[$var] ) ? sanitize_key($_REQUEST[$var]) : '' );
		}
		
		private function get_country_key( $name ) {
			$temp = explode( "_", $name);
			return $temp[1];
		}
		private function get_country_name( $name ) {
			$temp = explode( "_", $name);
			return $temp[0];
		}
		
		protected function prepare_xml( $countries = null ) {
			if( empty( $countries ) )
				$countries = $this->get_option();
			
			$data = '<?xml version="1.0" encoding="UTF-8"?>'. "\n\n"
				. '<map map_file="world3.swf" zoom="100%" zoom_x="7%" zoom_y="-8%">' . "\n\t<areas>";
			
			foreach ($countries as $country) {
			
				$key = $this->get_country_key( $country['country_name'] );
				$name = $this->get_country_name( $country['country_name'] );
				$country['hex_country'] = $this->add_hashtag( $country['hex_country'] );
				$country['hex_hover'] = $this->add_hashtag( $country['hex_hover'] );
				
				if ( !empty( $country['txt_desc'] ) )
					$country['txt_desc'] = '<br /><p>' . $country['txt_desc'] . '</p>';
				
				if ( !empty( $country['url_country'] ) )
					$country['url_country'] = 'http://' . $country['url_country'];
				
				if ( !empty( $country['txt_desc'] ) ) 
					$country['txt_desc'] = '<br /><p>'.$country['txt_desc'].'</p>';
					
				$data .= "
		<area mc_name=\"$key\" title=\"$name\" value=\"$country[int_visited]\" "
			. "url=\"$country[url_country]\" color=\"$country[hex_country]\" color_hover=\"$country[hex_hover]\" target=\"_blank\">
			<description>
				<![CDATA[$country[txt_desc]]]>
			</description>
		</area>";
		
			}
			
			$data .= "\n\t</areas>\n</map>";
			
			//$this->write_xml( $data );
		}

		private function get_countries() {
			$countries = array( 'AD' => 'Andorra', 'AE' => 'United Arab Emirates', 'AF' => 'Afghanistan', 'AG' => 'Antigua and Barbuda', 'AI' => 'Anguilla', 'AL' => 'Albania', 'AM' => 'Armenia', 'AO' => 'Angola', 'AR' => 'Argentina', 'AS' => 'American Samoa', 'AT' => 'Austria', 'AU' => 'Australia', 'AW' => 'Aruba', 'AX' => 'Aland Islands', 'AZ' => 'Azerbaijan', 'BA' => 'Bosnia and Herzegovina', 'BB' => 'Barbados', 'BD' => 'Bangladesh', 'BE' => 'Belgium', 'BF' => 'Burkina Faso', 'BG' => 'Bulgaria', 'BH' => 'Bahrain', 'BI' => 'Burundi', 'BJ' => 'Benin', 'BL' => 'Saint Barthelemy', 'BN' => 'Brunei Darussalam', 'BO' => 'Bolivia', 'BM' => 'Bermuda', 'BQ' => 'Bonaire, Sint Eustatius and Saba', 'BR' => 'Brazil', 'BS' => 'Bahamas', 'BT' => 'Bhutan', 'BV' => 'Bouvet Island', 'BW' => 'Botswana', 'BY' => 'Belarus', 'BZ' => 'Belize', 'CA' => 'Canada', 'CC' => 'Cocos (Keeling) Islands', 'CD' => 'Democratic Republic of Congo', 'CF' => 'Central African Republic', 'CG' => 'Republic of Congo', 'CH' => 'Switzerland', 'CI' => 'Côte d\'Ivoire', 'CK' => 'Cook Islands', 'CL' => 'Chile', 'CM' => 'Cameroon', 'CN' => 'China', 'CO' => 'Colombia', 'CR' => 'Costa Rica', 'CU' => 'Cuba', 'CV' => 'Cape Verde', 'CW' => 'Curaçao', 'CX' => 'Christmas Island', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DE' => 'Germany', 'DJ' => 'Djibouti', 'DK' => 'Denmark', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'DZ' => 'Algeria', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'EE' => 'Estonia', 'EH' => 'Western Sahara', 'ER' => 'Eritrea', 'ES' => 'Spain', 'ET' => 'Ethiopia', 'FI' => 'Finland', 'FJ' => 'Fiji', 'FK' => 'Falkland Islands', 'FM' => 'Federated States of Micronesia', 'FO' => 'Faroe Islands', 'FR' => 'France', 'GA' => 'Gabon', 'GB' => 'United Kingdom', 'GE' => 'Georgia', 'GD' => 'Grenada', 'GF' => 'French Guiana', 'GG' => 'Guernsey', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GL' => 'Greenland', 'GM' => 'Gambia', 'GN' => 'Guinea', 'GO' => 'Glorioso Islands', 'GP' => 'Guadeloupe', 'GQ' => 'Equatorial Guinea', 'GR' => 'Greece', 'GS' => 'South Georgia and South Sandwich Islands', 'GT' => 'Guatemala', 'GU' => 'Guam', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HK' => 'Hong Kong', 'HM' => 'Heard Island and McDonald Islands', 'HN' => 'Honduras', 'HR' => 'Croatia', 'HT' => 'Haiti', 'HU' => 'Hungary', 'ID' => 'Indonesia', 'IE' => 'Ireland', 'IL' => 'Israel', 'IM' => 'Isle of Man', 'IN' => 'India', 'IO' => 'British Indian Ocean Territory', 'IQ' => 'Iraq', 'IR' => 'Iran', 'IS' => 'Iceland', 'IT' => 'Italy', 'JE' => 'Jersey', 'JM' => 'Jamaica', 'JO' => 'Jordan', 'JP' => 'Japan', 'JU' => 'Juan De Nova Island', 'KE' => 'Kenya', 'KG' => 'Kyrgyzstan', 'KH' => 'Cambodia', 'KI' => 'Kiribati', 'KM' => 'Comoros', 'KN' => 'Saint Kitts and Nevis', 'KP' => 'North Korea', 'KR' => 'South Korea', 'XK' => 'Kosovo', 'KW' => 'Kuwait', 'KY' => 'Cayman Islands', 'KZ' => 'Kazakhstan', 'LA' => 'Lao Peoples Democratic Republic', 'LB' => 'Lebanon', 'LC' => 'Saint Lucia', 'LI' => 'Liechtenstein', 'LK' => 'Sri Lanka', 'LR' => 'Liberia', 'LS' => 'Lesotho', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'LV' => 'Latvia', 'LY' => 'Libya', 'MA' => 'Morocco', 'MC' => 'Monaco', 'MD' => 'Moldova', 'MG' => 'Madagascar', 'ME' => 'Montenegro', 'MF' => 'Saint Martin', 'MH' => 'Marshall Islands', 'MK' => 'Macedonia', 'ML' => 'Mali', 'MO' => 'Macau', 'MM' => 'Myanmar', 'MN' => 'Mongolia', 'MP' => 'Northern Mariana Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MS' => 'Montserrat', 'MT' => 'Malta', 'MU' => 'Mauritius', 'MV' => 'Maldives', 'MW' => 'Malawi', 'MX' => 'Mexico', 'MY' => 'Malaysia', 'MZ' => 'Mozambique', 'NA' => 'Namibia', 'NC' => 'New Caledonia', 'NE' => 'Niger', 'NF' => 'Norfolk Island', 'NG' => 'Nigeria', 'NI' => 'Nicaragua', 'NL' => 'Netherlands', 'NO' => 'Norway', 'NP' => 'Nepal', 'NR' => 'Nauru', 'NU' => 'Niue', 'NZ' => 'New Zealand', 'OM' => 'Oman', 'PA' => 'Panama', 'PE' => 'Peru', 'PF' => 'French Polynesia', 'PG' => 'Papua New Guinea', 'PH' => 'Philippines', 'PK' => 'Pakistan', 'PL' => 'Poland', 'PM' => 'Saint Pierre and Miquelon', 'PN' => 'Pitcairn Islands', 'PR' => 'Puerto Rico', 'PS' => 'Palestinian Territories', 'PT' => 'Portugal', 'PW' => 'Palau', 'PY' => 'Paraguay', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RS' => 'Serbia', 'RU' => 'Russia', 'RW' => 'Rwanda', 'SA' => 'Saudi Arabia', 'SB' => 'Solomon Islands', 'SC' => 'Seychelles', 'SD' => 'Sudan', 'SE' => 'Sweden', 'SG' => 'Singapore', 'SH' => 'Saint Helena', 'SI' => 'Slovenia', 'SJ' => 'Svalbard and Jan Mayen', 'SK' => 'Slovakia', 'SL' => 'Sierra Leone', 'SM' => 'San Marino', 'SN' => 'Senegal', 'SO' => 'Somalia', 'SR' => 'Suriname', 'SS' => 'South Sudan', 'ST' => 'Sao Tome and Principe', 'SV' => 'El Salvador', 'SX' => 'Sint Maarten', 'SY' => 'Syria', 'SZ' => 'Swaziland', 'TC' => 'Turks and Caicos Islands', 'TD' => 'Chad', 'TF' => 'French Southern and Antarctic Lands', 'TG' => 'Togo', 'TH' => 'Thailand', 'TJ' => 'Tajikistan', 'TK' => 'Tokelau', 'TL' => 'Timor-Leste', 'TM' => 'Turkmenistan', 'TN' => 'Tunisia', 'TO' => 'Tonga', 'TR' => 'Turkey', 'TT' => 'Trinidad and Tobago', 'TV' => 'Tuvalu', 'TW' => 'Taiwan', 'TZ' => 'Tanzania', 'UA' => 'Ukraine', 'UG' => 'Uganda', 'UM-DQ' => 'Jarvis Island', 'UM-FQ' =>  'Baker Island', 'UM-HQ' => 'Howland Island', 'UM-JQ' => 'Johnston Atoll', 'UM-MQ' => 'Midway Islands', 'UM-WQ' => 'Wake Island', 'US' => 'United States', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VA' => 'Vatican City', 'VC' => 'Saint Vincent and the Grenadines', 'VE' => 'Venezuela', 'VG' => 'British Virgin Islands', 'VI' => 'US Virgin Islands', 'VN' => 'Vietnam', 'VU' => 'Vanuatu', 'WF' => 'Wallis and Futuna', 'WS' => 'Samoa', 'YE' => 'Yemen', 'YT' => 'Mayotte', 'ZA' => 'South Africa', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');
			array_multisort( $countries, SORT_ASC );
			return $countries;
		}
	
	}
	
}