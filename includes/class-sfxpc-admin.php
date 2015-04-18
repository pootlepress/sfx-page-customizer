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
 * @class SFXPC_Settings_Output
 * @version	1.0.0
 * @since 1.0.0
 * @package	SFX_Page_Customizer
 * @author PootlePress
 */
class SFXPC_Admin extends SFXPC_Abstract{

	/**
	 * The plugin directory URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $plugin_url;

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
	public $admin_fields = array();

	/**
	 * Called by Parent::__consruct
	 * Initiates class variables
	 * 
	 * @access  public
	 * @since   1.0.0
	 */
	public function init( $args ){

		//Basic Setup
		$this->plugin_url 		= $args[2];

		//Renderer
		$this->renderer = new SFXPC_Render_Fields( $this->token, $this->version );

		//Supported Post Types and Taxonomies
		$this->get_supported_post_types();
		$this->get_supported_taxonomies();
		
		$this->get_admin_fields();
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

	/**
	 * Register the post meta box
	 * 
	 * @access  public
	 * @since   1.0.0
	 */
	public function register_meta_box() {
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this post', array($this, 'custom_fields'), 'post' );
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this page', array($this, 'custom_fields'), 'page' );
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this product', array($this, 'custom_fields'), 'product' );
	}

	/**
	 * Returns all fields data
	 * @return array Meta fields
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function get_admin_fields() {

		global $SFX_Page_Customizer_fields;
		$this->admin_fields = $SFX_Page_Customizer_fields;

	}
	
	/**
	 * PostMeta Callback
	 * 
	 * @param postObject $post
	 */
	public function custom_fields( $post ) {
		$fields = $this->admin_fields;
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
				$this->renderer->render_field($fld, 'post', $postMetaValues);
			}
			echo "</div>";
		}
		
		echo "</div>";
	}

	/**
	 * Hooked to Terms edit screen
	 * 
	 * @param object $term
	 */
	public function tax_custom_fields($term) {

		$tax_sfxpc_data = get_option( $this->token. '-cat' . $term->term_id );

		$fields = $this->admin_fields;

		$taxonomy = get_taxonomy($term->taxonomy);
		echo '<h2>Customize Storefront options for this ' . $taxonomy->labels->singular_name . ' archive</h2>';

		//Nonce
		wp_nonce_field( 'sfx-pc-tax-meta', 'sfx-pc-nonce' );

		echo '<table class="form-table">';
		foreach ($fields as $key => $field) {
			$this->renderer->render_field($field, 'termEdit', $tax_sfxpc_data);
		}
		echo '</table>';
	}

	/**
	 * Hooked to save_post
	 * @param object $postID
	 * @return null|void
	 */
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

}
