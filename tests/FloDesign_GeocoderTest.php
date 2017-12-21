<?php

use FloDesign\Geocoder;

class FloDesign_GeocoderTest extends \WP_Mock\Tools\TestCase {
	
	protected $geocoder;
	
	public function setUp() {
		$this->geocoder = new Geocoder();
	}
	
	public function testFlodesign_calculate_distance() {
		
		$milesResult         = $this->geocoder->flodesign_calculate_distance( '33.8280741', '116.5069582', '36.9258221',
			'111.4490456' );
		$kilometersResult    = $this->geocoder->flodesign_calculate_distance( '33.8280741', '116.5069582', '36.9258221',
			'111.4490456', 'K' );
		$nauticalMilesResult = $this->geocoder->flodesign_calculate_distance( '33.8280741', '116.5069582', '36.9258221',
			'111.4490456', 'N' );
		
		$this->assertEquals( 309, round( $nauticalMilesResult ) );
		$this->assertEquals( 573, round( $kilometersResult ) );
		$this->assertEquals( 356, round( $milesResult ) );
		
	}
}
