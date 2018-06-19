<?php

use Apple_Exporter\Exporter as Exporter;
use Prophecy\Argument;

class Exporter_Test extends WP_UnitTestCase {

	private $prophet;

	public function setup() {
		$this->prophet = new \Prophecy\Prophet;
	}

	public function tearDown() {
		$this->prophet->checkPredictions();
	}

	public function testExport() {
		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		// Cleans up workspace
		$workspace
			->clean_up()
			->shouldBeCalled();

		// Writes JSON
		$workspace
			->write_json( Argument::that( array( $this, 'isValidJSON' ) ) )
			->shouldBeCalled();

		// Get JSON
		$workspace
			->get_json()
			->shouldBeCalled();

		$content  = new Apple_Exporter\Exporter_Content( 3, 'Title', '<p>Example content</p>' );
		$exporter = new Exporter( $content, $workspace->reveal() );
		$exporter->export();
	}

	public function isValidJSON( $json ) {
		return ( null !== json_decode( $json ) );
	}

	public function testBuildersGetCalled() {
		$workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );
		// Cleans up workspace
		$workspace
			->clean_up()
			->shouldBeCalled();

		// Writes JSON
		$workspace
			->write_json( Argument::that( array( $this, 'isValidJSON' ) ) )
			->shouldBeCalled();

		// Get JSON
		$workspace
			->get_json()
			->shouldBeCalled();

		$builder1 = $this->prophet->prophesize( '\Apple_Exporter\Builders\Builder' );
		$builder1
			->to_array()
			->shouldBeCalled();
		$builder2 = $this->prophet->prophesize( '\Apple_Exporter\Builders\Builder' );
		$builder2
			->to_array()
			->shouldBeCalled();
		$builder3 = $this->prophet->prophesize( '\Apple_Exporter\Builders\Builder' );
		$builder3
			->to_array()
			->shouldBeCalled();

		$content  = new Apple_Exporter\Exporter_Content( 3, 'Title', '<p>Example content</p>' );
		$exporter = new Exporter( $content, $workspace->reveal() );
		$exporter->initialize_builders( array(
			'componentTextStyles' => $builder1->reveal(),
			'componentLayouts'    => $builder2->reveal(),
			'componentStyles'     => $builder3->reveal(),
		) );
		$exporter->export();
	}

}

