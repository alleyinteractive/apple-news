<?php

/**
 * Describes a WordPress setting section
 *
 * @since 0.6.0
 */
class Admin_Apple_Settings_Section_Formatting extends Admin_Apple_Settings_Section {

	/**
	 * Slug of the formatting settings section.
	 *
	 * @var string
	 * @access protected
	 */
	protected $slug = 'formatting-options';

	/**
	 * Constructor.
	 *
	 * @param string $page
	 */
	function __construct( $page ) {
		// Set the name
		$this->name =  __( 'Formatting', 'apple-news' );

		// Add the settings
		$this->settings = array(
			'layout_margin' => array(
				'label'   => __( 'Layout margin', 'apple-news' ),
				'type'    => 'integer',
			),
			'layout_gutter' => array(
				'label'   => __( 'Layout gutter', 'apple-news' ),
				'type'    => 'integer',
			),
			'body_font' => array(
				'label'   => '',
				'type'    => 'font',
			),
			'body_size' => array(
				'label'   => __( 'Body font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'body_color' => array(
				'label'   => __( 'Body font color', 'apple-news' ),
				'type'    => 'color',
			),
			'body_link_color' => array(
				'label'   => __( 'Body font hyperlink color', 'apple-news' ),
				'type'    => 'color',
			),
			'body_orientation' => array(
				'label'   => __( 'Body alignment', 'apple-news' ),
				'type'    => array( 'left', 'center', 'right' ),
			),
			'initial_dropcap' => array(
				'label'   => __( 'Use initial dropcap', 'apple-news' ),
				'type'    => array( 'yes', 'no' ),
			),
			'dropcap_font' => array(
				'label'   => '',
				'type'    => 'font',
			),
			'dropcap_color' => array(
				'label'   => __( 'Dropcap font color', 'apple-news' ),
				'type'    => 'color',
			),
			'byline_font' => array(
				'label'   => '',
				'type'    => 'font',
			),
			'byline_size' => array(
				'label'   => __( 'Byline font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'byline_color' => array(
				'label'   => __( 'Byline font color', 'apple-news' ),
				'type'    => 'color',
			),
			'byline_format' => array(
				'label'				=> __( 'Byline format', 'apple-news' ),
				'type' 				=> 'text',
				'description' => __( 'Set the byline format. Two tokens can be present, #author# to denote the location of the author name and a <a href="http://php.net/manual/en/function.date.php" target="blank">PHP date format</a> string also encapsulated by #. The default format is "by #author# | #M j, Y | g:i A#".', 'apple-news' ),
				'size'				=> 40,
				'required'		=> false,
			),
			'header_font' => array(
				'label'   => '',
				'type'    => 'font',
			),
			'header_color' => array(
				'label'   => __( 'Header font color', 'apple-news' ),
				'type'    => 'color',
			),
			'header1_size' => array(
				'label'   => __( 'Header 1 font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'header2_size' => array(
				'label'   => __( 'Header 2 font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'header3_size' => array(
				'label'   => __( 'Header 3 font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'header4_size' => array(
				'label'   => __( 'Header 4 font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'header5_size' => array(
				'label'   => __( 'Header 5 font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'header6_size' => array(
				'label'   => __( 'Header 6 font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'pullquote_font' => array(
				'label'   => '',
				'type'    => 'font',
			),
			'pullquote_size' => array(
				'label'   => __( 'Pull quote font size', 'apple-news' ),
				'type'    => 'integer',
			),
			'pullquote_color' => array(
				'label'   => __( 'Pull quote color', 'apple-news' ),
				'type'    => 'color',
			),
			'pullquote_transform' => array(
				'label'   => __( 'Pull quote transformation', 'apple-news' ),
				'type'    => array( 'none', 'uppercase' ),
			),
			'gallery_type' => array(
				'label'   => __( 'Gallery type', 'apple-news' ),
				'type'    => array( 'gallery', 'mosaic' ),
			),
			'enable_advertisement' => array(
				'label'   => __( 'Enable advertisement', 'apple-news' ),
				'type'    => array( 'yes', 'no' ),
			),
		);

		// Add the groups
		$this->groups = array(
			'layout' => array(
				'label'       => __( 'Layout Spacing', 'apple-news' ),
				'description' => __( 'The spacing for the base layout of the exported articles', 'apple-news' ),
				'settings'    => array( 'layout_margin', 'layout_gutter' ),
			),
			'body' => array(
				'label'       => __( 'Body', 'apple-news' ),
				'settings'    => array( 'body_font', 'body_size', 'body_color', 'body_link_color', 'body_orientation' ),
			),
			'dropcap' => array(
				'label'       => __( 'Dropcap', 'apple-news' ),
				'settings'    => array( 'dropcap_font', 'initial_dropcap', 'dropcap_color' ),
			),
			'byline' => array(
				'label'       => __( 'Byline', 'apple-news' ),
				'description' => __( "The byline displays the article's author and date", 'apple-news' ),
				'settings'    => array( 'byline_font', 'byline_size', 'byline_color', 'byline_format' ),
			),
			'headings' => array(
				'label'       => __( 'Headings', 'apple-news' ),
				'settings'    => array( 'header_font', 'header_color', 'header1_size',
				  'header2_size', 'header3_size', 'header4_size', 'header4_size',
				  'header5_size', 'header6_size' ),
			),
			'pullquote' => array(
				'label'       => __( 'Pull quote', 'apple-news' ),
				'description' => sprintf(
					'%s <a href="https://en.wikipedia.org/wiki/Pull_quote">%s</a>.',
					__( 'Articles can have an optional', 'apple-news' ),
					__( 'Pull quote', 'apple-news' )
				),
				'settings'    => array( 'pullquote_font', 'pullquote_size', 'pullquote_color', 'pullquote_transform' ),
			),
			'gallery' => array(
				'label'       => __( 'Gallery', 'apple-news' ),
				'description' => __( 'Can either be a standard gallery, or mosaic.', 'apple-news' ),
				'settings'    => array( 'gallery_type' ),
			),
			'advertisement' => array(
				'label'       => __( 'Advertisement', 'apple-news' ),
				'settings'    => array( 'enable_advertisement' ),
			),
		);

		parent::__construct( $page );
	}

	/**
	 * Gets section info.
	 *
	 * @return string
	 * @access public
	 */
	public function get_section_info() {
		return __( 'Configuration on the look and feel of the generated articles', 'apple-news' );
	}

}
