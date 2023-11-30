<?php
/**
 * Apple News Includes: Meta Helpers
 *
 * Contains functions for working with meta.
 *
 * @package Apple_News
 */

/**
 * Prepares a meta value, stored as an array, as JSON to be returned in the REST response.
 *
 * @param mixed $value The value to transform.
 *
 * @return false|string False on failure, JSON string on success.
 */
function apple_news_json_encode( $value ) {
	return wp_json_encode( $value );
}

/**
 * Register meta for posts or terms with sensible defaults and sanitization.
 *
 * @throws InvalidArgumentException For unmet requirements.
 *
 * @see register_post_meta
 * @see register_term_meta
 *
 * @param string $object_type  The type of meta to register, which must be one of 'post' or 'term'.
 * @param array  $object_slugs The post type or taxonomy slugs to register with.
 * @param string $meta_key     The meta key to register.
 * @param array  $args         Optional. Additional arguments for register_post_meta or register_term_meta. Defaults to an empty array.
 * @return bool True if the meta key was successfully registered in the global array, false if not.
 */
function apple_news_register_meta_helper( $object_type, $object_slugs, $meta_key, $args = [] ) {

	// Object type must be either post or term.
	if ( ! in_array( $object_type, [ 'post', 'term' ], true ) ) {
		throw new InvalidArgumentException(
			esc_html__(
				'Object type must be one of "post", "term".',
				'apple-news'
			)
		);
	}

	// Merge provided arguments with defaults.
	$args = wp_parse_args(
		$args,
		[
			'sanitize_callback' => 'apple_news_sanitize_meta_by_type',
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'string',
		]
	);

	// Fork for object type.
	switch ( $object_type ) {
		case 'post':
			foreach ( $object_slugs as $object_slug ) {
				if ( ! register_post_meta( $object_slug, $meta_key, $args ) ) {
					return false;
				}
			}
			break;
		case 'term':
			foreach ( $object_slugs as $object_slug ) {
				if ( ! register_term_meta( $object_slug, $meta_key, $args ) ) {
					return false;
				}
			}
			break;
		default:
			return false;
	}

	return true;
}

/**
 * A 'sanitize_callback' for a registered meta key that sanitizes based on type.
 *
 * @param mixed  $meta_value     Meta value to sanitize.
 * @param string $meta_key       Meta key.
 * @param string $object_type    Object type.
 * @param string $object_subtype Optional. Object subtype. Defaults to empty.
 * @return mixed Sanitized meta value.
 */
function apple_news_sanitize_meta_by_type( $meta_value, $meta_key, $object_type, $object_subtype ) {

	// Ensure the meta key is registered.
	$registered = get_registered_meta_keys( $object_type, $object_subtype );
	if ( empty( $registered[ $meta_key ] ) ) {
		return $meta_value;
	}

	// Ensure a type is set.
	$args = $registered[ $meta_key ];
	if ( empty( $args['type'] ) ) {
		return $meta_value;
	}

	// Sanitize by type.
	switch ( $args['type'] ) {
		case 'boolean':
			return rest_sanitize_boolean( $meta_value );
		case 'integer':
			return (int) $meta_value;
		case 'number':
			return (float) $meta_value;
		case 'string':
			return sanitize_text_field( $meta_value );
		default:
			return $meta_value;
	}
}

/**
 * A 'sanitize_callback' for the apple_news_sections meta field.
 *
 * @param mixed $meta_value Meta value to sanitize.
 * @return array Sanitized meta value.
 */
function apple_news_sanitize_selected_sections( $meta_value ) {
	if ( ! is_string( $meta_value ) ) {
		return $meta_value;
	}

	// The meta value should be a stringified JSON array. Ensure that it is.
	$raw_meta_value = json_decode( $meta_value, true );
	if ( ! is_array( $raw_meta_value ) ) {
		return [];
	}

	// Rebuild the data, sanitizing values, and validating keys.
	return array_map( 'sanitize_text_field', $raw_meta_value );
}
