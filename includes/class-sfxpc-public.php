<?php
/**
 * Contains class admin class
 *
 * @package SFX_Page_Customizer
 * @category Admin
 */

/**
 * Makes the SFX Page Customizer settings work
 *
 * @class SFXPC_Public
 * @version	1.0.0
 * @since 1.0.0
 * @package	SFX_Page_Customizer
 * @author PootlePress
 */
class SFXPC_Public extends SFXPC_Abstract {

	/**
	 * The settings object.
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings_output;

	/**
	 * The current page settings array.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings;

	/**
	 * The post types we support.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $supported_post_types = array();

	/**
	 * The taxonomies we support.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $supported_taxonomies = array();

	/**
	 * Called by Parent::__consruct
	 * Initiates class variables
	 * 
	 * @access  public
	 * @since   1.0.0
	 */
	public function init( $args ) {

		//Basic Setup
		$this->plugin_url           = $args[2];
		//Supported Post Types and Taxonomies
		$this->supported_post_types = $args[3];
		$this->supported_taxonomies = $args[4];

		//Settings frontend
		$this->settings_output	= new SFXPC_Settings_Output( $this->token, $this->version, $this->supported_taxonomies );

	}

	public function get_post_settings() {

		$is_shop = false;

		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$is_shop = true;
		}

		global $post;

		//Meta values for the page
		if ( $is_shop ) {
			$current_post = get_option( 'woocommerce_shop_page_id' );
		} elseif ( is_home() ) {
			$current_post = get_option( 'page_for_posts' );
		} else {
			$current_post = $post->ID;
		}

		return get_post_meta( $current_post , $this->token , true );

	}

	/**
	 * Gets Taxonomy Settings
	 * 
	 * @TODO Get rid of it
	 * @return void|null
	 */
	public function get_tax_settings() {

		//Get term object
		$term = get_queried_object();
		//Get the setting name
		$setting_name = $this->token. '-cat' . $term->term_id;
		//Return the settings (option)
		return get_option( $setting_name );

	}

	/**
	 * Initiates styles for options
	 * 
	 * @access  public
	 * @since   1.0.0
	 * @return null|string Styles
	 */
	public function settings_styles_init() {

		//Check if it is a supported taxonomy term archive
		if ( is_tax( $this->supported_taxonomies ) || is_tag() || is_category() ) {
			$settings = $this->get_tax_settings();
		} else {
			//If Its a post $settings woulb be trueish
			$settings = $this->get_post_settings();
		}
		return $settings ? $this->settings_output->css( $settings ) : false;

	}
	/**
	 * Enqueue CSS and custom styles.
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_styles( ) {
		wp_enqueue_style( 'sfxpc-styles', plugins_url( '/assets/css/style.css', __FILE__ ) );

		$css = $this->settings_styles_init( );

		wp_add_inline_style( 'sfxpc-styles', $css );
	}

	/**
	 * SFX Page Customizer Body Class
	 * Adds a class based on the extension name and any relevant settings.
	 */
	public function sfxpc_body_class( $classes ) {
		$this->body_classes[] = 'sfx-page-customizer-active';
		$this->body_classes = array_merge( $this->body_classes, $this->settings_output->body_classes );
		return array_merge( $classes, $this->body_classes );
	}

}