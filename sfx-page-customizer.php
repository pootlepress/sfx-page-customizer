<?php
/**
 * Plugin Name: Storefront Extension - Page Customizer
 * Plugin URI: http://woothemes.com/products/sfx-page-customizer/
 * Description:	@TODO write a description with Nick
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
 * @author Shramee
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
 * @author Shramee <shramee.srivastav@gmail.com>
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
	 * All the post metas to populate.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $post_meta = array();

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

		// get theme customizer url
		$url = admin_url() . 'customize.php?';
		$url .= 'url=' . urlencode( site_url() . '?storefront-customizer=true' ) ;
		$url .= '&return=' . urlencode( admin_url() . 'plugins.php' );
		$url .= '&storefront-customizer=true';

		$notices 		= get_option( 'sfxpc_activation_notice', array() );
		$notices[]		= sprintf( __( '%sThanks for installing the SFX Page Customizer extension. To get started, visit the %sCustomizer%s.%s %sOpen the Customizer%s', 'sfx-page-customizer' ), '<p>', '<a href="' . esc_url( $url ) . '">', '</a>', '</p>', '<p><a href="' . esc_url( $url ) . '" class="button button-primary">', '</a></p>' );

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
			$this->get_meta_fields();
			
			add_action('admin_init', array($this, 'register_meta_box'));
			add_action('save_post', array($this, 'save_post'));

			add_action('admin_print_scripts', array($this, 'admin_scripts'));
			add_action( 'wp_enqueue_scripts', array( $this, 'sfxpc_styles' ), 999 );
			add_action( 'admin_print_scripts', array( $this, 'sfxpc_script' ), 999 );
			add_action( 'customize_register', array( $this, 'sfxpc_customize_register' ) );
			add_action( 'customize_preview_init', array( $this, 'sfxpc_customize_preview_js' ) );
			add_filter( 'body_class', array( $this, 'sfxpc_body_class' ) );
			add_action( 'admin_notices', array( $this, 'sfxpc_customizer_notice' ) );

			// Hide the 'More' section in the customizer
			add_filter( 'storefront_customizer_more', '__return_false' );
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
	public function sfxpc_customize_register( $wp_customize ) {

		/**
	     * Add a new section
	     */
        $wp_customize->add_section( 'sfxpc_section' , array(
		    'title'      	=> __( 'SFX Page Customizer', 'sfx-page-customizer' ),
		    //'description' 	=> __( 'Add a description, if you want to!', 'sfx-page-customizer' ),
		    'priority'   	=> 55,
		) );

		/**
		 * Show Hide Page Post Title
		 */
		$wp_customize->add_setting('sfx-pc-show-page-post-title[checked]', array(
			'type' => 'option',
			'default' => true
		));

		$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'sfx-pc-show-page-post-title', array(
			'type' => 'checkbox',
			'label' => 'Show page/post and product titles globally',
			'section' => 'sfxpc_section',
			'settings' => 'sfx-pc-show-page-post-title[checked]',
			'default' => 1,
			'priority' => 1,
		)));
	}

	public function register_meta_box() {
		add_meta_box('sfx-pc-meta-box', 'Storefront settings', array($this, 'meta_box'), 'post');
		add_meta_box('sfx-pc-meta-box', 'Storefront settings', array($this, 'meta_box'), 'page');
		//adding Storefront settings in Woocommerce product page
		add_meta_box( 'sfx-pc-meta-box', 'Storefront settings', array($this, 'meta_box'), 'product');
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
				$new_val = $sfxPCValues[$meta['section']][$meta['id']];
				update_post_meta($postID, $meta_id, $new_val);
			}
		}
	}
	
	private function get_supported_post_types(){
		$this->supported_post_types = array(
		  'post',
		  'page',
		  'product'
		);
	}

	private function get_meta_fields() {
		$this->post_meta = array(
			'page-post-title' => array(
				'id' => 'page-post-title',
				'section' => 'header',
				'label' => 'Show/Hide title',
				'type' => 'select',
				'default' => 'default',
				'options' => array('default' => 'Global default', 'show' => 'Show', 'hide' => 'Hide')
			),
/*			'header-background-image' => array(
				'id' => 'header-background-image',
				'section' => 'header',
				'label' => 'Header background image',
				'type' => 'image',
				'default' => '',
			),
*/		  'header-background-color' => array(
				'id' => 'header-background-color',
				'section' => 'header',
				'label' => 'Header background color',
				'type' => 'color',
				'default' => '',
			),
			'header-text-color' => array(
				'id' => 'header-text-color',
				'section' => 'header',
				'label' => 'Header text color',
				'type' => 'color',
				'default' => '#5a6567',
			),
			'header-link-color' => array(
				'id' => 'header-link-color',
				'section' => 'header',
				'label' => 'Header link color',
				'type' => 'color',
				'default' => '#ffffff',
			)
		);
	}

	public function meta_box() {

		$fields = $this->post_meta;

		foreach ($fields as $key => $field) {
			$this->render_field($field);
		}
	}

	protected function get_value($section, $id, $default = null, $post_id=false) {
		//Getting post id if not set
		if(!$post_id){ global $post; $post_id = $post->ID; }
		
		$metaKey = $this->get_meta_key($section, $id);

		$ret = get_post_meta($post_id, $metaKey, true);
		if (isset($ret) && $ret != false) {
			return $ret;
		} else {
			return $default;
		}
	}

	private function get_meta_key($section, $id) {
		return $this->token . '-' . $section . '-' . $id;
	}

	private function get_field_key($section, $id) {
		return $this->token . '[' . $section . '][' . $id . ']';
	}

	/**
	 * Render a field of a given type.
	 * @access  public
	 * @since   1.0.0
	 * @param   array $args The field parameters.
	 * @return  void
	 */
	public function render_field ( $args ) {
		$html = '';
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
		$key 				= $this->get_field_key($args['section'], $args['id']);
		$method_output 		= $this->$method( $key, $args );

		if ( is_wp_error( $method_output ) ) {
			// if ( defined( 'WP_DEBUG' ) || true == constant( 'WP_DEBUG' ) ) print_r( $method_output ); // Add better error display.
		} else {
			$html .= $method_output;
		}

		// Output the description, if the current field allows it.
		if ( isset( $args['type'] ) && ! in_array( $args['type'], (array)apply_filters( 'wf_no_description_fields', array( 'checkbox' ) ) ) ) {
			if ( isset( $args['description'] ) ) {
				$description = '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>' . "\n";
				if ( in_array( $args['type'], (array)apply_filters( 'wf_newline_description_fields', array( 'textarea', 'select' ) ) ) ) {
					$description = wpautop( $description );
				}
				$html .= $description;
			}
		}

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
	protected function render_field_text ( $key, $args ) {
		$html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" size="40" type="text" value="' . esc_attr( $this->get_value( $args['id'], $args['default'], $args['section'] ) ) . '" />' . "\n";
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
	protected function render_field_radio ( $key, $args ) {
		$html = '';
		if ( isset( $args['options'] ) && ( 0 < count( (array)$args['options'] ) ) ) {
			$html = '';
			foreach ( $args['options'] as $k => $v ) {
				$html .= '<input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $k ) . '"' . checked( esc_attr( $this->get_value( $args['id'], $args['default'], $args['section'] ) ), $k, false ) . ' /> ' . esc_html( $v ) . '<br />' . "\n";
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
	protected function render_field_textarea ( $key, $args ) {
		// Explore how best to escape this data, as esc_textarea() strips HTML tags, it seems.
		$html = '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" cols="42" rows="5">' . $this->get_value( $args['id'], $args['default'], $args['section'] ) . '</textarea>' . "\n";
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
	protected function render_field_checkbox ( $key, $args ) {
		$html = '';
		$html .= '<div class="field">';
		$html .= '<label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label>';
		$html .= '<div class="control"><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="checkbox" value="true" ' . checked($this->get_value($args['section'], $args['id'], $args['default']), 'checked', false ) . ' /></div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render HTML markup for the "select2" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_select ( $key, $args ) {
		$html = '';
		$html .= '<div class="field">';
		$html .= '<label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label>';
		$html .= '<div class="control">';
		if ( isset( $args['options'] ) && ( 0 < count( (array)$args['options'] ) ) ) {
			$html .= '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . "\n";
			foreach ( $args['options'] as $k => $v ) {
				$html .= '<option value="' . esc_attr( $k ) . '"' . selected( esc_attr( $this->get_value($args['section'], $args['id'], $args['default']) ), $k, false ) . '>' . esc_html( $v ) . '</option>' . "\n";
			}
			$html .= '</select>' . "\n";
		}
		$html .= '</div>';
		$html .= '</div>';
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
	protected function render_field_color($key, $args) {
		$html = '';
		$html .= '<div class="field">';
		$html .= '<label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label>';
		$html .= '<div class="control"><input class="color-picker-hex" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="text" value="' . esc_attr( $this->get_value($args['section'], $args['id'], $args['default']) ) . '" /></div>';
		$html .= '</div>';
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
	protected  function render_field_image($key, $args) {
		$html = '';
		$html .= '<div class="field">';
		$html .= '<label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label>';
		$html .= '<div class="control"><input class="image-upload-path" type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($this->get_value($args['section'], $args['id'], $args['default'])) . '" /><button class="button upload-button">Upload</button></div>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * Enqueue CSS and custom styles.
	 * @since   1.0.0
	 * @return  void
	 */
	public function sfxpc_styles() {
		wp_enqueue_style( 'sfxpc-styles', plugins_url( '/assets/css/style.css', __FILE__ ) );
		
		// check if this is single post or page or product or shop
		if(!is_singular($this->supported_post_types) && !is_shop() && !is_home()) {
			return;
		}

		global $post;

		$css = '';

		$showPagePostTitle = null;

		//Meta values for the page
		if(is_shop()){
			$current_post = get_option( 'woocommerce_shop_page_id' );
		}elseif(is_home()){
			$current_post = get_option( 'page_for_posts' );
		}else{
			$current_post = false;
		}
		$pagePostTitleMeta = $this->get_value('header', 'page-post-title', 'default', $current_post);
		$headerBgColor = $this->get_value('header', 'header-background-color', null, $current_post);
		$headerLinkColor = $this->get_value('header', 'header-link-color', null, $current_post);
		$headerTextColor = $this->get_value('header', 'header-text-color', null, $current_post);

	
		if ($pagePostTitleMeta == 'default') {
			$arr = get_option('sfx-pc-show-page-post-title', array('checked' => true));
			if (is_array($arr) && isset($arr['checked'])) {
				$showPagePostTitleGlobally = $arr['checked'] == true;
			} else {
				$showPagePostTitleGlobally = false;
			}

			$showPagePostTitle = $showPagePostTitleGlobally;
		} else {
			if ($pagePostTitleMeta == 'show') {
				$showPagePostTitle = true;
			} else {
				$showPagePostTitle = false;
			}
		}
		
		//Hiding the title for Shop Page, Post, Products and Page
		if(is_shop() && !$showPagePostTitle){
			$css .= '.page-title { display: none !important; }';
		}elseif(is_home() && !$showPagePostTitle){
			$css .= '.blog-header { display: none !important; }';
		}
		elseif (in_array($post->post_type, $this->supported_post_types) && !$showPagePostTitle){
			$css .= '.entry-title { display: none !important; }';
		}

		
		//Solving negative margin for product rating
		if (in_array($post->post_type, array('product')) && !$showPagePostTitle){
			$css .= '.single-product div.product .woocommerce-product-rating{margin-top:0;}';
		}

		if ($headerBgColor) {
			$headerBgColorDark = storefront_adjust_color_brightness($headerBgColor, -16);
			$css .= "#masthead { background: {$headerBgColor} !important; }"
				. ".sub-menu , .site-header-cart .widget_shopping_cart { background: {$headerBgColorDark} !important; }\n";
		}

		if($headerLinkColor){
			$css .= ".main-navigation ul li a, .site-title a, ul.menu li:not(.current_page_item) a, .site-branding h1 a{ color: $headerLinkColor !important; }";
		}

		if($headerTextColor){
			$css .= "p.site-description, ul.menu li.current-menu-item > a, .site-header-cart .widget_shopping_cart, .site-header .product_list_widget li .quantity{ color: $headerTextColor !important; }";
		}
		
		wp_add_inline_style( 'sfxpc-styles', $css );
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
			$pagePostTitleMeta = $this->get_value('header', 'page-post-title', 'default', $current_post_id);
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

		if (!isset($pagenow) || !($pagenow == 'post-new.php' || $pagenow == 'post.php')) {
			return;
		}

		if (isset($_REQUEST['post-type']) && strtolower($_REQUEST['post_type']) != 'page') {
			return;
		}

		// only in post and page create and edit screen

		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('admin-script', trailingslashit($this->plugin_url) . 'assets/js/admin/admin.js', array('wp-color-picker', 'jquery'));

		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('admin-style', trailingslashit($this->plugin_url) . 'assets/css/admin/admin.css');
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
		$classes[] = 'sfx-page-customizer-active';

		return $classes;
	}

} // End Class
