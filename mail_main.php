<?php
	/*
	Plugin Name: Shaf WPmail
	Description: A messaging system for wordpress bloggers
	Author: Shafaet Ashraf
	Version: 1.10
	Author URI: http://www.shafaetsplanet.com
	*/
	/*
	Copyright (C) Shafaet Ashraf 2014
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
	*/
	
	class ShafWPmailer {
	

		public function __construct()
		{
			add_action( 'admin_menu', array($this,'my_plugin_menu') );
			/* Create Database table */
			global $wpdb;
			$charset_collate = '';
			if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}
			if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
			}
			$table_name = $wpdb->prefix."shaf_WPmailer";
			
			$sql = "CREATE TABLE $table_name (
			id int(10) NOT NULL AUTO_INCREMENT,
			time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			body text NOT NULL,
			subject text NOT NULL,
			sender text NOT NULL,
			receiver text NULL,
			read_flag int(10) NULL,
			sent_mail_flag int(10) NULL,
			UNIQUE KEY id(id)
			) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			/* Table Creation Done */
		}
		
		public function my_plugin_menu() {
		//Add settings button in options page
			$text = 'WPmailer';
			add_options_page($text, "WPmailer", 'read', $text, array($this,'my_plugin_options') );
		}
		function my_plugin_options() {
			if ( !current_user_can( 'read' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			include('mail_admin.php');
		}
		function activate_shortCode()
		{
			add_shortcode( 'WPmailer_menu', array($this,'short_code_func') );	
		}
		function short_code_func()
		{
			return "<p>HEY MAILER!</p>";
		}


	}
	
	$mymailer=new ShafWPmailer();
	$mymailer->activate_shortCode();
?>
