<?php
/*
Plugin Name: Uploads Logger
Plugin URI: https://github.com/vlood/uploads-logger
Description: Log user-uploaded files for future reference by a shortcode.

The plugin logs info on when a user uploaded a file. Info is saved as
usermeta entry, containing file-name and timestamp of the event.
The entry is actually an array of all upload log entries of the user.
Using the shortcode [my-uploads-log], you will get a list of current user's
uploads that have been logged.

Version: 0.1
Author: vloo
Author Email: vlood.vassilev@gmail.com
License: GNU GPL v.2

  Copyright 2015 Vladimir Vassilev (vlood.vassilev@gmail.com)

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

class UploadsLogger {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'Uploads Logger';
	const slug = 'uploads_logger';
	const usermeta_key = 'uploads_history';
	
	/**
	 * Constructor
	 */
	function __construct() {
		//register an activation hook for the plugin
		register_activation_hook( __FILE__, array( &$this, 'install_uploads_logger' ) );

		//Hook up to the init action
		add_action( 'init', array( &$this, 'init_uploads_logger' ) );
	}
  
	/**
	 * Runs when the plugin is activated
	 */  
	function install_uploads_logger() {
		// do not generate any output here
	}
  
	/**
	 * Runs when the plugin is initialized
	 */
	function init_uploads_logger() {
		// Setup localization
		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

		// Register the shortcode [my-uploads-log]
		add_shortcode( 'my-uploads-log', array( &$this, 'render_shortcode' ) );

		add_action( 'add_attachment', array( &$this, 'add_new_metauser_entry' ) );
	}

	function action_callback_method_name() {
		// TODO define your action method here
	}

	function filter_callback_method_name() {
		// TODO define your filter method here
	}

	function render_shortcode( $atts ) {
		if ( !is_user_logged_in() ) {
			return __( 'You need to be logged in, to see your uploads history', self::slug );
		}
		
		$user_id = get_current_user_id();
		
		$users_upload_entries = get_user_meta( $user_id, self::slug, false );
		
		if(empty($users_upload_entries)){
			return __( 'You haven\'t uploaded anything lately!', self::slug );
		}
		
		$result = '<table>';
		
		foreach ( $users_upload_entries as $time_file_pairs ){
			if ( sizeof( $time_file_pairs ) == 2 ) { 
				$result .= '<tr><td>' . $time_file_pairs[0] . '</td><td>' . $time_file_pairs[1] . '</td></tr>';
			}
		}
		$result .= '</table>';
		
		return $result;
	}
	
	function add_new_metauser_entry( $attachment_id ) {
		
		if ( !is_user_logged_in() ) {
			return; //whaaaat?! Anonymous user uploading files?!
		}
		
		$user_id = get_current_user_id();
		
		$attachment_path = basename( get_attached_file( $attachment_id ) );
		
		date_default_timezone_set('EET');
		
		add_user_meta( $user_id, self::slug, array( date( 'Y-m-d H:i' ), $attachment_path ), false );
		
	}
  
} // end class
new UploadsLogger();

?>
