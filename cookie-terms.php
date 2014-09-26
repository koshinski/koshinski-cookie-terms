<?php
/*
Plugin Name: Koshinski - Cookie Terms
Plugin URI:
Description: Dieses Wordpress Plugin blendet einen Hinweis über die Verwendung von Cookies auf der Webseite ein.
Version: 1.0
Author: koshinski
Author Email: kosh@koshinski.de
Author URI: http://www.koshinski.de/
License:

  Copyright 2014 koshinski (kosh@koshinski.de)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

if ( ! defined( 'ABSPATH' ) ) wp_die();

class CookieTerms {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'CookieTerms';
	const slug = 'cookieterms';
	const domain = 'cookie_terms';
	
	/**
	 * Constructor
	 */
	function __construct() {
		// menu hinzufügen
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		//register an activation hook for the plugin
		register_activation_hook( __FILE__, array( &$this, 'install_cookieterms' ) );

		// login und register seite rausnehmen, nur frontend
		global $pagenow;
		if( isset($pagenow) && !empty($pagenow) && !in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ) ) ){
			add_action( 'init', array( &$this, 'init_cookieterms' ) );
		}
	}
	
	/**
	 * Runs when the plugin is activated
	 */  
	function install_cookieterms() {
		// do not generate any output here
	}
  
  
	/**
	 * Runs when the plugin is initialized
	 */
	function init_cookieterms() {
		// Load JavaScript and stylesheets
		wp_enqueue_script( 'jquery-cookie', plugins_url( '/js/jquery.cookie.js', __FILE__ ), array('jquery'), '1.4.1' );
		$this->register_scripts_and_styles();
	}

	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {
		if ( !is_admin() ) {
			$this->load_file( self::slug . '-script', '/js/cookieterms.js', true );
			$this->load_file( self::slug . '-style', '/css/cookieterms.css' );
		}
	} // end register_scripts_and_styles
	
	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file( $name, $file_path, $is_script = false ) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {
				$display_text = get_option( self::slug . '_display_text', __('Durch die Benutzung unserer Webseite stimmen Sie der Verwendung von Cookies zu.', self::domain) );
				$more_url = get_option( self::slug . '_more_url', 0 );
				
				$display_more_url = false;
				if( $more_url != 0 ) $display_more_url = true;

				$more_url = get_permalink($more_url);
				
				wp_register_script( $name, $url, array('jquery') ); //depends on jquery
				wp_localize_script(
					$name,
					self::domain,
					array(
						'msg' => __( stripslashes($display_text) ),
						'label_button_ok' => __( 'Ok', self::domain ),
						'label_button_more' => ( ( $display_more_url ) ? __( 'Mehr erfahren', self::domain ) : 0 ),
						'more_url' => $more_url
					)
				);
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} // end if

	} // end load_file

	
	/**
	 * erstellt eine options page
	 */  
	function admin_menu(){
		add_options_page( 
			self::name . __(' - Options'), 
			self::name, 
			'manage_options', 
			self::slug, 
			array( $this, 'settings_page' )
		);
	}
  
	/**
	 * ausgabe der optionen
	 */  
	function settings_page(){
		if( isset( $_POST['submit'] ) ){
			if( isset( $_POST['display_text'] ) ){
				$display_text = $_POST['display_text'];
				update_option( self::slug . '_display_text' , $display_text);
			}
			if( isset( $_POST['more_url'] ) ){
				$more_url = esc_html( $_POST['more_url'] );
				update_option( self::slug . '_more_url' , $more_url);
			}
		}
		$display_text = get_option( self::slug . '_display_text', '' );
		$more_url = get_option( self::slug . '_more_url', 0 );
		?>
		<h3><?php echo self::name ?> - Options</h3>
		<form method="post" enctype="multipart/form-data" action="options-general.php?page=<?php echo self::slug ?>">
			<?php wp_nonce_field( self::slug . '_nonce_action', self::slug . '_nonce_field' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="display_text">Angezeigter Text</label></th>
					<td><textarea id="display_text" name="display_text" class="large-text code" cols="50" rows="4"><?php echo stripslashes($display_text); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="more_url">Mehr Erfahren - Link</label></th>
					<td>
						<select id="more_url" name="more_url" size="1">
							<option value="0" class="disabled">- keinen Link anzeigen -</option>
							<?php
							$alle_seiten = get_pages();
							foreach( $alle_seiten as $page ){
								?><option <?php if( isset($more_url) && $more_url == $page->ID ) echo 'selected="selected"'; ?> value="<?php echo $page->ID; ?>"><?php echo $page->post_title ?></option><?php
							}
							?>
						</select>
					</td>
				</tr>
			
			</table>
			<div class="clearfix">
				<button class="button button-primary" name="submit" type="submit"><?php _e( 'Save Changes' ); ?></button>
			</div>
		</form>
		
		<div><br/>
		<input type="text" name="test1" id="test1" class="regular-text koshinski-media-upload" value="">
		</div>

		<div><br/>
		<input type="text" name="test2" id="test2" class="regular-text koshinski-media-upload" value="">
		</div>
		
		<?php
	}
  
	
} // end class
new CookieTerms();

