<?php
/**
 * Contains sfxpc abstract class
 *
 * @package SFX_Page_Customizer
 * @category Admin
 */

/**
 * For all SFXPC classes to extend from
 *
 * @class SFXPC_abstract
 * @version	1.0.0
 * @since 1.0.0
 * @package	SFX_Page_Customizer
 * @author PootlePress
 */
abstract class SFXPC_Abstract {

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
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct( $token , $version) {
		$this->token 			= $token;
		$this->version 			= $version;

		if ( method_exists( $this, 'init' ) ) {
			$this->init(func_get_args());
		}
	}
}
