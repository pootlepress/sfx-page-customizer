<?php
/**
 * Plugin Name: Storefront Page Customizer
 * Plugin URI: http://woothemes.com/products/sfx-page-customizer/
 * Description:	Adds options for individual pages, posts and products underneath the WordPress editor. Change background image and color, header background image and color, hide titles, menus, breadcrumbs, layouts and footer.
 * Version: 0.9-beta.1
 * Author: PootlePress
 * Author URI: http://pootlepress.com/
 * Requires at least: 4.0.0
 * Tested up to: 4.1.1
 *
 * Text Domain: sfx-page-customizer
 * Domain Path: /languages/
 *
 * @package SFX_Page_Customizer
 * @category Core
 * @author PootlePress
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }// Exit if accessed directly

// Sold On Woo - Start
/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'FILE_ID', 'PRODUCT_ID' );
// Sold On Woo - End

//Parent class
require_once( dirname( __FILE__ ) . '/includes/sfxpc-variables-functions.php' );

//Parent class
require_once( dirname( __FILE__ ) . '/includes/class-sfxpc-abstract.php' );

//Admin class
require_once( dirname( __FILE__ ) . '/includes/class-sfxpc-admin.php' );

//Admin controls renderer
require_once( dirname( __FILE__ ) . '/includes/class-sfxpc-settings-output.php' );

//Admin controls renderer
require_once( dirname( __FILE__ ) . '/includes/class-sfxpc-render-admin-controls.php' );

//PootlePress Updator
require_once( dirname( __FILE__ ) . '/includes/class-pootlepress-updater.php' );

/**
 * Instantiates Pootlepress_Updater
 */
function sfxpc_pp_updater( ) {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$data = get_plugin_data( __FILE__ );
	$sfxpc_plugin_current_version = $data['Version'];
	$sfxpc_plugin_remote_path = 'http://www.pootlepress.com/?updater=1';
	$sfxpc_plugin_slug = plugin_basename( __FILE__ );
	new Pootlepress_Updater( $sfxpc_plugin_current_version, $sfxpc_plugin_remote_path, $sfxpc_plugin_slug );
}
add_action( 'init', 'sfxpc_pp_updater' );

/**
 * Returns the main instance of SFX_Page_Customizer to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object SFX_Page_Customizer
 */
function sfx_page_customizer( ) {
	return SFX_Page_Customizer::instance( );
} // End SFX_Page_Customizer( )

sfx_page_customizer();

/**
 * Main SFX_Page_Customizer Class
 *
 * @class SFX_Page_Customizer
 * @version	1.0.0
 * @since 1.0.0
 * @package	SFX_Page_Customizer
 * @author PootlePress
 */
final class SFX_Page_Customizer {
	/**
	 * SFX_Page_Customizer The single instance of SFX_Page_Customizer.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * The plugin directory URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $plugin_url;

	/**
	 * The plugin directory path.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $plugin_path;

	/**
	 * The settings object.
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings;

	/**
	 * The admin class object.
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $admin;

	/**
	 * All the post metas to populate.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $post_meta = array();

	/**
	 * Array of classes to be put in body
	 * @var array 
	 */
	public $body_classes = array();
	
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct( ) {
		$this->token 			= 'sfx-page-customizer';
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->version 			= '1.0.0';

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'sfxpc_load_plugin_textdomain' ) );

		add_action( 'init', array( $this, 'sfxpc_setup' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'sfxpc_plugin_links' ) );
	}

	/**
	 * Main SFX_Page_Customizer Instance
	 *
	 * Ensures only one instance of SFX_Page_Customizer is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see SFX_Page_Customizer( )
	 * @return Main SFX_Page_Customizer instance
	 */
	public static function instance( ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( );
		}
		return self::$_instance;
	} // End instance( )

	/**
	 * Load the localisation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_load_plugin_textdomain( ) {
		load_plugin_textdomain( 'sfx-page-customizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone( ) {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup( ) {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Plugin page links
	 *
	 * @since  1.0.0
	 */
	public function sfxpc_plugin_links( $links ) {
		$plugin_links = array( 
			'<a href="http://support.woothemes.com/">' . esc_html__( 'Support', 'sfx-page-customizer' ) . '</a>',
			'<a href="http://docs.woothemes.com/document/sfx-page-customizer/">' . esc_html__( 'Docs', 'sfx-page-customizer' ) . '</a>',
		 );

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Installation.
	 * Runs on activation. Logs the version number and assigns a notice message to a WordPress option.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install( ) {
		$this->_log_version_number( );

		$notices 		= get_option( 'sfxpc_activation_notice', array() );
		$notices[]		= '<p>' 
		  . esc_html__( 
			  'Thanks for installing Page Customizer extension for Storefront. You now have new options for individual pages, posts and products. You can find these options underneath the WordPress editor when you edit pages, posts and products.',
		  'sfx-page-customizer' )
		  . '</p>';

		update_option( 'sfxpc_activation_notice', $notices );
	}

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number( ) {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	}

	/**
	 * Setup all the things.
	 * Only executes if Storefront or a child theme using Storefront as a parent is active and the extension specific filter returns true.
	 * Child themes can disable this extension using the sfx_page_customizer_enabled filter
	 * @return void
	 */
	public function sfxpc_setup( ) {
		$theme = wp_get_theme( );
		if ( 'Storefront' == $theme->name || 'storefront' == $theme->template && apply_filters( 'sfx_page_customizer_supported', true ) ) {
			//Renderer
			$this->admin = new SFXPC_Admin( $this->token, $this->version, $this->plugin_url );
			//settings frontend
			$this->settings = new SFXPC_Settings_Output( $this->token, $this->version, $this->admin->supported_taxonomies );
			//Admin Hooks
			$this->sfxpc_admin_hooks( );
			//Hooks
			$this->sfxpc_hooks( );
		}
	}

	/**
	 * Adds hooks necessary for proper functioning
	 * 
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_hooks( ) {

		add_action( 'wp_enqueue_scripts', array( $this, 'sfxpc_styles' ), 999 );
		add_filter( 'body_class', array( $this, 'sfxpc_body_class' ) );
		
	}

	/**
	 * Adds hooks necessary for proper functioning
	 * 
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_admin_hooks( ) {

		add_action( 'admin_init', array( $this->admin, 'register_meta_box' ) );
		add_action( 'save_post', array( $this->admin, 'save_post' ) );
		add_action( 'admin_print_scripts', array( $this->admin, 'admin_scripts' ) );
		add_action( 'customize_preview_init', array( $this->admin, 'sfxpc_customize_preview_js' ) );
		add_filter( 'admin_body_class', array( $this->admin, 'sfxpc_admin_body_class' ) );
		add_action( 'admin_notices', array( $this->admin, 'sfxpc_customizer_notice' ) );
		// Hide the 'More' section in the customizer
		add_filter( 'storefront_customizer_more', '__return_false' );
		foreach ( $this->admin->supported_taxonomies as $tax ) {
			add_action( "{$tax}_edit_form", array( $this->admin, 'tax_custom_fields' ) );
		}
		add_action( 'edit_terms', array( $this->admin, 'save_term_fields' ) );

	}

	/**
	 * Enqueue CSS and custom styles.
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_styles( ) {
		wp_enqueue_style( 'sfxpc-styles', plugins_url( '/assets/css/style.css', __FILE__ ) );

		$css = $this->settings->styles_init( );

		wp_add_inline_style( 'sfxpc-styles', $css );
	}

	/**
	 * SFX Page Customizer Body Class
	 * Adds a class based on the extension name and any relevant settings.
	 */
	public function sfxpc_body_class( $classes ) {
		$this->body_classes[] = 'sfx-page-customizer-active';
		$this->body_classes = array_merge( $this->body_classes, $this->settings->body_classes );
		return array_merge( $classes, $this->body_classes );
	}
} // End Class
