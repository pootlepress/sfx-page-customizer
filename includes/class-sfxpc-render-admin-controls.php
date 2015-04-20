<?php
/**
 * Contains class for rendering the admin fields
 *
 * @package SFX_Page_Customizer
 * @category Admin
 */

/**
 * Renders the fields in Post edit page and Taxonomy edit page
 *
 * @class SFXPC_render_fields
 * @version	1.0.0
 * @since 1.0.0
 * @package	SFX_Page_Customizer
 * @author PootlePress
 */
class SFXPC_Render_Fields extends SFXPC_Abstract{

	/**
	 * Rendrs the taxonomy Field
	 * 
	 * @param array $args Argument for field
	 * @param array|null $settings Current settings array
	 */
	protected function _get_current_value( $args, $settings ) {

		$current_val = $args['default'];
		//Getting current value
		if ( isset( $settings[ $args['section'] ][ $args['id'] ] ) ) {
			$current_val = $settings[ $args['section'] ][ $args['id'] ];
		}
		
		return $current_val;

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
	public function render_field ( $args, $output_format, $current_data ) {

		// Construct the key.
		$args['key'] = $this->token . '[' . $args['section'] . '][' . $args['id'] .']';

		//Setting blank css-class key if not set
		if ( ! isset( $args['css-class'] ) ) { $args['css-class'] = ''; }
		
		$args['css-class'] .= ' sfxpc-field '  . $args['id'];

		// Make sure we have some kind of default, if the key isn't set.
		if ( ! isset( $args['default'] ) ) { $args['default'] = ''; }

		$current_val = $this->_get_current_value( $args, $current_data );

		$this->_render_field_wrapper( $args, $output_format, $current_val );

	}

	/**
	 * Renders the field with HTML wrap
	 * 
	 * @param array $args Argument for field
	 * @param string $output_format = ( post || termEdit )
	 * @param string $current_val Current value of the field
	 */
	protected function _render_field_wrapper( $args, $output_format, $current_val ) {

		//Control/Label wrap start
		echo esc_attr( $output_format ) == 'termEdit' ?	
		  '<tr class="form-field ' . esc_attr( $args['css-class'] ) . '"><th scope="row">'
		:
		  '<div class="field ' . esc_attr( $args['css-class'] ) . '">';

		//Label
		echo '<label class="label" for="' . esc_attr( $args['key'] ) . '">' . esc_html( $args['label'] ) . '</label>';

		//Field wrap start
		echo esc_attr( $output_format ) == 'termEdit' ?	'</th><td>' : '<div class="control">';

		//The field
		$this->_output_rendered_field( $args, $current_val );

		//Label and field wrap close
		echo esc_attr( $output_format ) == 'termEdit' ?	'</td></tr>' : '</div></div>';

	}

	/**
	 * Outputs the field after rendering with HTML for the context
	 * 
	 * @param array $args Argument for field
	 * @param string $current_val Current value of the field
	 */
	protected function _output_rendered_field( $args, $current_val ) {

		//Getting the method for field
		$method = '_render_field_' . $args['type'];
		if ( ! method_exists( $this, $method ) ) {
			$method = '_render_field_text';
		}

		$allowed_attr = array(
			'name' => array(),
			'value' => array(),
			'type' => array(),
			'id' => array(),
			'class' => array(),
			'size' => array(),
			'for' => array(),
			'style' => array(),
			'checked' => array(),
			'for' => array(),
		);

		$allowed_tags = array( 
			'input', 
			'label', 
			'br', 
			'select', 
			'options', 
			'textarea', 
			'button',
		);

		$allowed = array();

		foreach ( $allowed_tags as $tag ) {
			$allowed[ $tag ] = $allowed_attr;
		}
		//Output the field
		echo wp_kses( $this->$method( $args['key'], $current_val, $args ), $allowed );

		// Output the description
		if ( isset( $args['description'] ) ) {
			echo '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>' . "\n";
		}

	}

	/**
	 * Render HTML markup for the "text" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   string $current_val The current value of the field
	 * @return  string       HTML markup for the field.
	 */
	protected function _render_field_text ( $key, $current_val ) {
		$html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" size="40" type="text" value="' . esc_attr( $current_val ) . '" />' . "\n";
		return $html;
	}

	/**
	 * Render HTML markup for the "radio" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   string $current_val The current value of the field
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function _render_field_radio ( $key, $current_val, $args ) {
		$html = '';
		if ( isset( $args['options'] ) && ( 0 < count( (array) $args['options'] ) ) ) {
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
	 * @param   string $current_val The current value of the field
	 * @return  string       HTML markup for the field.
	 */
	protected function _render_field_textarea ( $key, $current_val ) {
		$html = '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" cols="42" rows="5">' . $current_val . '</textarea>' . "\n";
		return $html;
	}

	/**
	 * Render HTML markup for the "checkbox" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   array $current_val Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function _render_field_checkbox ( $key, $current_val ) {
		$html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="checkbox" value="true" ' . checked( $current_val, 'true', false ) . ' />';
		return $html;
	}

	/**
	 * Render HTML markup for the "select" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   string $current_val The current value of the field
	 * @param   array $args  Arguments used to construct this field.
	 * @return  string       HTML markup for the field.
	 */
	protected function _render_field_select ( $key, $current_val, $args ) {
		$html = '';
		if ( isset( $args['options'] ) && ( 0 < count( (array) $args['options'] ) ) ) {
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
	 * @param   string $current_val The current value of the field
	 * @return  string       HTML markup for the field.
	 */
	protected function _render_field_color( $key, $current_val ) {
		$html = '<input class="color-picker-hex" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="text" value="' . esc_attr( $current_val ) . '" />';
		return $html;
	}

	/**
	 * Render HTML markup for the "image" field type.
	 * @access  protected
	 * @since   1.0
	 * @param   string $key  The unique ID of this field.
	 * @param   string $current_val The current value of the field
	 * @return  string       HTML markup for the field.
	 */
	protected function _render_field_image( $key, $current_val ) {
		$html = '<input class="image-upload-path" type="text" id="' . esc_attr( $key ) . '" style="width: 200px; max-width: 100%;" name="' . esc_attr($key) . '" value="' . esc_attr( $current_val ) . '" /><button class="button upload-button">Upload</button>';
		return $html;
	}


}