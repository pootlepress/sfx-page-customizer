<?php
/**
 * Plugin Name: Storefront Page Customizer
 * Plugin URI: http://woothemes.com/products/sfx-page-customizer/
 * Description:	Adds options for individual pages, posts and products underneath the WordPress editor. Change background image and color, header background image and color, hide titles, menus, breadcrumbs, layouts and footer.
 * Version: 1.0.0
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

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
 
require_once(dirname(__FILE__) . '/includes/class-pootlepress-updater.php');

/**
 * Instantiates Pootlepress_Updater
 */
function sfxpc_pp_updater(){
	if (!function_exists('get_plugin_data')) {
		include(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	$data = get_plugin_data(__FILE__);
	$sfxpc_plugin_current_version = $data['Version'];
	$sfxpc_plugin_remote_path = 'http://www.pootlepress.com/?updater=1';
	$sfxpc_plugin_slug = plugin_basename(__FILE__);
	new Pootlepress_Updater ($sfxpc_plugin_current_version, $sfxpc_plugin_remote_path, $sfxpc_plugin_slug);
}
add_action('init', 'sfxpc_pp_updater');

/**
 * Returns the main instance of SFX_Page_Customizer to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object SFX_Page_Customizer
 */
function SFX_Page_Customizer() {
	return SFX_Page_Customizer::instance();
} // End SFX_Page_Customizer()

SFX_Page_Customizer();

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

	// Admin - Start
	/**
	 * The admin object.
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $admin;
 
 	/**
	 * The settings object.
	 * @var     object
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
	 * All the post metas to populate.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $post_meta = array();
	
	/**
	 * Controls not to show in taxonomies
	 * @var array
	 */
	public $not_in_tax = array();

	/**
	 * Array of classes to be put in body
	 * @var array 
	 */
	public $body_classes = array();


	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct() {
		$this->token 			= 'sfx-page-customizer';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
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
	 * @see SFX_Page_Customizer()
	 * @return Main SFX_Page_Customizer instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()

	/**
	 * Load the localisation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_load_plugin_textdomain() {
		load_plugin_textdomain( 'sfx-page-customizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Plugin page links
	 *
	 * @since  1.0.0
	 */
	public function sfxpc_plugin_links( $links ) {
		$plugin_links = array(
			'<a href="http://support.woothemes.com/">' . __( 'Support', 'sfx-page-customizer' ) . '</a>',
			'<a href="http://docs.woothemes.com/document/sfx-page-customizer/">' . __( 'Docs', 'sfx-page-customizer' ) . '</a>',
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
	public function install() {
		$this->_log_version_number();

		$notices 		= get_option( 'sfxpc_activation_notice', array() );
		$notices[]		= '<p>' 
		  . sprintf( __( 
			'Thanks for installing Page Customizer extension for Storefront. You now have new options for individual pages, posts and products. You can find these options underneath the WordPress editor when you edit pages, posts and products.', 
			'sfx-page-customizer' ) ) 
		  . '</p>';

		update_option( 'sfxpc_activation_notice', $notices );
	}

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	}

	/**
	 * Setup all the things.
	 * Only executes if Storefront or a child theme using Storefront as a parent is active and the extension specific filter returns true.
	 * Child themes can disable this extension using the sfx_page_customizer_enabled filter
	 * @return void
	 */
	public function sfxpc_setup() {
		$theme = wp_get_theme();

		if ( 'Storefront' == $theme->name || 'storefront' == $theme->template && apply_filters( 'sfx_page_customizer_supported', true ) ) {
			//Getting/Setting supported post types
			$this->get_supported_post_types();
			$this->get_supported_taxonomies();
			$this->get_meta_fields();

			add_action('admin_init', array($this, 'register_meta_box'));
			add_action('save_post', array($this, 'save_post'));

			add_action('admin_print_scripts', array($this, 'admin_scripts'));
			add_action( 'wp_enqueue_scripts', array( $this, 'sfxpc_styles' ), 999 );
			add_action( 'admin_print_scripts', array( $this, 'sfxpc_script' ), 999 );
			add_action( 'customize_register', array( $this, 'sfxpc_customize_register' ) );
			add_action( 'customize_preview_init', array( $this, 'sfxpc_customize_preview_js' ) );
			add_filter( 'body_class', array( $this, 'sfxpc_body_class' ) );
			add_filter( 'admin_body_class', array( $this, 'sfxpc_admin_body_class') );
			add_action( 'admin_notices', array( $this, 'sfxpc_customizer_notice' ) );
			//Unhooks the hidden stuff
			add_action( 'wp_head', array( $this, 'remove_hidden_stuff' ) );
			// Hide the 'More' section in the customizer
			add_filter( 'storefront_customizer_more', '__return_false' );
			foreach ($this->supported_taxonomies as $tax){
				//add_action( "{$tax}_add_form_fields", array( $this, 'tax_custom_fields'));
				add_action( "{$tax}_edit_form", array( $this, 'tax_custom_fields' ) );
				//add_action( 'create_terms', array( $this, 'save_term_fields' ) );
				add_action( 'edit_terms', array( $this, 'save_term_fields' ) );
			}
		}
	}

	/**
	 * Admin notice
	 * Checks the notice setup in install(). If it exists display it then delete the option so it's not displayed again.
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_customizer_notice() {
		$notices = get_option( 'sfxpc_activation_notice' );

		if ( $notices = get_option( 'sfxpc_activation_notice' ) ) {

			foreach ( $notices as $notice ) {
				echo '<div class="updated">' . $notice . '</div>';
			}

			delete_option( 'sfxpc_activation_notice' );
		}
	}

	/**
	 * Customizer Controls and settings
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function sfxpc_customize_register( $wp_customize ) {/*Placeholder for future*/}

	public function register_meta_box() {
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this post', array($this, 'custom_fields'), 'post' );
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this page', array($this, 'custom_fields'), 'page' );
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this product', array($this, 'custom_fields'), 'product' );
	}

	public function save_post($postID) {
		$post = get_post($postID);
		 
		//check if post type is post,page or product
		if (!in_array($post->post_type, $this->supported_post_types)) {
			return;
		}

		if (isset($_REQUEST[$this->token]) && is_array($_REQUEST[$this->token])) {
			$sfxPCValues = $_REQUEST[$this->token];

			//Automating the saving of our post metas
			$all_meta = $this->post_meta;
			foreach($all_meta as $meta){
				$meta_id = $this->get_meta_key($meta['section'], $meta['id']);
				switch($meta['type']){
					case 'image':
						$new_val = esc_url_raw($sfxPCValues[$meta['section']][$meta['id']]);
						break;
					default:
						$new_val = sanitize_text_field($sfxPCValues[$meta['section']][$meta['id']]);
				}
				update_post_meta($postID, $meta_id, $new_val);
			}
		}
	}

	public function save_term_fields($ID) {
		if (isset($_REQUEST[$this->token]) && is_array($_REQUEST[$this->token])) {
			$setting_name = $this->token.'-cat'.$ID;
			$sfxPCValues = $_REQUEST[$this->token];
			update_option($setting_name, $sfxPCValues);
		}
	}

	private function get_supported_post_types(){
		$this->supported_post_types = array(
		  'post',
		  'page',
		  'product'
		);
	}
	
	private function get_supported_taxonomies(){
		$this->supported_taxonomies = array(
		  'category',
		  'post_tag',
		  'product_cat',
		  'product_tag',
		);
	}

	private function get_meta_fields() {
	//	$header_text_color = storefront_sanitize_hex_color( get_theme_mod( 'storefront_header_text_color', apply_filters( 'storefront_default_header_text_color', '#5a6567' ) ) );
	//	$header_link_color = storefront_sanitize_hex_color( get_theme_mod( 'storefront_header_link_color', apply_filters( 'storefront_default_header_link_color', '#ffffff' ) ) );
	//	$header_background_color = storefront_sanitize_hex_color( get_theme_mod( 'storefront_header_background_color', apply_filters( 'storefront_default_header_background_color', '#2c2d33' ) ) );
	//	$background_color = '#'.get_background_color();

		$header_text_color = '';
		$header_link_color = '';
		$header_background_color = '';
		$background_color = '';

		$this->post_meta = array(
		  //Body Controls
			'background-image' => array(
				'id' => 'background-image',
				'section' => 'background',
				'label' => 'Page background image',
				'type' => 'image',
				'default' => '',
			),
			'background-repeat' => array(
				'id' => 'background-repeat',
				'section' => 'background',
				'label' => 'Background repeat',
				'type' => 'radio',
				'default' => 'repeat',
				'options' => array('no-repeat' => 'No Repeat','repeat' => 'Tile','repeat-x' => 'Tile Horizontally','repeat-y' => 'Tile Vertically',)
			),
			'background-position' => array(
				'id' => 'background-position',
				'section' => 'background',
				'label' => 'Background position',
				'type' => 'radio',
				'default' => 'center',
				'options' => array('left' => 'Left', 'center' => 'Center', 'right' => 'Right')
			),
			'background-attachment' => array(
				'id' => 'background-attachment',
				'section' => 'background',
				'label' => 'Background attachment',
				'type' => 'radio',
				'default' => 'scroll',
				'options' => array('fixed' => 'Fixed','scroll' => 'Scroll')
			),
		  'background-color' => array(
				'id' => 'background-color',
				'section' => 'background',
				'label' => 'Page background color',
				'type' => 'color',
				'default' => $background_color,
			),
		  //Header Options
			'hide-header' => array(
				'id' => 'hide-header',
				'section' => 'header',
				'label' => 'Hide header',
				'type' => 'checkbox',
				'default' => '',
			),
			'header-background-image' => array(
				'id' => 'header-background-image',
				'section' => 'header',
				'label' => 'Header background image',
				'type' => 'image',
				'default' => '',
			    'description' => 'Recommended header size is 1950 × 250 pixels.',
			),
			'header-background-color' => array(
				'id' => 'header-background-color',
				'section' => 'header',
				'label' => 'Header background color',
				'type' => 'color',
				'default' => $header_background_color,
			),
			'header-text-color' => array(
				'id' => 'header-text-color',
				'section' => 'header',
				'label' => 'Header text color',
				'type' => 'color',
				'default' => $header_text_color,
			),
			'header-link-color' => array(
				'id' => 'header-link-color',
				'section' => 'header',
				'label' => 'Header link color',
				'type' => 'color',
				'default' => $header_link_color,
			),
			'hide-shop-cart' => array(
				'id' => 'hide-shop-cart',
				'section' => 'header',
				'label' => 'Hide shopping cart in header',
				'type' => 'checkbox',
				'default' => '',
				'css-class' => 'wc-only',
			),
		  //Menu Options
			'hide-primary-menu' => array(
				'id' => 'hide-primary-menu',
				'section' => 'header',
				'label' => 'Hide primary menu',
				'type' => 'checkbox',
				'default' => '',
			),
			'hide-secondary-menu' => array(
				'id' => 'hide-secondary-menu',
				'section' => 'header',
				'label' => 'Hide secondary menu',
				'type' => 'checkbox',
				'default' => '',
			),
		  //Main Section
			'hide-breadcrumbs' => array(
				'id' => 'hide-breadcrumbs',
				'section' => 'header',
				'label' => 'Hide breadcrumbs',
				'type' => 'checkbox',
				'default' => '',
				'css-class' => 'wc-only',
			),
			'hide-title' => array(
				'id' => 'hide-title',
				'section' => 'header',
				'label' => 'Hide page title',
				'type' => 'checkbox',
				'default' => '',
			),
		  //Content Options
			'body-link-color' => array(
				'id' => 'body-link-color',
				'section' => 'content',
				'label' => 'Typography - link / accent color',
				'type' => 'color',
				'default' => '',
			),
			'body-text-color' => array(
				'id' => 'body-text-color',
				'section' => 'content',
				'label' => 'Typography - text color',
				'type' => 'color',
				'default' => '',
			),
			'body-head-color' => array(
				'id' => 'body-head-color',
				'section' => 'content',
				'label' => 'Typography - heading color',
				'type' => 'color',
				'default' => '',
			),
		  //Layout
			'layout' => array(
				'id' => 'layout',
				'section' => 'content',
				'label' => 'Layout',
				'type' => 'radio',
				'default' => 'right',
				'options' => array(
				  'left' => '<img src="' . get_template_directory_uri() . '/inc/customizer/controls/img/2cl.png">',
				  'right' => '<img src="' . get_template_directory_uri() . '/inc/customizer/controls/img/2cr.png">')
			),
		  //Footer
			'hide-footer' => array(
				'id' => 'hide-footer',
				'section' => 'footer',
				'label' => 'Hide footer',
				'type' => 'checkbox',
				'default' => '',
			),

		);
	}
	
	public function custom_fields() {
		$fields = $this->post_meta;
		$class = ' sfxpc-metabox sfxpc-tabs-wrapper ';
		echo "<div class='{$class}'>";

		$field_structure = array();
		foreach ($fields as $key => $field) {
			$field_structure[$field['section']][] = $field;
		}
		echo "<ul class='sfxpc-sections-nav nav-tab-wrapper'>";
		  foreach( $field_structure as $sec => $fields ){
			  $Sec = ucwords($sec);
			echo ""
			. "<li>"
			  . "<a href='#sfxpc-section-{$sec}'> $Sec </a>"
			. "</li>";
		  }
		echo "</ul>";
		foreach( $field_structure as $sec => $fields ){
			echo "<div class='sfxpc-section' id='sfxpc-section-{$sec}'>";
			foreach ($fields as $fld){
				$this->render_field($fld);
			}
			echo "</div>";
		}
		
		echo "</div>";
	}
	
	public function tax_custom_fields($term) {
		global $pagenow;
		$id = $term;
		$tax_sfxpc_data = null;
		
		if(isset($_REQUEST['action'])){
			$output_format = 'termEdit';
			$setting_name = $this->token. '-cat' . $term->term_id;
			$tax_sfxpc_data = get_option($setting_name);

		}else{
			$output_format = 'termAdd';
		}
		
		$fields = $this->post_meta;
		echo '<h2>'
		  . 'Customize Storefront options for this category archive'
		. '</h2>';
		echo '<table class="form-table">';
		foreach ($fields as $key => $field) {
			$this->render_field($field, $output_format, $tax_sfxpc_data);
		}
		echo '</table>';
	}
 
	/**
	 * Gets value of post meta
	 * @global type $post
	 * @param type $section
	 * @param type $id
	 * @param type $default
	 * @param type $post_id
	 * @return string
	 */
	protected function get_value($section, $id, $default = null, $post_id=false) {
		//Getting post id if not set
		if( !$post_id ){ global $post; $post_id = $post->ID; }

		$metaKey = $this->get_meta_key($section, $id);

		$ret = get_post_meta($post_id, $metaKey, true);
		if (isset($ret) && $ret != false) {
			return $ret;
		} else {
			return $default;
		}
	}

	private function get_meta_key($section, $id) {
		return '_'.$this->token . '-' . $section . '-' . $id;
	}

	private function get_field_key($section, $id) {
		return $this->token . '[' . $section . '][' . $id . ']';
	}
	
	/**
	 * Removes the hidden stuff via remove_action for storefront
	 * @global type $post
	 * @since   1.0.0
	 * @return void
	 */
	public function remove_hidden_stuff(){
		$is_shop=false;
		if(function_exists('is_shop')){
			if(is_shop()){
				$is_shop = true;
			}
		}
		
		// check if this is single post or page or product or shop
		if(!is_singular($this->supported_post_types) && !$is_shop && !is_home()) {
			return;
		}

		global $post;


		$showPagePostTitle = null;

		//Meta values for the page
		if($is_shop){
			$current_post = get_option( 'woocommerce_shop_page_id' );
		}elseif(is_home()){
			$current_post = get_option( 'page_for_posts' );
		}else{
			$current_post = false;
		}
		
		$hideHeader = $this->get_value('header', 'hide-header', false, $current_post);
		$hidePrimaryNav = $this->get_value('header', 'hide-primary-menu', null, $current_post);
		$hideSecondaryNav = $this->get_value('header', 'hide-secondary-menu', null, $current_post);
		$hideHeaderCart = $this->get_value('header', 'hide-shop-cart', null, $current_post);
		$hideBreadcrumbs = $this->get_value('header', 'hide-breadcrumbs', null, $current_post);
		$hideTitle = $this->get_value('header', 'hide-title', '', $current_post);
		$hideFooter = $this->get_value('footer', 'hide-footer', false, $current_post);

		if($hideHeader){
			remove_all_actions( 'storefront_header' );
		}
		if($hidePrimaryNav){
			remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
		}
		if($hideSecondaryNav){
			remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
		}
		if($hideHeaderCart){
			remove_action( 'storefront_header', 'storefront_header_cart', 		60 );
		}
		if($hideBreadcrumbs){
			remove_action( 'storefront_content_top', 'woocommerce_breadcrumb', 					10 );
			$this->body_classes[] = 'no-wc-breadcrumb';
		}
		if($hideTitle){
			remove_action( 'storefront_page', 'storefront_page_header',	10 );
			remove_action( 'storefront_single_post', 'storefront_post_header', 10 );

		}
		if($hideFooter){
			remove_all_actions('storefront_footer');
		}

	}

	/**
	 * Enqueue CSS and custom styles.
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_styles() {
		wp_enqueue_style( 'sfxpc-styles', plugins_url( '/assets/css/style.css', __FILE__ ) );

		//Check if it is a supported taxonomy term archive
		if(is_tax($this->supported_taxonomies) || is_tag() || is_category()){
			$css = $this->sfxpc_tax_styles();
			wp_add_inline_style( 'sfxpc-styles', $css );
			return;
		}
		
		$is_shop=false;
		if(function_exists('is_shop')){
			if(is_shop()){
				$is_shop = true;
			}
		}
		
		
		// check if this is single post or page or product or shop
		if(!is_singular($this->supported_post_types) && !$is_shop && !is_home()) {
			return;
		}

		global $post;

		$css = '';

		$showPagePostTitle = null;

		//Meta values for the page
		if($is_shop){
			$current_post = get_option( 'woocommerce_shop_page_id' );
		}elseif(is_home()){
			$current_post = get_option( 'page_for_posts' );
		}else{
			$current_post = false;
		}

		$layout = $this->get_value('general', 'layout', 'right', $current_post);
		$hideHeader = $this->get_value('header', 'hide-header', false, $current_post);
		$hidePrimaryNav = $this->get_value('header', 'hide-primary-menu', null, $current_post);
		$hideSecondaryNav = $this->get_value('header', 'hide-secondary-menu', null, $current_post);
		$hideHeaderCart = $this->get_value('header', 'hide-shop-cart', null, $current_post);
		$hideBreadcrumbs = $this->get_value('header', 'hide-breadcrumbs', null, $current_post);
		$hideTitle = $this->get_value('header', 'hide-title', '', $current_post);
		$headerBgColor = $this->get_value('header', 'header-background-color', null, $current_post);
		$headerBgImage = $this->get_value('header', 'header-background-image', null, $current_post);
		$headerLinkColor = $this->get_value('header', 'header-link-color', null, $current_post);
		$headerTextColor = $this->get_value('header', 'header-text-color', null, $current_post);
		$BgColor = $this->get_value('background', 'background-color', null, $current_post);
		$BgOptions = ' '.$this->get_value('background', 'background-repeat', null, $current_post).' '
		  . $this->get_value('background', 'background-attachment', null, $current_post).' '
		  . $this->get_value('background', 'background-position', null, $current_post);
		$BgImage = $this->get_value('background', 'background-image', null, $current_post);
		$bodyLinkColor = $this->get_value('content', 'body-link-color', null, $current_post);
		$bodyTextColor = $this->get_value('content', 'body-text-color', null, $current_post);
		$bodyHeadColor = $this->get_value('content', 'body-head-color', null, $current_post);
		$hideFooter = $this->get_value('footer', 'hide-footer', false, $current_post);

		//Hiding the title for Shop Page, Post, Products and Page
		if($is_shop && $hideTitle){
			$css .= '.page-title { display: none !important; }';
		}elseif(is_home() && $hideTitle){
			$css .= '.blog-header { display: none !important; }';
		}elseif (in_array($post->post_type, $this->supported_post_types) && $hideTitle){
			//Fallback for Products
			$css .= '.product_title { display: none !important; }';
		}

		//Solving negative margin for product rating
		if (in_array($post->post_type, array('product')) && $hideTitle){
			$css .= '.single-product div.product .woocommerce-product-rating{margin-top:0;}';
		}

		//Layout
		remove_filter( 'body_class', 'storefront_layout_class' );
		$this->body_classes[] = $layout . '-sidebar';
		
		if($hideHeader){
			$css .= "#masthead { display:none !important; }\n";
		}
		if ($headerBgColor) {
			$headerBgColorDark = storefront_adjust_color_brightness($headerBgColor, -16);
			$css .= "#masthead { background: {$headerBgColor} !important; }"
				. ".sub-menu , .site-header-cart .widget_shopping_cart { background: {$headerBgColor} !important; }\n";
		}
		if ($headerBgImage) {
			$css .= "#masthead { background-image: url('$headerBgImage') !important; }\n";
		}
		if($headerLinkColor){
			$css .= ".main-navigation ul li a, .site-title a, ul.menu li:not(.current_page_item) a, .site-branding h1 a{ color: $headerLinkColor !important; }";
		}
		if($headerTextColor){
			$css .= "p.site-description, ul.menu li.current-menu-item > a, .site-header-cart .widget_shopping_cart, .site-header .product_list_widget li .quantity{ color: $headerTextColor !important; }";
		}
		if ($BgColor) {
			$headerBgColorDark = storefront_adjust_color_brightness($headerBgColor, -16);
			$css .= "body.sfx-page-customizer-active { background: {$BgColor} !important; }";
		}
		if($bodyLinkColor){
			$css .= "a { color: $bodyLinkColor !important; }";
		}
		if($bodyTextColor){
			$css .= "body, .secondary-navigation a, .widget-area .widget a, .onsale, #comments .comment-list .reply a { color: $bodyTextColor !important; }";
		}
		if($bodyHeadColor){
			$css .= "h1, h2, h3, h4, h5, h6 { color: $bodyHeadColor !important; }";
		}
		if ($BgImage) {
			$css .= "body.sfx-page-customizer-active { background: url('$BgImage'){$BgOptions} !important; }\n";
		}
		if($hideFooter){
			$css .= "footer.site-footer { display:none !important; }\n";
		}

		wp_add_inline_style( 'sfxpc-styles', $css );
	}

	public function sfxpc_tax_styles(){
		$term = get_queried_object();
		$setting_name = $this->token. '-cat' . $term->term_id;
		$tax_data = get_option($setting_name);

		if(!isset($tax_data['header']['hide-header']))$tax_data['header']['hide-header'] = false;
		if(!isset($tax_data['header']['hide-primary-menu']))$tax_data['header']['hide-primary-menu'] = false;
		if(!isset($tax_data['header']['hide-secondary-menu']))$tax_data['header']['hide-secondary-menu'] = false;
		if(!isset($tax_data['header']['hide-shop-cart']))$tax_data['header']['hide-shop-cart'] = false;
		if(!isset($tax_data['header']['hide-breadcrumbs']))$tax_data['header']['hide-breadcrumbs'] = false;
		if(!isset($tax_data['header']['hide-title']))$tax_data['header']['hide-title'] = false;
		if(!isset($tax_data['footer']['hide-footer']))$tax_data['footer']['hide-footer'] = false;
	
		if(!$tax_data)return;
		
		//Background options
		$BgImage = $tax_data['background']['background-image'];
		$BgOptions = ' '.$tax_data['background']['background-repeat'].' '
		  . $tax_data['background']['background-attachment'].' '
		  . $tax_data['background']['background-position'];
		$BgColor  = $tax_data['background']['background-color'];

		//Header
		$hideHeader = $tax_data['header']['hide-header'];
		$headerBgColor = $tax_data['header']['header-background-color'];
		$headerBgImage = $tax_data['header']['header-background-image'];
		$headerTextColor = $tax_data['header']['header-text-color'];
		$headerLinkColor = $tax_data['header']['header-link-color'];
		$hidePrimaryNav = $tax_data['header']['hide-primary-menu'];
		$hideSecondaryNav = $tax_data['header']['hide-secondary-menu'];
		$hideHeaderCart = $tax_data['header']['hide-shop-cart'];

		//Content
		$bodyLinkColor = $tax_data['content']['body-link-color'];
		$bodyTextColor = $tax_data['content']['body-text-color'];
		$bodyHeadColor = $tax_data['content']['body-head-color'];
		//General settings
		$layout = $tax_data['general']['layout'];

		$hideBreadcrumbs = $tax_data['header']['hide-breadcrumbs'];
		$hideTitle = $tax_data['header']['hide-title'];
		$hideFooter = $tax_data['footer']['hide-footer'];

		$css = '';

		//For layout
		$this->body_classes[] = $layout . '-sidebar';

		if($hideHeader){
			remove_all_actions( 'storefront_header' );
			$css .= "#masthead { display:none !important; }\n";
		}
		if($hidePrimaryNav){
			remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
		}
		if($hideSecondaryNav){
			remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
		}
		if($hideHeaderCart){
			remove_action( 'storefront_header', 'storefront_header_cart', 		60 );
		}
		if($hideBreadcrumbs){
			remove_action( 'storefront_content_top', 'woocommerce_breadcrumb', 					10 );
			$this->body_classes[] = 'no-wc-breadcrumb';
		}
		if($hideTitle){
			$css .= ".page-title { display:none !important; }\n";
		}
		if($hideFooter){
			remove_all_actions('storefront_footer');
			$css .= "footer.site-footer { display:none !important; }\n";
		}
		if ($headerBgColor) {
			$headerBgColorDark = storefront_adjust_color_brightness($headerBgColor, -16);
			$css .= "#masthead { background: {$headerBgColor} !important; }"
				. ".sub-menu , .site-header-cart .widget_shopping_cart { background: {$headerBgColor} !important; }\n";
		}

		if ($headerBgImage) {
			$css .= "#masthead { background-image: url('$headerBgImage') !important; }\n";
		}

		if($headerLinkColor){
			$css .= ".main-navigation ul li a, .site-title a, ul.menu li:not(.current_page_item) a, .site-branding h1 a{ color: $headerLinkColor !important; }";
		}

		if($headerTextColor){
			$css .= "p.site-description, ul.menu li.current-menu-item > a, .site-header-cart .widget_shopping_cart, .site-header .product_list_widget li .quantity{ color: $headerTextColor !important; }";
		}
		
		if ($BgColor) {
			$headerBgColorDark = storefront_adjust_color_brightness($headerBgColor, -16);
			$css .= "body.sfx-page-customizer-active { background: {$BgColor} !important; }";
		}
		if ($BgImage) {
			$css .= "body.sfx-page-customizer-active { background: url('$BgImage'){$BgOptions} !important; }\n";
		}

		if($bodyLinkColor){
			$css .= "a { color: $bodyLinkColor !important; }";
		}
		if($bodyTextColor){
			$css .= "body, .secondary-navigation a, .widget-area .widget a, .onsale, #comments .comment-list .reply a { color: $bodyTextColor !important; }";
		}
		if($bodyHeadColor){
			$css .= "h1, h2, h3, h4, h5, h6 { color: $bodyHeadColor !important; }";
		}

		return $css;
	}

	/**
	 * Print custom js
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_script() {
	?>
	<script id='sfx-pc-script'>
	jQuery(document).ready(function($){
		<?php
		if(is_home()){
		/*
			$current_post_id = get_option( 'page_for_posts' );
			$pagePostTitleMeta = $this->get_value('header', 'hide-title', 'default', $current_post_id);
			if($pagePostTitleMeta=='show'){
			?>
		$('#main')
			.prepend('<header class="entry-header blog-header"><h1 class="entry-title" itemprop="name"><?php echo get_the_title($current_post_id); ?></h1></header>')
			.addClass('hentry');
			<?php
			}
		*/
		}
	?>
	})
	</script>
	<?php
	}

	/**
	 * Enqueue Js 
	 * @global type $pagenow
	 * @return null
	 */
	public function admin_scripts() {
		global $pagenow;

		if($pagenow=='edit-tags.php'){
			//Though everything is commented this if section is still important coz it prevents returning the function
			wp_enqueue_media();
			//wp_enqueue_script('sfxpc-tax-script', trailingslashit($this->plugin_url) . 'assets/js/admin/taxonomy.js', array('wp-color-picker', 'thickbox', 'jquery'));
		}elseif(
		  (!isset($pagenow) || !($pagenow == 'post-new.php' || $pagenow == 'post.php'))
		  OR
		  (isset($_REQUEST['post-type']) && strtolower($_REQUEST['post_type']) != 'page')
		) {
			return;
		}

		// only in post and page create and edit screen

		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('sfxpc-admin-script', trailingslashit($this->plugin_url) . 'assets/js/admin/admin.js', array('wp-color-picker', 'jquery', 'thickbox'));

		wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('wp-mediaelement');
		wp_enqueue_style('thickbox');
		wp_enqueue_style('sfxpc-admin-style', trailingslashit($this->plugin_url) . 'assets/css/admin/admin.css');
	}

	
	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 *
	 * @since  1.0.0
	 */
	public function sfxpc_customize_preview_js() {
		wp_enqueue_script( 'sfxpc-customizer', plugins_url( '/assets/js/customizer.min.js', __FILE__ ), array( 'customize-preview' ), '1.1', true );
	}

	/**
	 * SFX Page Customizer Body Class
	 * Adds a class based on the extension name and any relevant settings.
	 */
	public function sfxpc_body_class( $classes ) {
		$this->body_classes[] = 'sfx-page-customizer-active';
		return array_merge($classes, $this->body_classes);
	}
	function sfxpc_admin_body_class( $classes ){
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$classes .= ' woo-commerce-active ';
		}
		return $classes;
	}

	/**
	 * Render a field of a given type.
	 * @access public
	 * @since 1.0.0
	 * @param array $args The field parameters.
	 * @param string $output_format = ( post || termEdit || termAdd )
	 * @param array $tax_data - Taxonomy sfxpc data if rendering for taxonomy
	 * @return string
	 */
	public function render_field ( $args, $output_format = 'post', $tax_data=null ) {
		$html = '';
		$css_class = '';
		if( isset($args['css-class']) ) $css_class .= $args['css-class'];

		if ( ! in_array( $args['type'], array( 'text', 'checkbox', 'radio', 'textarea', 'select', 'color', 'image' ) ) ) return ''; // Supported field type sanity check.

		// Make sure we have some kind of default, if the key isn't set.
		if ( ! isset( $args['default'] ) ) {
			$args['default'] = '';
		}

		$method = 'render_field_' . $args['type'];

		if ( ! method_exists( $this, $method ) ) {
			$method = 'render_field_text';
		}

		// Construct the key.
		$key = $this->get_field_key($args['section'], $args['id']);
		
		switch($output_format){
			case 'termEdit':
				if(in_array($args['id'], $this->not_in_tax))return;

				//Prefix to field
				$html_prefix = ''
				. '<tr class="form-field sfxpc-field '  . $args['id'] . ' ' . $css_class . '">'
				. '<th scope="row"><label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label></th>'
				. '<td>';

				//Getting current value
				if($tax_data[$args['section']][$args['id']]){
					$current_val = $tax_data[$args['section']][$args['id']];
				}else{
					$current_val = $args['default'];
				}

				//Suffix to field
				$html_suffix = ''
					. '</td>'
					. '</tr>';

				break;
			case 'termAdd':
				if($args['id'] == 'hide-title')return;

				//Prefix to field
				$html_prefix = ''
				. '<div class="form-field sfxpc-field '  . $args['id'] . ' ' . $css_class . '">'
				. '<label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label>';

				//Getting current value
				$current_val = '';

				//Suffix to field
				$html_suffix = ''
					. '</div>';
				
				break;
			default:
				//Prefix to field
				$html_prefix = ''
				. '<div class="field sfxpc-field '  . $args['id'] . ' ' . $css_class . '">'
				. '<label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label>'
				. '<div class="control">';

				//Getting current value
				$current_val = $this->get_value( $args['section'], $args['id'], $args['default'] );

				//Suffix to field
				$html_suffix = ''
					. '</div>'
					. '</div>';
		}

		//Prefix
		$html .= $html_prefix;

		//Output the field
		$method_output = $this->$method( $key, $args, $current_val );
		$html .= $method_output;

		// Output the description
		if ( isset( $args['description'] ) ) {
			$description = '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>' . "\n";
			if ( in_array( $args['type'], (array)apply_filters( 'wf_newline_description_fields', array( 'textarea', 'select' ) ) ) ) {
					$description = wpautop( $description );
				}
			$html .= $description;
		}

		//Suffix
		$html .= $html_suffix;
		
		echo $html;
	}

	/**
	 * Render HTML markup for the "text" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_text ( $key, $args, $current_val=null ) {
		$html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" size="40" type="text" value="' . esc_attr( $current_val ) . '" />' . "\n";
		return $html;
	}

	/**
	 * Render HTML markup for the "radio" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_radio ( $key, $args, $current_val=null ) {
		$html = '';
		if ( isset( $args['options'] ) && ( 0 < count( (array)$args['options'] ) ) ) {
			foreach ( $args['options'] as $k => $v ) {
				$html .= '<label for="' . esc_attr( $key ) . '"><input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $k ) . '"' . checked( esc_attr( $current_val ), $k, false ) . ' /> ' . $v . '</label><br>' . "\n";
			}
		}
		return $html;
	}

	/**
	 * Render HTML markup for the "textarea" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_textarea ( $key, $args, $current_val=null ) {
		$html = '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" cols="42" rows="5">' . $current_val . '</textarea>' . "\n";
		return $html;
	}

	/**
	 * Render HTML markup for the "checkbox" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_checkbox ( $key, $args, $current_val=null ) {
		$html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="checkbox" value="true" ' . checked( $current_val, 'true', false ) . ' />';
		return $html;
	}

	/**
	 * Render HTML markup for the "select" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_select ( $key, $args, $current_val=null ) {
		$html = '';
		if ( isset( $args['options'] ) && ( 0 < count( (array)$args['options'] ) ) ) {
			$html .= '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . "\n";
			foreach ( $args['options'] as $k => $v ) {
				$html .= '<option value="' . esc_attr( $k ) . '"' . selected( esc_attr( $current_val ), $k, false ) . '>' . esc_html( $v ) . '</option>' . "\n";
			}
			$html .= '</select>' . "\n";
		}
		return $html;
	}

	/**
	 * Render HTML markup for the "color" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_color( $key, $args, $current_val=null ) {
		$html = '<input class="color-picker-hex" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="text" value="' . esc_attr( $current_val ) . '" />';
		return $html;
	}

	/**
	 * Render HTML markup for the "image" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected  function render_field_image( $key, $args, $current_val=null ) {
		$html = '<input class="image-upload-path" type="text" id="' . esc_attr($key) . '" style="width: 200px; max-width: 100%;" name="' . esc_attr($key) . '" value="' . esc_attr( $current_val ) . '" /><button class="button upload-button">Upload</button>';
		return $html;
	}

} // End Class