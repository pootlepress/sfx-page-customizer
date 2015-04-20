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
 * @class SFXPC_Admin
 * @version	1.0.0
 * @since 1.0.0
 * @package	SFX_Page_Customizer
 * @author PootlePress
 */
class SFXPC_Admin extends SFXPC_Abstract{

	/**
	 * All the post metas to populate.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $admin_fields = array();

	/**
	 * Holds instance of SFXPC_Render_Controls
	 * @var     object SFXPC_Render_Controls
	 * @access  public
	 * @since   1.0.0
	 */
	public $renderer;

	/**
	 * Called by Parent::__consruct
	 * Initiates class variables
	 * 
	 * @param array $args Arguments to the parent __construct method
	 * @access  public
	 * @since   1.0.0
	 */
	public function init( $args ) {

		parent::init( $args );

		//Renderer
		$this->renderer = new SFXPC_Render_Controls( $this->token, $this->version );

		$this->get_admin_fields();
	}

	/**
	 * Verify SFXPC Nonce.
	 * @access  private
	 * @since   1.0.0
	 * @param string $action
	 * @return bool Nonce verified
	 */
	private function _verify_nonce( $action ) {

		$nonce = filter_input( INPUT_POST, 'sfx-pc-nonce' );

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Gets postdata.
	 * @access  private
	 * @since   1.0.0
	 * @return array|bool Sanitized postdata
	 */
	private function _get_posted_data( ) {

		$post_ = filter_input_array( INPUT_POST );

		if( ! empty( $post_[ $this->token ] ) ){

			return $post_[ $this->token ];
			
		}

	}

	/**
	 * Register the post meta box
	 * 
	 * @access  public
	 * @since   1.0.0
	 */
	public function register_meta_box() {
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this post', array( $this, 'custom_fields' ), 'post' );
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this page', array( $this, 'custom_fields' ), 'page' );
		add_meta_box( 'sfx-pc-meta-box', 'Customize Storefront options for this product', array( $this, 'custom_fields' ), 'product' );
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
		$postMetaValues = get_post_meta( $post->ID , $this->token , true );
		
		echo "<div class='" . esc_attr( $class ) . "'>" ;

		//WP Nonce
		wp_nonce_field( 'sfx-pc-post-meta', 'sfx-pc-nonce' );
		
		$field_structure = array();
		foreach ( $fields as $key => $field ) {
			$field_structure[ $field['section'] ][] = $field;
		}

		echo "<ul class='sfxpc-sections-nav nav-tab-wrapper'>";
		foreach ( $field_structure as $sec => $fields ) {
			$Sec = ucwords( $sec );
			
			echo "<li> <a href='#sfxpc-section-" . esc_attr( $sec ) . "'> " . esc_html__( $Sec ) . ' </a> </li>';
		}

		echo '</ul>';
		foreach ( $field_structure as $sec => $fields ) {
			echo "<div class='sfxpc-section' id='sfxpc-section-" . esc_attr( $sec ) . "'>";
			foreach ( $fields as $fld ) {
				$this->renderer->render_field( $fld, 'post', $postMetaValues );
			}
			echo '</div>';
		}
		
		echo '</div>';
	}

	/**
	 * Hooked to Terms edit screen
	 * 
	 * @param object $term
	 */
	public function tax_custom_fields( $term ) {

		$tax_sfxpc_data = get_option( $this->token. '-cat' . $term->term_id );

		$fields = $this->admin_fields;

		$taxonomy = get_taxonomy( $term->taxonomy );
		echo '<h2>Customize Storefront options for this ' . esc_html( $taxonomy->labels->singular_name ) . ' archive</h2>';

		//Nonce
		wp_nonce_field( 'sfx-pc-tax-meta', 'sfx-pc-nonce' );

		echo '<table class="form-table">';
		foreach ( $fields as $key => $field ) {
			$this->renderer->render_field( $field, 'termEdit', $tax_sfxpc_data );
		}
		echo '</table>';
	}

	public function save_term_fields( $ID ) {

		$term_data = $this->_get_posted_data();

		if ( ! $term_data OR ! $this->_verify_nonce( 'sfx-pc-tax-meta' ) ) { return; }

		if ( is_array( $term_data ) ) {

			$option_name = $this->token.'-cat'.$ID;

			update_option( $option_name, $term_data );
		}
	}

	/**
	 * Hooked to save_post
	 * @param object $postID
	 * @return null|void
	 */
	public function save_post( $postID ) {

		$post_data = $this->_get_posted_data();

		if ( ! $post_data OR ! $this->_verify_nonce( 'sfx-pc-post-meta' ) ) { return; }

		if ( is_array( $post_data ) ) {

			update_post_meta( $postID, $this->token, $post_data );

		}
		
	}

	/**
	 * Enqueue Js 
	 * @global type $pagenow
	 * @return null
	 */
	public function admin_scripts() {
		global $pagenow;

		if ( 'edit-tags.php' == $pagenow ) {
			//Though everything is commented this if section is still important coz it prevents returning the function
			wp_enqueue_media();
		} elseif (
		  ( ! isset( $pagenow ) OR ! ( 'post-new.php' == $pagenow OR 'post.php' == $pagenow ) )
		) { 
			return;
		}

		// only in post and page create and edit screen

		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'sfxpc-admin-script', trailingslashit( $this->plugin_url ) . 'assets/js/admin/admin.js', array( 'wp-color-picker', 'jquery', 'thickbox' ) );

		wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'sfxpc-admin-style', trailingslashit( $this->plugin_url ) . 'assets/css/admin/admin.css' );
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
	public function sfxpc_admin_body_class( $classes ) {
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
				echo '<div class="updated">' . esc_html( $notice ) . '</div>';
			}

			delete_option( 'sfxpc_activation_notice' );
		}
	}

}
