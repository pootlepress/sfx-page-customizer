<?php
/**
 * Plugin Name: Storefront Extension - Page Customizer
 * Version: 1.0.0
 * Author: PootlePress
 *
 * Text Domain: sfx-page-customizer
 * Domain Path: /languages/
 *
 * @package SFX_Page_Customizer
 * @category Core
 * @author Alan
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/classes/class-pootlepress-updater.php');

add_action('init', 'pp_sfx_pc_updater');
function pp_sfx_pc_updater()
{
	if (!function_exists('get_plugin_data')) {
		include(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	$data = get_plugin_data(__FILE__);
	$wptuts_plugin_current_version = $data['Version'];
	$wptuts_plugin_remote_path = 'http://www.pootlepress.com/?updater=1';
	$wptuts_plugin_slug = plugin_basename(__FILE__);
	new Pootlepress_Updater ($wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug);
}


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
 * @author Alan
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
	// Admin - End

	// Post Types - Start
	/**
	 * The post types we're registering.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $post_types = array();
	// Post Types - End


	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->token 			= 'sfx-page-customizer';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0.0';

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action('init', array( $this, 'load_plugin_textdomain' ));

		add_action('admin_init', array($this, 'register_meta_box'));

		add_action('save_post', array($this, 'save_post'));

		add_action('admin_print_scripts', array($this, 'admin_scripts'));

		add_action('wp_head', array($this, 'option_css'));

		add_action('wp_head', array($this, 'option_js'));

		add_action('customize_register', array($this, 'customize_register'));

		// needs to be hooked to 'wp_loaded', since this is the action that when executed, will filter get_option from customizer
//		add_action('wp_loaded', array($this, 'get_options'), 100);

//		add_action('wp_loaded', array($this, 'align_menu_right'), 120);



//		add_action('customize_register', array($this, 'customize_register'));

	} // End __construct()

	public function customize_register(WP_Customize_Manager $customizeManager) {
		$customizeManager->add_setting('sfx-pc-show-page-post-title[checked]', array(
			'type' => 'option',
			'default' => true
		));

		$customizeManager->add_control(new WP_Customize_Control($customizeManager, 'sfx-pc-show-page-post-title', array(
			'type' => 'checkbox',
			'label' => 'Show page/post and product titles globally',
			'section' => 'storefront_layout',
			'settings' => 'sfx-pc-show-page-post-title[checked]',
			'default' => 1,
			'priority' => 1,
		)));
	}

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
		wp_enqueue_script('admin-script', trailingslashit(plugin_dir_url(__FILE__)) . 'js/admin.js', array('wp-color-picker', 'jquery'));

		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('admin-style', trailingslashit(plugin_dir_url(__FILE__)) . 'css/admin.css');
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
		if (!in_array($post->post_type, array('post', 'page', 'product'))) {
			return;
		}

//		$fields = $this->get_meta_fields();

		//$key = $this->get_meta_key($fields['header-background-color']['section'], 'header-background-color');
		 
		if (isset($_REQUEST[$this->token]) && is_array($_REQUEST[$this->token])) {
			$sfxPCValues = $_REQUEST[$this->token];

			if (isset($sfxPCValues['header']) && is_array($sfxPCValues['header'])) {
				if (isset($sfxPCValues['header']['header-background-color'])) {
					$value = $sfxPCValues['header']['header-background-color'];
					update_post_meta($postID, $this->get_meta_key('header', 'header-background-color'), $value);
				}

				if (isset($sfxPCValues['header']['header-background-image'])) {
					$value = $sfxPCValues['header']['header-background-image'];
					update_post_meta($postID, $this->get_meta_key('header', 'header-background-image'), $value);
				}

				if (isset($sfxPCValues['header']['page-post-title'])) {
					$value = $sfxPCValues['header']['page-post-title'];
					update_post_meta($postID, $this->get_meta_key('header', 'page-post-title'), $value);
				}
			}
		}
	}

	private function get_meta_fields() {
		global $post;
		
		//change label text according to page/post or product
		if (in_array($post->post_type, array('post', 'page'))) {
			$label_val = 'Page/post title';
		} elseif(in_array($post->post_type, array('product'))){
			$label_val = 'Product title';
		}
		
		
		return array(
			'page-post-title' => array(
				'id' => 'page-post-title',
				'section' => 'header',
				'label' => $label_val,
				'type' => 'select',
				'default' => 'default',
				'options' => array('default' => 'Global default', 'show' => 'Show', 'hide' => 'Hide')
			),
			'header-background-image' => array(
				'id' => 'header-background-image',
				'section' => 'header',
				'label' => 'Header background image',
				'type' => 'image',
				'default' => '',
			),
			'header-background-color' => array(
				'id' => 'header-background-color',
				'section' => 'header',
				'label' => 'Header background color',
				'type' => 'color',
				'default' => '',
			)
		);
	}

	public function meta_box() {

		$fields = $this->get_meta_fields();

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
	} // End render_field()

	/**
	 * Render HTML markup for the "text" field type.
	 * @access  protected
	 * @since   6.0.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_text ( $key, $args ) {
		$html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" size="40" type="text" value="' . esc_attr( $this->get_value( $args['id'], $args['default'], $args['section'] ) ) . '" />' . "\n";
		return $html;
	} // End render_field_text()

	/**
	 * Render HTML markup for the "radio" field type.
	 * @access  protected
	 * @since   6.0.0
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
	} // End render_field_radio()

	/**
	 * Render HTML markup for the "textarea" field type.
	 * @access  protected
	 * @since   6.0.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function render_field_textarea ( $key, $args ) {
		// Explore how best to escape this data, as esc_textarea() strips HTML tags, it seems.
		$html = '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" cols="42" rows="5">' . $this->get_value( $args['id'], $args['default'], $args['section'] ) . '</textarea>' . "\n";
		return $html;
	} // End render_field_textarea()

	/**
	 * Render HTML markup for the "checkbox" field type.
	 * @access  protected
	 * @since   6.0.0
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
	} // End render_field_checkbox()

	/**
	 * Render HTML markup for the "select2" field type.
	 * @access  protected
	 * @since   6.0.0
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
	} // End render_field_select()

	protected function render_field_color($key, $args) {
		$html = '';
		$html .= '<div class="field">';
		$html .= '<label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label>';
		$html .= '<div class="control"><input class="color-picker-hex" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="text" value="' . esc_attr( $this->get_value($args['section'], $args['id'], $args['default']) ) . '" /></div>';
		$html .= '</div>';
		return $html;
	}

	protected  function render_field_image($key, $args) {
		$html = '';
		$html .= '<div class="field">';
		$html .= '<label class="label" for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label>';
		$html .= '<div class="control"><input class="image-upload-path" type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($this->get_value($args['section'], $args['id'], $args['default'])) . '" /><button class="button upload-button">Upload</button></div>';
		$html .= '</div>';
		return $html;
	}

	public function option_js() {
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
	
	public function option_css() {
		// check is this is single post or page or product or shop
		if(!is_singular(array('post', 'page', 'product')) && !is_shop() && !is_home()) {
//			return;
		}

		global $post;

		$css = '';
		
		//Removing media query to make options work on all resolutions
		//$css .= "@media screen and (min-width: 768px) {\n";

		$showPagePostTitle = null;

		//Meta values for the page
		if(is_shop()){
			$current_post = get_option( 'woocommerce_shop_page_id' );
		}elseif(is_home()){
			$css .= "#main .hentry {margin: 0; padding-bottom: 0; border-bottom: none;}";
			$current_post = get_option( 'page_for_posts' );
		}else{
			$current_post = false;
		}
		$pagePostTitleMeta = $this->get_value('header', 'page-post-title', 'default', $current_post);
		$headerBgColor = $this->get_value('header', 'header-background-color', '');
		$headerBgImage = $this->get_value('header', 'header-background-image', '');

	
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
		elseif (in_array($post->post_type, array('post', 'page', 'product')) && !$showPagePostTitle){
			$css .= '.entry-title { display: none !important; }';
		}
		
		//Solving negative margin for product rating
		if (in_array($post->post_type, array('product')) && !$showPagePostTitle){
			$css .= '.single-product div.product .woocommerce-product-rating{margin-top:0;}';
		}

		if ($headerBgColor != '') {
			$css .= "#masthead { background: $headerBgColor !important; }\n";
		}
		if ($headerBgImage != '') {
			$css .= "#masthead { background: url('$headerBgImage') !important; }\n";
		}

		//Removing media query to make options work on all resolutions
		//$css .= "}\n";
		
		//Echoing the CSS
		echo "<style id='sfx-pc-styles'>\n";
		echo $css;
		echo "</style>\n";
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
	public static function instance () {
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
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'sfx-page-customizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	} // End load_plugin_textdomain()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __wakeup()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install()

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	} // End _log_version_number()
} // End Class
?>
