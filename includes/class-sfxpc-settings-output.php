<?php
/**
 * Contains class making the settings work on frontend
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
class SFXPC_Settings_Output extends SFXPC_Abstract {

	/**
	 * Array of classes to be put in body
	 * @var array 
	 */
	public $body_classes = array();

	/**
	 * 
	 * @param array $settings
	 * @access  public
	 * @since   1.0.0
	 */
	public function css($settings){
		
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
		
		return $css;

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
	 * @param array $head_settings Header settings
	 * @return  string CSS for header
	 */
	public function header_styles( $head_settings ) {
		$css = '';

		$css .= $this->hide_title(! empty( $head_settings['hide-title']));


		if($head_settings['header-background-color']){
			$css .= "#masthead, .sub-menu , .site-header-cart .widget_shopping_cart { background: {$head_settings['header-background-color']} !important; }";
		}
		if($head_settings['header-background-image']){
			$css .= "#masthead { background-image: url('{$head_settings['header-background-image']}') !important; }\n";
		}
		if($head_settings['header-link-color']){
			$css .= ".main-navigation ul li a, .site-title a, ul.menu li:not(.current_page_item) a, .site-branding h1 a{ color: {$head_settings['header-link-color']} !important; }";
		}
		if($head_settings['header-text-color']){
			$css .= "p.site-description, ul.menu li.current-menu-item > a, .site-header-cart .widget_shopping_cart, .site-header .product_list_widget li .quantity{ color: {$head_settings['header-text-color']} !important; }";
		}

		return $css;
	}

	/**
	 * Removes hooked stuff.
	 * 
	 * @since   1.0.0
	 * @param array $head_settings Header settings
	 * @return  string CSS for header
	 */
	public function remove_hooked_in_header($head_settings){
		
		if( ! empty( $head_settings['hide-header'] ) ){
			remove_all_actions( 'storefront_header' );
			$css .= "#masthead { display:none !important; }\n";
		}
		if( ! empty( $head_settings['hide-primary-menu'] ) ){
			remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
		}
		if( ! empty( $head_settings['hide-secondary-menu'] ) ){
			remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
		}
		if( ! empty( $head_settings['hide-shop-cart'] ) ){
			remove_action( 'storefront_header', 'storefront_header_cart', 		60 );
		}
		if( ! empty( $head_settings['hide-breadcrumbs'] ) ){
			remove_action( 'storefront_content_top', 'woocommerce_breadcrumb', 					10 );
			$this->body_classes[] = 'no-wc-breadcrumb';
		}
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

}