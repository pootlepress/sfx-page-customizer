<?php
/**
* Plugin Name: Storefront wc page customizer
* Plugin URI: http://github.com/storefront-wc-page-customizer/
* Description: A Storefront settings for product pages.
* Author: WooThemes
* Version: 1.0
* Author URI:http://www.pottlepress.com
*
* Text Domain: storefront-wc-page-customizer
* Domain Path: /languages/
*
* @package Storefront-wc-page-customizer
* @author Magnon.
*/

/**
 * Adds a meta box to the product editing screen
 */
function prfx_custom_meta() {
	add_meta_box( 'prfx_meta', __( 'Storefront settings', 'prfx-textdomain' ), 'prfx_meta_callback', 'product' );
}
add_action( 'add_meta_boxes', 'prfx_custom_meta' );

/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
	$prfx_stored_meta = get_post_meta( $post->ID );
	?>

	<p>
		<label for="meta-select" class="prfx-row-title"><?php _e( 'Post/Page Title', 'prfx-textdomain' )?></label>
		<select name="meta-select" id="meta-select">
			<option value="none" <?php if ( isset ( $prfx_stored_meta['meta-select'] ) ) selected( $prfx_stored_meta['meta-select'][0], 'none' ); ?>><?php _e( 'Global Default', 'prfx-textdomain' )?></option>';
			
			<option value="select-one" <?php if ( isset ( $prfx_stored_meta['meta-select'] ) ) selected( $prfx_stored_meta['meta-select'][0], 'select-one' ); ?>><?php _e( 'one', 'prfx-textdomain' )?></option>';
			<option value="select-two" <?php if ( isset ( $prfx_stored_meta['meta-select'] ) ) selected( $prfx_stored_meta['meta-select'][0], 'select-two' ); ?>><?php _e( 'Two', 'prfx-textdomain' )?></option>';
		</select>
	</p>

	<p>
		<label for="meta-color" class="prfx-row-title"><?php _e( 'Header background color', 'prfx-textdomain' )?></label>
		<input name="meta-color" type="text" value="<?php if ( isset ( $prfx_stored_meta['meta-color'] ) ) echo $prfx_stored_meta['meta-color'][0]; ?>" class="meta-color" />
	</p>

	<p>
		<label for="meta-image" class="prfx-row-title"><?php _e( 'Header Background Image', 'prfx-textdomain' )?></label>
		<input type="text" name="meta-image" id="meta-image" value="<?php if ( isset ( $prfx_stored_meta['meta-image'] ) ) echo $prfx_stored_meta['meta-image'][0]; ?>" />
		
		<input type="button" id="meta-image-button" class="button" value="<?php _e( 'Upload', 'prfx-textdomain' )?>" />
	</p>
 

	<?php
}

/**
 * Saves the custom meta input
 */
function prfx_meta_save( $post_id ) {
 
	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}
 	
	// Checks for input and saves if needed
	if( isset( $_POST[ 'meta-select' ] ) ) {
		update_post_meta( $post_id, 'meta-select', $_POST[ 'meta-select' ] );
	}


	// Checks for input and saves if needed
	if( isset( $_POST[ 'meta-color' ] ) ) {
		update_post_meta( $post_id, 'meta-color', $_POST[ 'meta-color' ] );
	}

	// Checks for input and saves if needed
	if( isset( $_POST[ 'meta-image' ] ) ) {
		update_post_meta( $post_id, 'meta-image', $_POST[ 'meta-image' ] );
	}

}
add_action( 'save_post', 'prfx_meta_save' );


/**
 * Adds the meta box stylesheet when appropriate
 */
 
function prfx_admin_styles(){
	global $typenow;
	if( $typenow == 'product' ) {
		wp_enqueue_style( 'prfx_meta_box_styles', plugin_dir_url( __FILE__ ) . '/css/meta-box-styles.css' );
	}
}
add_action( 'admin_print_styles', 'prfx_admin_styles' );

/**
 * Loads the color picker javascript
 */
function prfx_color_enqueue() {
	global $typenow;
	if( $typenow == 'product' ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'meta-box-color-js', plugin_dir_url( __FILE__ ) . '/js/meta-box-color.js', array( 'wp-color-picker' ) );
	}
}
add_action( 'admin_enqueue_scripts', 'prfx_color_enqueue' );

/**
 * Loads the image management javascript
 */
function prfx_image_enqueue() {
	global $typenow;
	if( $typenow == 'product' ) {
		wp_enqueue_media();
	
		// Registers and enqueues the required javascript.
		wp_register_script( 'meta-box-image', plugin_dir_url( __FILE__ ) . '/js/meta-box-image.js', array( 'jquery' ) );
		wp_localize_script( 'meta-box-image', 'meta_image',
			array(
				'title' => __( 'Upload Image', 'prfx-textdomain' ),
				'button' => __( 'Use this image', 'prfx-textdomain' ),
			)
		);
		wp_enqueue_script( 'meta-box-image' );
	}
}
add_action( 'admin_enqueue_scripts', 'prfx_image_enqueue' );
