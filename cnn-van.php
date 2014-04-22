<?php
/*
Plugin Name: CNN VAN Dashboard
Plugin URI: http://www.theplatform.com/
Description: Embed videos from the CNN VAN Network
Version: 1.0.0
Author: thePlatform for Media, Inc.
Author URI: http://theplatform.com/
License: GPL2

Copyright 2014 thePlatform for Media, Inc.

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

if ( ! defined( 'ABSPATH' ) ) exit;

define('FEED_URL', apply_filters('van_feed_url', 'http://feed.theplatform.com/f/van/'));

/**
 * Main class
 * @package default
 */
class van_Plugin {
	private $preferences;
	private $preferences_options_key = 'van_preferences_options';

	/**
	 * Initialize plugin
	 */
	function &init() {
		static $instance = false;

		if ( !$instance ) {
			$instance = new van_Plugin;
		}

		return $instance;
	}

	/**
	 * Constructor
	 */
	function __construct() {	
		if (is_admin()) {							
			add_action('admin_menu', array($this, 'add_admin_page'));
			add_action('admin_init', array($this, 'register_scripts_and_styles'));
			add_action('admin_init', array($this, 'register_plugin_settings'));
			add_action('wp_ajax_van_embed', array($this, 'embed'));
			add_action('wp_ajax_get_van_categories', array($this, 'van_categories'));
			add_action('wp_ajax_get_van_feed', array($this, 'van_feed'));	
			add_action('wp_ajax_set_thumbnail', array($this, 'set_thumbnail_ajax'));	
		}	
		add_shortcode('van', array($this, 'shortcode'));
	}	

	/**
	 * Registers initial plugin settings during initalization
	 * @return void
	 */
	function register_plugin_settings() {
		$preferences_options_key = 'van_preferences_options';
		register_setting( $preferences_options_key, $preferences_options_key, array($this, 'verify_settings')); 
	}

	/**
	 * Verify our preferences options
	 * @param  [array()] $input [Plugin settings]
	 * @return [array()]        [Clean plugin settings]
	 */
	function verify_settings($input) {
		if ( ! is_array( $input ) )  {
			return array(
				'van_feed_pid' => 'van-',
				'van_video_section' => '',
				'van_width' => $GLOBALS['content_width'],
				'van_height' => ($GLOBALS['content_width']/16)*9
			);
		}

		foreach ($input as $key => $value) {
			if ($key === 'van_width' || $key === 'van_height') 
				$input[$key] = intval($value); 		
			else	
				$input[$key] = trim(strval($value));
		}
		return $input;
	}

	/**
	 * Registers javascripts and css
	 */
	function register_scripts_and_styles() {		
		wp_register_script('pdk_external_controller', "http://pdk.theplatform.com/pdk/tpPdkController.js");
		wp_register_script('van_holder', plugins_url('/js/holder.js', __FILE__));
		wp_register_script('van_moment', plugins_url('/js/moment.min.js', __FILE__));
		wp_register_script('van_bootstrap_js', plugins_url('/js/bootstrap.min.js', __FILE__), array('jquery'));		
		wp_register_script('van_infiniscroll_js', plugins_url('/js/jquery.infinitescroll.min.js', __FILE__), array('jquery'));
		wp_register_script('van_Helper_js', plugins_url('/js/vanHelper.js', __FILE__), array('jquery'));		
		wp_register_script('van_mediaview_js', plugins_url('/js/mediaview.js', __FILE__), array('jquery', 'van_holder', 'van_moment', 'van_Helper_js', 'pdk_external_controller', 'van_infiniscroll_js', 'van_bootstrap_js'));


		if (!$this->preferences)
			$this->preferences = get_option($this->preferences_options_key);		

		wp_localize_script('van_Helper_js', 'van_wp_data', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),			
			'tp_nonce' => wp_create_nonce('van-ajax-nonce'),
			'feed_pid' => $this->preferences['van_feed_pid']		
		));	

		wp_register_style('bootstrap_van_css', plugins_url('/css/bootstrap_van.min.css', __FILE__ ));
		wp_register_style('van_media_browser_css', plugins_url('/css/van-media-browser.css', __FILE__ ));
	}

	/**
	 * Add admin pages 
	 */
	function add_admin_page() {		
		$van_admin_cap = apply_filters('van_admin_cap', 'manage_options');			
		add_plugins_page('CNN VAN Settings', 'CNN VAN Settings', $van_admin_cap, 'van-settings', array($this, 'admin_page' ));
	}

	/**
	 * Calls the plugin's options page template
	 * @return type
	 */
	function admin_page() {		
		require_once(dirname(__FILE__) . '/cnn-van-options.php' );	
	}

	/**
	 * Calls the Embed template in an IFrame and Dialog
	 * @return void
	 */
	function embed() {		
		require_once( $this->plugin_dir . 'cnn-van-media-browser.php' );
		die();
	}

	/**
	 * Calls thePlatform's feed to retrieve a list of categories
	 * @return [json] [JSON encoded list of categories]
	 */
	function van_categories() {	
		check_admin_referer('van-ajax-nonce');	
		if (!$this->preferences)
			$this->preferences = get_option($this->preferences_options_key);		

		$url = FEED_URL . $this->preferences['van_feed_pid'] . $_POST['url'];
		
		$response = wp_remote_get($url);
		echo wp_remote_retrieve_body($response);
		die();
	}

	/**
	 * Calls thePlatform's feed to retrieve a list of media
	 * @return [json] [JSON encoded list of media]
	 */
	function van_feed() {	
		check_admin_referer('van-ajax-nonce');			
		if (!$this->preferences)
			$this->preferences = get_option($this->preferences_options_key);

		$url = FEED_URL . $this->preferences['van_feed_pid'] . $_POST['url'];
		$response = wp_remote_get($url);
		echo wp_remote_retrieve_body($response, array('timeout' => 50000));
		die();
	}

	/**
	 * AJAX callback to set a post thumbnail
	 */
	function set_thumbnail_ajax() {
		check_admin_referer('van-ajax-nonce');

		global $post_ID;			
		
		if (!isset($_POST['id'])) 
			die("Post ID not found");

		$post_ID = $_POST['id'];

		$url = isset($_POST['img']) ? esc_attr( $_POST['img'] ) : '';

		$thumbnail_id = $this->set_thumbnail( $url, $post_ID );   

		if ($thumbnail_id !== FALSE) {
			set_post_thumbnail($post_ID, $thumbnail_id);
			die( _wp_post_thumbnail_html( $thumbnail_id ) );
		}

		//TODO: Better error
		die("Something went wrong");

	}

	/**
	 * Sets a post thumbnail based on the current video default thumbnail
	 * @param String $url     URL pointing to the thumbnail
	 * @param int $post_id Wordpress Post ID
	 */
	function set_thumbnail($url, $post_id) {
		$file = download_url( $url );

		preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);
		$file_array['name'] = basename($matches[0]);
		$file_array['tmp_name'] = $file;

		if ( is_wp_error( $file ) ) {
			@unlink($file_array['tmp_name']);
			return false;
		}

		$thumbnail_id = media_handle_sideload( $file_array, $post_id);

		if ( is_wp_error($thumbnail_id) ) {
			@unlink($file_array['tmp_name']);
			return false;
		}

		return $thumbnail_id;
	}

	/**
	 * Shortcode Callback
	 * @param array $atts Shortcode attributes
	 */
	function shortcode( $atts ) {
		if (!$this->preferences)
			$this->preferences = get_option($this->preferences_options_key);

		extract(shortcode_atts(array(
			'width' => '',
			'id' => '',			
			'autostart' => ''
			), $atts
		));

		if ( empty($width) )
			$width = $this->preferences['van_width'];

		if ( empty($autostart) )
			$autostart = $this->preferences['van_autoStart'];

		if ( empty( $id ) )
			return '<!--Syntax Error: Required \'id\' parameter missing. -->';

	
		$output = $this->get_embed_shortcode($id, $width, $autostart, is_feed());
		$output = apply_filters('van_embed_code', $output);							

		return $output;
	}

	/**
	 * Called by the plugin shortcode callback function to construct a media embed iframe.
	 * 
	 * @param string $id Identifier of the video to embed
	 * @param string $player_width The width of the embedded player
	 * @param boolean $autoStart whether or not should the player auto start playback
	 * @param boolean $isFeed Whether or not we are rendering a feed
	 * @return string An iframe tag sourced from the selected media embed URL
	*/ 
	function get_embed_shortcode($id, $player_width, $autoStart, $isFeed) {
		if (!$this->preferences)
			$this->preferences = get_option($this->preferences_options_key);

		$timestamp = esc_attr(rand());
		$affiliate_id = esc_attr($this->preferences['van_affiliate_id']);
		$width = esc_attr($player_width);
		$video_id = esc_attr($id);
		$autoStart = esc_attr($autoStart);

		if ($isFeed) {
			$url = "http://van.cnn.com/embed/?videoid=" . $video_id . "&affiliate=" . $affiliate_id . "&size=" . $width . "&autostart=" . $autoStart . "&container=cnnvan-" . $timestamp;
			$html = '<a href="' . esc_url_raw($url) . '">Watch Video</a>';
		}
		else {
			$html = "<script type='text/javascript' id='cnnvan-widgetsinglecvp-js' src='http://i.cdn.turner.com/cnn/van/resources/scripts/van-widget-single-cvp.js?container=cnnvan-" . $timestamp . "'></script>"; 
			$html .= "<div id='cnnvan-" . $timestamp . "' data-affiliate='" . $affiliate_id . "' data-videoid='" . $video_id . "' data-size='" . $width . "' data-autostart='" . $autoStart . "'></div>";	
		}
		return $html;
	}
}

// Instantiate CNN VAN plugin on WordPress init
add_action('init', array( 'van_Plugin', 'init' ) );


// Init the Tinymce plugin
add_action('init', 'van_buttonhooks');	

function van_buttonhooks() {
	$van_embedder_cap = apply_filters('van_embedder_cap', 'edit_posts');		
	if (current_user_can($van_embedder_cap)) {
		add_filter("mce_external_plugins", "van_register_tinymce_javascript");
		add_filter('mce_buttons', 'van_register_buttons');
	}
}
 
function van_register_buttons($buttons) {
   array_push($buttons, "|", "van");
   return $buttons;
}
 
// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function van_register_tinymce_javascript($plugin_array) {
   $plugin_array['van'] = plugins_url('/js/cnn-van.tinymce.plugin.js', __file__);
   return $plugin_array;
}



