<?php

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Components\Title as Title;

class Title_Test extends Component_TestCase {

	public function testBuildingRemovesTags() {
		$body_component = new Title( 'Example Title', null, $this->settings,
			$this->styles, $this->layouts );

		$this->assertEquals(
			array(
				'role' => 'title',
				'text' => 'Example Title',
				'format' => 'html',
				'textStyle' => 'default-title',
				'layout' => 'title-layout',
		 	),
			$body_component->to_array()
		);
	}

	public function testFilter() {
		$body_component = new Title( 'Example Title', null, $this->settings,
			$this->styles, $this->layouts );

		add_filter( 'apple_news_title_json', function( $json ) {
			$json['textStyle'] = 'fancy-title';
			return $json;
		} );

		$this->assertEquals(
			array(
				'role' => 'title',
				'text' => 'Example Title',
				'format' => 'html',
				'textStyle' => 'fancy-title',
				'layout' => 'title-layout',
		 	),
			$body_component->to_array()
		);
	}

}

