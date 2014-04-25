<?php
/* CNN VAN Dashboard Wordpress Plugin
	Copyright (C) 2014 thePlatform for Media Inc.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License along
	with this program; if not, write to the Free Software Foundation, Inc.,
	51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA. */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class VAN_Options {

	private $preferences_options_key = 'van_preferences_options';

	private $preferences;

	/*
		 * Fired during plugins_loaded
		 */

	function __construct() {

		$van_admin_cap = apply_filters( 'van_admin_cap', 'manage_options' );
		if ( !current_user_can( $van_admin_cap ) ) {
			wp_die( '<p>' . __( 'You do not have sufficient permissions to manage this plugin' ) . '</p>' );
		}

		$this->load_options();
		$this->register_preferences_options();

		//Render the page
		$this->plugin_options_page();
	}

	/**
	 * Loads the plugin options from the database into their respective arrays.
	 * Uses array_merge to merge with default values if they're missing.
	 */
	function load_options() {
		// Get existing options, or empty arrays if no options exist
		$this->preferences_options = get_option( $this->preferences_options_key, array() );

		// Initialize option defaults
		$this->preferences_options = array_merge( array(
				'van_feed_pid' => 'van-',
				'van_affiliate_id' => '',
				'van_width' => $GLOBALS['content_width'],
				'van_autoStart' => false
			), $this->preferences_options );

		// Create options table entries in DB if none exist. Initialize with defaults
		update_option( $this->preferences_options_key, $this->preferences_options );

		//Get preferences from the database for sanity checks
		$this->preferences = get_option( $this->preferences_options_key );
	}

	/*
		 * Registers the preference options via the Settings API,
		 * appends the setting to the tabs array of the object.
		 */

	function register_preferences_options() {
		add_settings_section( 'section_vanaffiliate_options', 'CNN VAN Affiliate Options', array( &$this, 'section_van_affiliate_desc' ), $this->preferences_options_key );
		add_settings_field( 'van_feed_pid_option', 'CNN VAN Feed PID', array( &$this, 'field_preference_option' ), $this->preferences_options_key, 'section_vanaffiliate_options', array( 'field' => 'van_feed_pid' ) );
		add_settings_field( 'van_affiliate_id_option', 'CNN VAN Affiliate ID', array( &$this, 'field_preference_option' ), $this->preferences_options_key, 'section_vanaffiliate_options', array( 'field' => 'van_affiliate_id' ) );
		add_settings_field( 'van_width_option', 'Default Video Width', array( &$this, 'field_preference_option' ), $this->preferences_options_key, 'section_vanaffiliate_options', array( 'field' => 'van_width' ) );
		add_settings_field( 'van_autoStart', 'Auto Start Videos', array( &$this, 'field_preference_option' ), $this->preferences_options_key, 'section_vanaffiliate_options', array( 'field' => 'van_autoStart' ) );
	}

	/*
		 * The following methods provide descriptions
		 * for their respective sections, used as callbacks
		 * with add_settings_section
		 */

	function section_van_affiliate_desc() {
		echo 'Configure general plugin preferences below.';
	}

	/*
		 * Option fields callback.
		 */

	function field_preference_option( $args ) {
		$opts = get_option( $this->preferences_options_key, array() );
		$field = $args['field'];
		if ( $field == 'van_autoStart' ) {
			$html = '<select id="' . esc_attr( $field ) . '" name="van_preferences_options[' . esc_attr( $field ) . ']">';
			$html .= '<option value="true"' . selected( $this->preferences_options[$field], 'true', false ) . '>True</option>';
			$html .= '<option value="false"' . selected( $this->preferences_options[$field], 'false', false ) . '>False</option>';
			$html .= '</select>';
		} else {
			$html = '<input type="text" id="' . esc_attr( $field ) . '" name="van_preferences_options[' . esc_attr( $field ) . ']" value="' . esc_attr( $opts[$field] ) . '" />';
		}
		echo $html;
	}

	/*
		 * Plugin Options page rendering goes here, checks
		 * for active tab and replaces key with the related
		 * settings key. Uses the plugin_options_tabs method
		 * to render the tabs.
		 */

	function plugin_options_page() {
?>
				<div class="wrap">
						<form method="POST" action="options.php">
				<?php settings_fields( $this->preferences_options_key ); ?>
				<?php do_settings_sections( $this->preferences_options_key ); ?>
				<?php submit_button(); ?>
						</form>
				</div>
				<?php
	}
}

new VAN_Options;
