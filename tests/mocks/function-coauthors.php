<?php
/**
 * Apple News Tests Mocks: coauthors function
 *
 * @package Apple_News
 * @subpackage Tests
 */

/* phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.WP.I18n.TextDomainMismatch, WordPress.WP.I18n.MissingTranslatorsComment */

/**
 * A mock for the coauthors function from Co-Authors Plus.
 *
 * @param string $between     Delimiter that should appear between the co-authors.
 * @param string $between_last Delimiter that should appear between the last two co-authors.
 * @param string $before      What should appear before the presentation of co-authors.
 * @param string $after       What should appear after the presentation of co-authors.
 * @param bool   $echo        Whether the co-authors should be echoed or returned. Defaults to true.
 */
function coauthors( $between = ', ', $between_last = ' and ', $before = '', $after = '', $echo = true ) {
	// To use this function, put display names in a global array called $apple_news_coauthors.
	global $apple_news_coauthors;

	// Bail if we don't have coauthors.
	if ( empty( $apple_news_coauthors ) ) {
		return '';
	}

	// Get last index.
	$last_index = count( $apple_news_coauthors ) - 1;

	// Default was not set when running unit tests,
	// causing failures.
	if ( empty( $between ) ) {
		$between = ', ';
	}

	// Compute output.
	$output = $before
		. implode( $between, array_slice( $apple_news_coauthors, 0, $last_index ) )
		. ( 0 !== $last_index ? $between_last : '' )
		. $apple_news_coauthors[ $last_index ]
		. $after;

	// Fork for echo.
	if ( ! $echo ) {
		return $output;
	}

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * A mock for the coauthors_posts_links function from Co-Authors Plus.
 *
 * @param string $between      Delimiter that should appear between the co-authors.
 * @param string $between_last Delimiter that should appear between the last two co-authors.
 * @param string $before       What should appear before the presentation of co-authors.
 * @param string $after        What should appear after the presentation of co-authors.
 * @param bool   $echo         Whether the co-authors should be echoed or returned. Defaults to true.
 */
function coauthors_posts_links( $between = null, $between_last = null, $before = null, $after = null, $echo = true ) {
	// To use this function, put display names in a global array called $apple_news_coauthors.
	global $apple_news_coauthors;

	// Bail if we don't have coauthors.
	if ( empty( $apple_news_coauthors ) ) {
		return '';
	}

	$output = [];

	foreach ( $apple_news_coauthors as $author ) {
		// Get author data.
		$author = get_user_by( 'id', $author );

		$args = [
			'before_html' => '',
			'href'        => get_author_posts_url( $author->ID, $author->user_nicename ),
			'rel'         => 'author',
			'title'       => sprintf( __( 'Posts by %s', 'co-authors-plus' ), $author->display_name ),
			'class'       => 'author url fn',
			'text'        => $author->display_name,
			'after_html'  => '',
		];

		$single_link = sprintf(
			'<a href="%1$s" rel="%2$s">%3$s</a>',
			esc_url( $args['href'] ),
			esc_attr( $args['rel'] ),
			esc_html( $args['text'] )
		);

		$output[] = $args['before_html'] . $single_link . $args['after_html'];

	}

	// Get last element and prepend 'and'.
	$last_element = array_pop( $output );
	array_push( $output, 'and ' . $last_element );

	// If we have more than two items comma-separate array items and then conver to string.
	$output = implode( 2 > count( $output ) ? ', ' : ' ', $output );

	// Fork for echo.
	if ( ! $echo ) {
		return $output;
	}

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * A mock for the coauthors_posts_links_single function from Co-Authors Plus.
 *
 * @param object $author The author object to use in output.
 * @return string
 */
function coauthors_posts_links_single( $author ) {
	// Return if the fields we are trying to use are not sent.
	if ( ! isset( $author->ID, $author->user_nicename, $author->display_name ) ) {
		_doing_it_wrong(
			'coauthors_posts_links_single',
			'Invalid author object used',
			'3.2'
		);
		return;
	}
	$args        = [
		'before_html' => '',
		'href'        => get_author_posts_url( $author->ID, $author->user_nicename ),
		'rel'         => 'author',
		'title'       => sprintf( __( 'Posts by %s', 'co-authors-plus' ), apply_filters( 'the_author', $author->display_name ) ),
		'class'       => 'author url fn',
		'text'        => apply_filters( 'the_author', $author->display_name ),
		'after_html'  => '',
	];
	$args        = apply_filters( 'coauthors_posts_link', $args, $author );
	$single_link = sprintf(
		'<a href="%1$s" title="%2$s" class="%3$s" rel="%4$s">%5$s</a>',
		esc_url( $args['href'] ),
		esc_attr( $args['title'] ),
		esc_attr( $args['class'] ),
		esc_attr( $args['rel'] ),
		esc_html( $args['text'] )
	);
	return $args['before_html'] . $single_link . $args['after_html'];
}
