<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Component_Spec class
 *
 * Defines a JSON spec for a component.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 1.2.4
 */

namespace Apple_Exporter;

/**
 * A class that defines a JSON spec for a component.
 *
 * @since 1.2.4
 */
class Component_Spec {

	/**
	 * The component for this spec.
	 *
	 * @access public
	 * @var string
	 */
	public $component;

	/**
	 * The name for this spec.
	 *
	 * @access public
	 * @var string
	 */
	public $name;

	/**
	 * The label for this spec.
	 *
	 * @access public
	 * @var string
	 */
	public $label;

	/**
	 * The spec.
	 *
	 * @access public
	 * @var array
	 */
	public $spec;

	/**
	 * Initializes the object with the name, label and the spec.
	 *
	 * @param string $component The component name.
	 * @param string $name The spec name.
	 * @param string $label The human-readable label for the spec.
	 * @param array $spec The spec definition.
	 *
	 * @access public
	 */
	public function __construct( $component, $name, $label, $spec ) {
		$this->component = $component;
		$this->name = $name;
		$this->label = $label;
		$this->spec = $spec;
	}

	/**
	 * Using the provided spec and array of values, build the component's JSON.
	 *
	 * @param array $values Values to substitute into the spec.
	 * @param int $post_id Optional. The post ID to pull postmeta for.
	 *
	 * @access public
	 * @return array The component JSON with placeholders in the spec replaced.
	 */
	public function substitute_values( $values, $post_id = 0 ) {
		return $this->value_iterator( $this->get_spec(), $values, $post_id );
	}

	/**
	 * Substitute values recursively for a given spec.
	 *
	 * @param array $spec The spec to use as a template.
	 * @param array $values Values to substitute in the spec.
	 * @param int $post_id Optional. Post ID to pull postmeta for.
	 *
	 * @access public
	 * @return array The spec with placeholders replaced by values.
	 */
	public function value_iterator( $spec, $values, $post_id = 0 ) {

		// Go through this level of the iterator.
		foreach ( $spec as $key => $value ) {

			// If the current element has children, call this recursively.
			if ( is_array( $value ) ) {

				// Call this function recursively to handle the substitution on this child array.
				$spec[ $key ] = $this->value_iterator( $spec[ $key ], $values, $post_id );
			} elseif ( ! is_array( $value ) && $this->is_token( $value ) ) {

				// Fork for postmeta vs. standard tokens.
				if ( 0 === strpos( $value, '#postmeta.' ) ) {
					$meta_key = substr( $value, strlen( '#postmeta.' ), -1 );
					$meta_value = get_post_meta( $post_id, $meta_key, true );
					$value = ( ! empty( $meta_value ) ) ? $meta_value : '';
				} elseif ( ! empty( $values[ $value ] ) ) {
					$value = $values[ $value ];
				}

				// Fork for setting the spec or unsetting based on valid values.
				if ( ! empty( $value ) ) {
					$spec[ $key ] = $value;
				} else {
					unset( $spec[ $key ] );
				}
			}
		}

		return $spec;
	}

	/**
	 * Validate the provided spec against the built-in spec.
	 *
	 * @param array $spec The spec to validate.
	 *
	 * @access public
	 * @return boolean True if validation was successful, false otherwise.
	 */
	public function validate( $spec ) {

		// Iterate recursively over the built-in spec and get all the tokens.
		// Do the same for the provided spec.
		$new_tokens = $default_tokens = array();
		$this->find_tokens( $spec, $new_tokens );
		$this->find_tokens( $this->spec, $default_tokens );

		// Removing tokens is fine, but new tokens cannot be added, except for postmeta.
		foreach ( $new_tokens as $token ) {

			// If the new token references postmeta, allow it.
			if ( 0 === strpos( $token, '#postmeta.' ) ) {
				continue;
			}

			// Check for standard tokens.
			if ( ! in_array( $token, $default_tokens, true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Recursively find tokens in the spec.
	 *
	 * @param array $spec The spec to iterate over to look for tokens.
	 * @param array $tokens A list of found tokens.
	 *
	 * @access public
	 */
	public function find_tokens( $spec, &$tokens ) {

		// Find all tokens in the spec.
		foreach ( $spec as $key => $value ) {

			// If the current element has children, call this recursively.
			if ( is_array( $value ) ) {
				$this->find_tokens( $spec[ $key ], $tokens );
			} elseif ( ! is_array( $value ) && $this->is_token( $value ) ) {
				$tokens[] = $value;
			}
		}
	}

	/**
	 * Save the provided spec override.
	 *
	 * @param array $spec The spec definition to save.
	 * @param string $theme Optional. Theme name to save to if other than default.
	 *
	 * @access public
	 * @return boolean True on success, false on failure.
	 */
	public function save( $spec, $theme = '' ) {

		// Negotiate the theme name.
		$themes = new \Admin_Apple_Themes();
		if ( empty( $theme ) ) {
			$theme = $themes->get_active_theme();
		}

		// Validate the JSON.
		$json = json_decode( $spec, true );
		if ( empty( $json ) ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'The spec for %s was invalid and cannot be saved', 'apple-news' ),
				$this->label
			) );

			return false;
		}

		// Compare this JSON to the built-in JSON.
		// If they are the same, there is no reason to save this.
		$custom_json = $this->format_json( $json );
		$default_json = $this->format_json( $this->spec );
		if ( $custom_json === $default_json ) {
			// Delete the spec in case we've reverted back to default.
			// No need to keep it in storage.
			return $this->delete( $theme );
		}

		// Validate the JSON.
		$result = $this->validate( $json );
		if ( false === $result ) {
			\Admin_Apple_Notice::error( sprintf(
				__(
					'The spec for %s had invalid tokens and cannot be saved',
					'apple-news'
				),
				$this->label
			) );

			return $result;
		}

		// If we've gotten to this point, save the JSON.
		$component_key = $this->key_from_name( $this->component );
		$spec_key = $this->key_from_name( $this->name );
		$theme_settings = $themes->get_theme( $theme );
		// TODO: REFACTOR THIS
		if ( ! is_array( $theme_settings['json_templates'] ) ) {
			$theme_settings['json_templates'] = array();
		}
		$theme_settings['json_templates'][ $component_key ][ $spec_key ] = $json;
		$themes->save_theme( $theme, $theme_settings, true );

		// Indicate success.
		return true;
	}

	/**
	 * Delete the current spec override.
	 *
	 * @param string $theme Optional. Theme to delete from if not the default.
	 *
	 * @access public
	 * @return bool True on success, false on failure.
	 */
	public function delete( $theme = '' ) {

		// Negotiate theme name.
		$themes = new \Admin_Apple_Themes();
		if ( empty( $theme ) ) {
			$theme = $themes->get_active_theme();
		}

		// Compute component and spec keys.
		$component_key = $this->key_from_name( $this->component );
		$spec_key = $this->key_from_name( $this->name );

		// Determine if this spec override is defined in the theme.
		$theme_settings = $themes->get_theme( $theme );
		if ( ! isset( $theme_settings['json_templates'][ $component_key ][ $spec_key ] ) ) {
			return false;
		}

		// Remove this spec from the theme.
		unset( $theme_settings['json_templates'][ $component_key ][ $spec_key ] );

		// If there are no more overrides for this component, remove it.
		if ( empty( $theme_settings['json_templates'][ $component_key ] ) ) {
			unset( $theme_settings['json_templates'][ $component_key ] );

			// If there are no more JSON templates, remove the block.
			if ( empty( $theme_settings['json_templates'] ) ) {
				unset( $theme_settings['json_templates'] );
			}
		}

		// Update the theme.
		$themes->save_theme( $theme, $theme_settings, true );

		return true;
	}

	/**
	 * Get the spec for this component as JSON.
	 *
	 * @param string $theme Optional. A theme other than the default to load from.
	 *
	 * @access public
	 * @return array The configuration for the spec.
	 */
	public function get_spec( $theme = '' ) {

		// Negotiate theme name.
		if ( empty( $theme ) ) {
			$themes = new \Admin_Apple_Themes();
			$theme = $themes->get_active_theme();
		}

		// Determine if this spec in the specified theme is overridden.
		$override = $this->get_override( $theme );
		if ( ! empty( $override ) ) {
			return $override;
		}

		return $this->spec;
	}

	/**
	 * Get the spec for this component as JSON.
	 *
	 * @param string $spec
	 * @return string
	 * @access public
	 */
	public function format_json( $spec ) {
		return wp_json_encode( $spec, JSON_PRETTY_PRINT );
	}

	/**
	 * Get the override for this component spec.
	 *
	 * @param string $theme Optional. Theme name to load from if not default.
	 *
	 * @access public
	 * @return array|null An array of values if an override is present, else null.
	 */
	public function get_override( $theme = '' ) {

		// Negotiate theme name.
		$themes = new \Admin_Apple_Themes();
		if ( empty( $theme ) ) {
			$theme = $themes->get_active_theme();
		}

		// Get the configuration from the theme.
		$theme_options = $themes->get_theme( $theme );

		// Determine if there is an override in the theme.
		$component = $this->key_from_name( $this->component );
		$spec = $this->key_from_name( $this->name );
		if ( ! empty( $theme_options['json_templates'][ $component ][ $spec ] ) ) {
			return $theme_options['json_templates'][ $component ][ $spec ];
		}

		return null;
	}

	/**
	 * Determines whether or not the spec value is a token.
	 *
	 * @param string $value The value to check against the token format.
	 *
	 * @access public
	 * @return boolean True if the value is a token, false otherwise.
	 */
	public function is_token( $value ) {
		return ( 1 === preg_match( '/#[^#]+#/', $value ) );
	}

	/**
	 * Generates a key for the JSON from the provided component or spec.
	 *
	 * @param string $name The name to turn into a key.
	 *
	 * @access public
	 * @return string The name converted into a key.
	 */
	public function key_from_name( $name ) {
		return sanitize_key( $name );
	}
}
