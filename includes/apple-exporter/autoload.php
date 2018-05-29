<?php
/**
 * Publish to Apple News: Apple Exporter Autoloader
 *
 * @package Apple_News
 */

spl_autoload_register(
	function ( $class ) {
		$path = strtolower( $class );
		$path = str_replace( '_', '-', $path );
		$path = explode( '\\', $path );
		$file = array_pop( $path );
		$path = implode( '/', $path ) . '/class-' . $file . '.php';
		$path = realpath( __DIR__ . '/../' . $path );

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);
