<?php

use FloDesign\AdviserSearch;
use FloDesign\Geocoder;

class FloDesign_AdviserSearchTest extends \WP_Mock\Tools\TestCase {
	
	protected $instance;
	protected $geocoder;
	protected $location_results;
	
	public function setUp() {
		WP_Mock::setUp();
		$this->geocoder = Mockery::mock( Geocoder::class );
		$this->instance = new AdviserSearch( $this->geocoder );
	}
	
	public
	function tearDown() {
		unset( $_POST );
		Mockery::close();
		WP_Mock::tearDown();
	}
	
	public
	function testHooksAreAddedOnPluginInitialisation() {
		WP_Mock::expectActionAdded( 'init', [ $this->instance, 'init' ], 20 );
		WP_Mock::expectActionAdded( 'admin_post_adviser_search', [ $this->instance, 'flodesign_adviser_search' ], 99 );
		WP_Mock::expectActionAdded( 'admin_post_nopriv_adviser_search', [ $this->instance, 'flodesign_adviser_search' ],
			99 );
		
		$this->instance->init();
		
		$this->assertHooksAdded();
	}
	
	public
	function testFindAdviserByLocationReturnsAListOrderedByDistanceBasedOnTheInputLocation() {
		$_POST["location"] = "Aberdeen";
		
		$posts        = [];
		$post1        = new stdClass();
		$post1->title = "Test Title1";
		$post1->lat   = 51.429355;
		$post1->lng   = - 2.307129;
		$posts[]      = $post1;
		$post2        = new stdClass();
		$post2->title = "Test Title1";
		$post2->lat   = 57.305164;
		$post2->lng   = - 3.142090;
		$posts[]      = $post2;
		
		$this->geocoder->shouldReceive( 'flodesign_geocode_location' )
		               ->once()
		               ->andReturn( [ "lat" => - 53.621186, "lng" => - 2.900879 ] );
		
		$this->geocoder->shouldReceive( 'flodesign_calculate_distance' )
		               ->andReturn( 20, 10 );
		
		$query = Mockery::mock( 'overload:\WP_Query' );
		$query->shouldReceive( 'get_posts' )
		      ->once()
		      ->with( [
			      'post_type'   => "adviser",
			      'numberposts' => - 1
		      ] )
		      ->andReturn( $posts );
		
		$instance = new AdviserSearch( $this->geocoder );
		
		$result = $instance->flodesign_adviser_search();
		
		$this->assertInstanceOf( "stdClass", $result[0] );
		$this->assertGreaterThan( $result[0]->distance, $result[1]->distance );
	}
	
	public function testThrowExceptionIfLocationOrNameNotProvided() {
		$this->expectExceptionMessage( "Location or adviser name required" );
		$this->expectException( InvalidArgumentException::class );
		$this->instance->flodesign_adviser_search();
	}
	
	public function testShouldReturnResultsBasedOnTaxonomyTermAndInputSpecialism() {
		$_POST["location"]    = "Aberdeen";
		$_POST['specialisms'] = [ "investments" ];
		
		$posts        = [];
		$post1        = new stdClass();
		$post1->title = "Test Title1";
		$post1->lat   = 51.429355;
		$post1->lng   = - 2.307129;
		$posts[]      = $post1;
		$post2        = new stdClass();
		$post2->title = "Test Title1";
		$post2->lat   = 57.305164;
		$post2->lng   = - 3.142090;
		$posts[]      = $post2;
		
		$this->geocoder->shouldReceive( 'flodesign_geocode_location' )
		               ->once()
		               ->andReturn( [ "lat" => - 53.621186, "lng" => - 2.900879 ] );
		
		$this->geocoder->shouldReceive( 'flodesign_calculate_distance' )
		               ->andReturn( 20, 10 );
		
		$query = Mockery::mock( 'overload:\WP_Query' );
		$query->shouldReceive( 'get_posts' )
		      ->once()
		      ->with( [
			      'post_type'   => "adviser",
			      'numberposts' => - 1,
			      'tax_query'   => [
				      [
					      'taxonomy' => "expertise",
					      "field"    => "slug",
					      "terms"    => array_map( function ( $term ) {
						      return strtolower( $term );
					      }, $_POST['specialisms'] )
				      ]
			      ]
		      ] )
		      ->andReturn( $posts );
		
		$instance = new AdviserSearch( $this->geocoder );
		
		$result = $instance->flodesign_adviser_search();
		
		$this->assertInstanceOf( "stdClass", $result[0] );
		$this->assertGreaterThan( $result[0]->distance, $result[1]->distance );
	}
}
