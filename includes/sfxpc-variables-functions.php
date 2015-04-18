<?php
/**
 * Contains functions and variables for SFX Page Customizer
 *
 * @package SFX_Page_Customizer
 * @category Admin
 */

/**
 * Array of fields used in Post Metabox and Taxonomies
 * @var array All the fields used in SFXPC
 */
$SFX_Page_Customizer_fields = array(
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
		'default' => '',
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
		'default' => '',
	),
	'header-text-color' => array(
		'id' => 'header-text-color',
		'section' => 'header',
		'label' => 'Header text color',
		'type' => 'color',
		'default' => '',
	),
	'header-link-color' => array(
		'id' => 'header-link-color',
		'section' => 'header',
		'label' => 'Header link color',
		'type' => 'color',
		'default' => '',
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