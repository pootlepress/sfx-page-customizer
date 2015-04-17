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
	 * Array of classes to be put in body
	 * @var array 
	 */
	public $body_classes = array();
	
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
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
			add_action( 'customize_preview_init', array( $this, 'sfxpc_customize_preview_js' ) );
			add_filter( 'body_class', array( $this, 'sfxpc_body_class' ) );
			add_filter( 'admin_body_class', array( $this, 'sfxpc_admin_body_class') );
			add_action( 'admin_notices', array( $this, 'sfxpc_customizer_notice' ) );
			// Hide the 'More' section in the customizer
			add_filter( 'storefront_customizer_more', '__return_false' );
			foreach ($this->supported_taxonomies as $tax){
				add_action( "{$tax}_edit_form", array( $this, 'tax_custom_fields' ) );
			}
			add_action( 'edit_terms', array( $this, 'save_term_fields' ) );
		}
	}

	/**
	 * Admin notice
	 * Checks the notice setup in install(). If it exists display it then delete the option so it's not displayed again.
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_customizer_notice() {
		if ( $notices = get_option( 'sfxpc_activation_notice' ) ) {

			foreach ( $notices as $notice ) {
				echo '<div class="updated">' . $notice . '</div>';
			}

			delete_option( 'sfxpc_activation_notice' );
		}
	}

	public function register_meta_box() {
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this post', array($this, 'custom_fields'), 'post' );
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this page', array($this, 'custom_fields'), 'page' );
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this product', array($this, 'custom_fields'), 'product' );
	}

	public function save_post($postID) {
		
		//Checking Nonce
		if ( ! isset( $_POST['sfx-pc-nonce'] ) || ! wp_verify_nonce( $_POST['sfx-pc-nonce'], 'sfx-pc-post-meta' ) ){
			return;
		}

		$post = get_post($postID);
		 
		//check if post type is post,page or product
		if ( !in_array($post->post_type, $this->supported_post_types) || !isset($_POST[$this->token]) ) {
			return;
		}

		//Caching postdata
		$data = $_POST[$this->token];

		if ( is_array($data)) {
			update_post_meta($postID, $this->token, $data);
		}
	}

	public function save_term_fields($ID) {

		if ( ! isset( $_POST['sfx-pc-nonce'] ) || ! wp_verify_nonce( $_POST['sfx-pc-nonce'], 'sfx-pc-tax-meta' ) ){
			return;
		}

		if (isset($_POST[$this->token]) && is_array($_POST[$this->token])) {
			$setting_name = $this->token.'-cat'.$ID;
			$sfxPCValues = $_POST[$this->token];
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
	
	/**
	 * PostMeta Callback
	 * 
	 * @param postObject $post
	 */
	public function custom_fields( $post ) {
		$fields = $this->post_meta;
		$class = ' sfxpc-metabox sfxpc-tabs-wrapper ';
		$postMetaValues = get_post_meta( $post->ID , $this->token , true);
		echo "<div class='{$class}'>";

		//WP Nonce
		wp_nonce_field( 'sfx-pc-post-meta', 'sfx-pc-nonce' );
		
		$field_structure = array();
		foreach ($fields as $key => $field) {
			$field_structure[$field['section']][] = $field;
		}
		echo "<ul class='sfxpc-sections-nav nav-tab-wrapper'>";
		  foreach( $field_structure as $sec => $fields ){
			  $Sec = ucwords($sec);
			echo "<li> <a href='#sfxpc-section-{$sec}'> $Sec </a> </li>";
		  }
		echo "</ul>";
		foreach( $field_structure as $sec => $fields ){
			echo "<div class='sfxpc-section' id='sfxpc-section-{$sec}'>";
			foreach ($fields as $fld){
				$this->render_field($fld, 'post', $postMetaValues);
			}
			echo "</div>";
		}
		
		echo "</div>";
	}
	
	public function tax_custom_fields($term) {

		$output_format = 'termEdit';
		$tax_sfxpc_data = get_option( $this->token. '-cat' . $term->term_id );

		$fields = $this->post_meta;

		$taxonomy = get_taxonomy($term->taxonomy);
		echo '<h2>Customize Storefront options for this ' . $taxonomy->labels->singular_name . ' archive</h2>';

		//Nonce
		wp_nonce_field( 'sfx-pc-tax-meta', 'sfx-pc-nonce' );

		echo '<table class="form-table">';
		foreach ($fields as $key => $field) {
			$this->render_field($field, $output_format, $tax_sfxpc_data);
		}
		echo '</table>';
	}
 
	/**
	 * Gets value of post meta
	 * @global string $post
	 * @param string $section
	 * @param string $id
	 * @param string $default
	 * @param string $post_id
	 * @return string
	 */
	protected function get_value($section, $id, $default = null, $post_id = null) {
		//Getting post id if not set
		if( null === $post_id ){ global $post; $post_id = $post->ID; }

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

	public function get_post_settings(){

		$is_shop=false;

		if(function_exists('is_shop') && is_shop()){
			$is_shop = true;
		}

		global $post;

		//Meta values for the page
		if($is_shop){
			$current_post = get_option( 'woocommerce_shop_page_id' );
		}elseif(is_home()){
			$current_post = get_option( 'page_for_posts' );
		}else{
			$current_post = $post->ID;
		}

		return get_post_meta( $current_post , $this->token , true);

	}

	/**
	 * Taxonomy Style
	 * 
	 * @TODO Get rid of it
	 * @return void|null
	 */
	public function get_tax_settings(){

		//Get term object
		$term = get_queried_object();
		//Get the setting name
		$setting_name = $this->token. '-cat' . $term->term_id;
		//Return the settings (option)
		return get_option($setting_name);

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
			$settings = $this->get_tax_settings();
		}else{
			if( ! $settings = $this->get_post_settings() ){	
				return;
			}
		}
 
		$css = '';

		//BG styles
		$css .= $this->background_styles( $settings['background'] );
		//Header styles
		$css .= $this->header_styles( $settings['header'] );
		//Content styles
		$css .= $this->content_styles( $settings['content'] );
		//Footer styles
		if( ! empty( $settings['footer']['hide-footer'] ) ){
			remove_all_actions('storefront_footer');
			$css .= "footer.site-footer { display:none !important; }\n";
		}

		wp_add_inline_style( 'sfxpc-styles', $css );
	}

	/**
	 * Background styles.
	 * 
	 * @since   1.0.0
	 * @param string|null $current_post
	 * @return  string CSS for header
	 */
	public function background_styles( $bg ) {
		$css = '';

		//Preparing BG Option
		$BgOptions = ' '.$bg['background-repeat'] . ' '
		  . $bg['background-attachment'] . ' '
		  . $bg['background-position'];

		if ( $bg['background-color'] ) {
			$css .= "body.sfx-page-customizer-active { background: {$bg['background-color']} !important; }";
		}
		if ( $bg['background-image'] ) {
			$css .= "body.sfx-page-customizer-active { background: {$bg['background-color']} url('{$bg['background-image']}'){$BgOptions} !important; }\n";
		}

		return $css;
	}

	/**
	 * Header styles.
	 * 
	 * @since   1.0.0
	 * @param array $head Header settings
	 * @return  string CSS for header
	 */
	public function header_styles( $head ) {
		$css = '';

		$css .= $this->hide_title(! empty( $head['hide-title']));

		if( ! empty( $head['hide-header'] ) ){
			remove_all_actions( 'storefront_header' );
			$css .= "#masthead { display:none !important; }\n";
		}
		if( ! empty( $head['hide-primary-menu'] ) ){
			remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
		}
		if( ! empty( $head['hide-secondary-menu'] ) ){
			remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
		}
		if( ! empty( $head['hide-shop-cart'] ) ){
			remove_action( 'storefront_header', 'storefront_header_cart', 		60 );
		}
		if( ! empty( $head['hide-breadcrumbs'] ) ){
			remove_action( 'storefront_content_top', 'woocommerce_breadcrumb', 					10 );
			$this->body_classes[] = 'no-wc-breadcrumb';
		}

		if($head['header-background-color']){
			$css .= "#masthead, .sub-menu , .site-header-cart .widget_shopping_cart { background: {$head['header-background-color']} !important; }";
		}
		if($head['header-background-image']){
			$css .= "#masthead { background-image: url('{$head['header-background-image']}') !important; }\n";
		}
		if($head['header-link-color']){
			$css .= ".main-navigation ul li a, .site-title a, ul.menu li:not(.current_page_item) a, .site-branding h1 a{ color: {$head['header-link-color']} !important; }";
		}
		if($head['header-text-color']){
			$css .= "p.site-description, ul.menu li.current-menu-item > a, .site-header-cart .widget_shopping_cart, .site-header .product_list_widget li .quantity{ color: {$head['header-text-color']} !important; }";
		}

		return $css;
	}

	/**
	 * Content styles.
	 * 
	 * @since   1.0.0
	 * @param array $content Content settings
	 * @return  string CSS for header
	 */
	public function content_styles( $content ) {

		$css = '';
		//Layout
		remove_filter( 'body_class', 'storefront_layout_class' );
		$this->body_classes[] = $content['layout'] . '-sidebar';
		
		if($content['body-link-color']){
			$css .= "a { color: {$content['body-link-color']} !important; }";
		}
		if($content['body-text-color']){
			$css .= "body, .secondary-navigation a, .widget-area .widget a, .onsale, #comments .comment-list .reply a { color: {$content['body-text-color']} !important; }";
		}
		if($content['body-head-color']){
			$css .= "h1, h2, h3, h4, h5, h6 { color: {$content['body-head-color']} !important; }";
		}

		return $css;
	}
	
	/**
	 * Hides the title
	 * 
	 * @since   1.0.0
	 * @param bool $hideTitle
	 */
	public function hide_title( $hideTitle ){

		//Hiding the title for Shop and Products
		if( function_exists('is_shop') && is_shop() && $hideTitle ){
			$css .= '.page-title { display: none !important; }';
		}elseif ( $hideTitle ){
			//Fallback for Products
			$css .= '.product_title { display: none !important; }';
			//Solving negative margin for product rating
			$css .= '.single-product div.product .woocommerce-product-rating{margin-top:0;}';
		}

		//Removing the title for post and pages
		if($hideTitle){
			remove_action( 'storefront_page', 'storefront_page_header',	10 );
			remove_action( 'storefront_single_post', 'storefront_post_header', 10 );
		}
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
		}elseif(
		  (!isset($pagenow) || !($pagenow == 'post-new.php' || $pagenow == 'post.php'))
		  OR
		  (isset($_POST['post-type']) && strtolower($_POST['post_type']) != 'page')
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
	
	/**
	 * Adds required classes to admin
	 * @param string $classes
	 * @return string
	 */
	public function sfxpc_admin_body_class( $classes ){
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
	 * @param string $output_format = ( post || termEdit )
	 * @param array $current_data - Current data to put current values in the fields
	 * @return string
	 */
	public function render_field ( $args, $output_format = 'post', $current_data ) {

		// Construct the key.
		$args['key'] = $this->get_field_key($args['section'], $args['id']);


		//Setting blank css-class key if not set
		if( !isset($args['css-class']) ) $args['css-class'] = '';

		// Make sure we have some kind of default, if the key isn't set.
		if ( ! isset( $args['default'] ) ) {
			$args['default'] = '';
		}

		if( $output_format == 'termEdit' ){
			$this->render_tax_field( $args, $current_data );
		}else{
			$this->render_post_meta_field( $args, $current_data );
		}

	}

	/**
	 * Rendrs the taxonomy Field
	 * 
	 * @param array $args Argument for field
	 * @param array|null $tax_data Taxonomy data
	 */
	protected function render_tax_field( $args, $tax_data ){

		//Prefix to HTML
		$html_prefix = ''
		. '<tr class="form-field sfxpc-field '  . $args['id'] . ' ' . $args['css-class'] . '">'
		. '<th scope="row"><label class="label" for="' . esc_attr($args['key']) . '">' . esc_html($args['label']) . '</label></th>'
		. '<td>';

		$current_val = $args['default'];
		//Getting current value
		if( isset( $tax_data[ $args['section'] ][ $args['id'] ] ) ){
			$current_val = $tax_data[$args['section']][$args['id']];
		}

		//Suffix to field
		$html_suffix = ''
			. '</td>'
			. '</tr>';

		//Output Field
		$this->output_rendered_field($html_prefix, $html_suffix, $args, $current_val);

	}

	/**
	 * Renders the post meta Field
	 * 
	 * @param array $args Argument for field
	 */
	protected function render_post_meta_field( $args, $post_meta ){

		$html_prefix = ''
		. '<div class="field sfxpc-field '  . $args['id'] . ' ' . $args['css-class'] . '">'
		. '<label class="label" for="' . esc_attr($args['key']) . '">' . esc_html($args['label']) . '</label>'
		. '<div class="control">';
		
		$current_val = $args['default'];
		//Getting current value
		if( isset( $post_meta[ $args['section'] ][ $args['id'] ] ) ){
			$current_val = $post_meta[$args['section']][$args['id']];
		}

		//Suffix to field
		$html_suffix = ''
			. '</div>'
			. '</div>';

		//Output Field
		$this->output_rendered_field($html_prefix, $html_suffix, $args, $current_val);

	}

	/**
	 * Outputs the field after rendering with HTML for the context
	 * 
	 * @param string $html_prefix
	 * @param string $html_suffix
	 * @param array $args Argument for field
	 * @param string $current_val Current value of the field
	 */
	protected function output_rendered_field( $html_prefix, $html_suffix, $args, $current_val ){

		//Getting the method for field
		$method = 'render_field_' . $args['type'];
		if ( ! method_exists( $this, $method ) ) {
			$method = 'render_field_text';
		}

		//Output the field
		$html = $this->$method( $args['key'], $current_val, $args );

		// Output the description
		if ( isset( $args['description'] ) ) {
			$html .= '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>' . "\n";
		}

		//Ouput $html with suffix and prefix
		echo $html_prefix . $html . $html_suffix;

	}

	/**
	 * Render HTML markup for the "text" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_text ( $key, $current_val ) {
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
	protected function render_field_radio ( $key, $current_val, $args ) {
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
	protected function render_field_textarea ( $key, $current_val ) {
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
	protected function render_field_checkbox ( $key, $current_val ) {
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
	protected function render_field_select ( $key, $current_val, $args ) {
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
	protected function render_field_color( $key, $current_val ) {
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
	protected  function render_field_image( $key, $current_val ) {
		$html = '<input class="image-upload-path" type="text" id="' . esc_attr($key) . '" style="width: 200px; max-width: 100%;" name="' . esc_attr($key) . '" value="' . esc_attr( $current_val ) . '" /><button class="button upload-button">Upload</button>';
		return $html;
	}

} // End Class
