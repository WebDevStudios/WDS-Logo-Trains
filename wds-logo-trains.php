<?php

/**
* Plugin Name: Logo Trains
* Plugin URI:  http://webdevstudios.com
* Description: Add logo train collections.
* Version:     1.0
* Author:      WebDevStudios
* Author URI:  http://webdevstudios.com
* Donate link: http://webdevstudios.com
* License:     GPLv2
* Text Domain: wds-logo-trains
* Domain Path: /languages
*/

/**
 * Copyright (c) 2015 WebDevStudios (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built with the help of generator-plugin-wp
 */

/**
 * Main initiation class
 */
class WDS_Logo_Trains {

	protected $version;
	protected $url      = '';
	protected $path     = '';
	protected $basename = '';
	protected static $single_instance = null;
	protected $post_type = 'wds_logo_trains';
	protected $text_domain;
	protected $meta_prefix = '_wds_logo_train_';

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 *
	 * @return WDS_Logo_Trains A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  1.0
	 */
	protected function __construct() {

		// Get the header values easily.
		$this->plugin_headers = $this->plugin_headers();
		$this->text_domain = $this->header('Text Domain');
		$this->version = $this->header('Version');

		// Set other important info.
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		// Start things up!
		$this->plugin_classes();
		$this->hooks();
		$this->includes();
	}

	/**
	 * Returns the meta prefix.
	 *
	 * @return string The meta prefix.
	 */
	function meta_prefix( $preprefix = false ) {
		return ( $preprefix ) ? $this->meta_prefix . $preprefix : $this->meta_prefix;
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since 1.0
	 */
	function plugin_classes() {
		// Attach other plugin classes to the base plugin class.
		// $this->admin = new WDLT_Admin( $this );
	}

	/**
	 * One of the headers of the plugin
	 *
	 * @param  string $header The header you would like.
	 *
	 * @return mixed          The header value, false if unset.
	 */
	function header( $header ) {
		return ( isset ( $this->plugin_headers[ $header ] ) ) ? $this->plugin_headers[ $header ] : false;
	}

	/**
	 * Returns the commented headers of the plugin
	 *
	 * @return array Headers.
	 */
	function plugin_headers() {
		return get_file_data( __FILE__, array(
			'Plugin Name' => 'Plugin Name',
			'Plugin URI' => 'Plugin URI',
			'Version' => 'Version',
			'Description' => 'Description',
			'Author' => 'Author',
			'Author URI' => 'Author URI',
			'Text Domain' => 'Text Domain',
			'Domain Path' => 'Domain Path',
		), 'plugin' );
	}

	/**
	 * Add hooks and filters
	 *
	 * @since 1.0
	 */
	public function hooks() {
		register_activation_hook( __FILE__, array( $this, '_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, '_deactivate' ) );

		// Init (because we always init things)
		add_action( 'init', array( $this, 'init' ) );

		// Create custom post types.
		add_action( 'init', array( $this, 'register_cpt' ) );

		// Widget
		add_action( 'widgets_init', array( $this, 'widget_init' ) );

		// Add Custom Meta Boxes
		add_action( 'cmb2_init', array( $this, 'logo_train_cmb2_init' ) );

		// Make our screen nice.
		add_filter( 'gettext', array( $this, 'screen_text' ), 20, 3 );
		add_action( 'admin_head-post.php', array( $this, 'hide_visibility_screen' ) );
		add_action( 'admin_head-post-new.php', array( $this, 'hide_visibility_screen' ) );
		add_filter( 'post_row_actions', array( $this, 'remove_quick_edit' ), 10, 2 );

		// Default styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// Admin Styles
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// JS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add the Widget.
	 *
	 * @return void
	 */
	function widget_init() {
		register_widget( 'WDS_Logo_Train' );
	}

	/**
	 * Get the details of a logo (attachment).
	 *
	 * @param  int $attachment_id   The ID of the attachment.
	 *
	 * @return array                Details for the attachment/logo.
	 */
	function get_logo_details( $attachment_id, $size = 'large' ) {

		// Get the desired attachment src for the size we want.
		$details['src'] = wp_get_attachment_image_src( $attachment_id, $size );

		$details['src'] = ( isset( $details['src'][0] ) ) ? $details['src'][0] : $details['src'];

		// Meta alt tag.
		$details['alt'] = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

		// We want to get the description (have to hack post_content for that).
		$attachment = get_post( $attachment_id );
		$details['url'] = $attachment->post_content;

		return $details;
	}

	/**
	 * When debugging, disabled cache.
	 *
	 * @return string Timestamp when debugging, actual version when not.
	 */
	protected function script_version() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return time();
		} else {
			return $this->version;
		}
	}

	/**
	 * Enqueue WP Admin only styles.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'admin-wds-logo-train', plugins_url( 'assets/css/admin/wds-logo-trains.css', __FILE__ ), array(), $this->script_version(), 'screen' );
	}

	/**
	 * Enqueue JS scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		// Slick logo train animations.
		wp_enqueue_script( 'slick-js', plugins_url( 'assets/slick/slick.min.js', __FILE__ ), array(
			'jquery',
		), $version, true );

	}

	/**
	 * Enqueue public facing styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'wds-logo-train', plugins_url( 'assets/css/public/wds-logo-trains.css', __FILE__ ), array(), $this->script_version(), 'screen' );

		// Slick logo train animations.
		wp_enqueue_style( 'slick-css', plugins_url( 'assets/slick/slick.css', __FILE__ ), array(), $version );
		wp_enqueue_style( 'slick-css-theme', plugins_url( 'assets/slick/slick-theme.css', __FILE__ ), array( 'slick-css' ), $version );
	}

	/**
	 * Removes the Quick Edit from the bulk list options.
	 *
	 * @param  array $actions Default Actions
	 *
	 * @return array          Actions with any inline actions removed.
	 */
	public function remove_quick_edit( $actions ) {

		global $current_screen;

		// Only on this CPT
		if( $current_screen->post_type != $this->post_type ) {
			return $actions;
		}

		// Remove any inline actions (Quick Edit).
		unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}

	/**
	 * Returns the post type set.
	 *
	 * @return string The post type setup for this plugin.
	 */
	public function post_type() {
		return $this->post_type;
	}

	/**
	 * Hides the publishing options since they aren't relevant here.
	 *
	 * @return void
	 */
	public function hide_visibility_screen() {
			if( $this->post_type == get_post_type() ){
				echo '
					<!-- Hides the publishing options -->
					<style type="text/css">
						#misc-publishing-actions,
						#minor-publishing-actions {
							display:none;
						}
					</style>
				';
			}
	}

	/**
	 * Changes default text to text that makes more sense for this plugin.
	 *
	 * @param  string $translated_text The un-translated text.
	 * @param  string $text            The original translated text.
	 * @param  string $domain          The text domain.
	 *
	 * @return string                  Modified text.
	 */
	public function screen_text( $translated_text, $text, $domain ) {
		if ( $this->post_type == get_post_type() ) {
			switch ( $translated_text ) {
				case 'Publish' :
					$translated_text = __( 'Save', $domain );
					break;
				case 'Published' :
					$translated_text = __( 'Saved', $domain );
					break;

				// Here we guise the description as the URL parameter.
				case 'Description' :
					$translated_text = __( 'URL', $domain );
					break;
			}
		}
		return $translated_text;
	}

	/**
	 * Registeres Logo Train CPT.
	 *
	 * @return void
	 */
	public function register_cpt() {

		$labels = array(
			'name'               => _x( 'Logo Trains', 'post type general name', 'mcf' ),
			'singular_name'      => _x( 'Logo Train', 'post type singular name', 'mcf' ),
			'menu_name'          => _x( 'Logo Trains', 'admin menu', 'mcf' ),
			'name_admin_bar'     => _x( 'Logo Train', 'add new on admin bar', 'mcf' ),
			'add_new'            => _x( 'Add New', 'book', 'mcf' ),
			'add_new_item'       => __( 'Add New Logo Train', 'mcf' ),
			'new_item'           => __( 'New Logo Train', 'mcf' ),
			'edit_item'          => __( 'Edit Logo Train', 'mcf' ),
			'view_item'          => __( 'View Logo Train', 'mcf' ),
			'all_items'          => __( 'All Logo Trains', 'mcf' ),
			'search_items'       => __( 'Search Logo Trains', 'mcf' ),
			'parent_item_colon'  => __( 'Parent Logo Trains:', 'mcf' ),
			'not_found'          => __( 'No Logo Train found.', 'mcf' ),
			'not_found_in_trash' => __( 'No Logo Train found in Trash.', 'mcf' )
		);

		$args = array(
			'labels'    => $labels,
			'public'    => false,
			'show_ui'   => true,
			'supports'  => array( 'title' ),
			'rewrite'   => false,
			'menu_icon' => 'dashicons-images-alt2',
		);

		register_post_type( $this->post_type, $args );

	}

	/**
	 * Add a way to add multiple images (or logos) to CPT.
	 *
	 * @return void
	 */
	public function logo_train_cmb2_init() {

		$box = new_cmb2_box( array(
			'id'            => $this->meta_prefix( 'metabox' ),
			'title'         => __( 'Logo Train', 'mcf' ),
			'object_types'  => array( $this->post_type, ), // Post type
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true,
		) );

		$box->add_field( array(
			'name'       => __( 'Logo Order', 'mcf' ),
			'id'         => $this->meta_prefix( 'logos' ),
			'type'       => 'file_list',
			'desc'       => __( 'Add or Order Logos below. Select logo to edit it, other attributes, and URL.', 'cmb2' ),
			'preview_size' => array( 50, 50 ), // Note we force height using admin-styles.scss
		) );

		// TODO: Add instructions for using template tag and shortcode.
	}

	/**
	 * Adds inline style for background image on the fly.
	 *
	 * @param  string  $src    The URL of the image.
	 * @param  boolean $return Whether to return or echo the style.
	 *
	 * @return string          The inline CSS.
	 */
	public function logo_background_inline_style( $src, $return = false ) {
		if ( $return ) {
			return "background-image: url($src); ";
		} else {
			echo "background-image: url($src); ";
		}
	}

	/**
	 * Activate the plugin
	 *
	 * @since  1.0
	 */
	function _activate() {
		// Make sure any rewrite functionality has been loaded
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  1.0
	 */
	function _deactivate() {
		// Nothing to do.
	}

	/**
	 * Init hooks
	 *
	 * @since  1.0
	 *
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'wds-logo-trains', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  1.0
	 *
	 * @return boolean
	 */
	public static function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('')

		// We have met all requirements
		return true;
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 * @since  1.0
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {
			// Display our error
			echo '<div id="message" class="error">';
				echo '<p>' . sprintf( __( 'Logo Trains is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'wds-logo-trains' ), admin_url( 'plugins.php' ) ) . '</p>';
			echo '</div>';
			// Deactivate our plugin
			deactivate_plugins( $this->basename );

			return false;
		}

		return true;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  1.0
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return $this->version;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 * @since  1.0
	 * @param  string $filename Name of the file to be included
	 */
	public static function includes( $filename = false ) {
		if ( $filename ) {
			$file = self::dir( './includes/'. $filename .'.php' );
			if ( file_exists( $file ) ) {
				return include_once( $file );
			}
		}

		foreach ( new DirectoryIterator( trailingslashit( dirname( __FILE__ ) ) . 'includes' ) as $fileInfo ) {
			if( ! $fileInfo->isDot() ) {
				require_once trailingslashit( $fileInfo->getPath() ) . $fileInfo->getFilename();
			}
		}
	}
}

/**
 * Grab the WDS_Logo_Trains object and return it.
 *
 * Template Tag Wrapper for WDS_Logo_Trains::get_instance()
 *
 * @return object Plugin instance.
 */
function wds_logo_trains() {
	return WDS_Logo_Trains::get_instance();
}

// Bootup!
wds_logo_trains();