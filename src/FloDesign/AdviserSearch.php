<?php

namespace FloDesign;

class AdviserSearch {
	
	private $geocoder;
	
	public function __construct( Geocoder $geocoder ) {
		$this->geocoder = $geocoder;
	}
	
	public function init() {
		add_action( 'init', [ $this, 'init' ], 20 );
		add_action( "admin_post_adviser_search", [ $this, "flodesign_adviser_search" ], 99 );
		add_action( "admin_post_nopriv_adviser_search", [ $this, "flodesign_adviser_search" ], 99 );
	}
	
	public function flodesign_adviser_search() {
		if ( ! isset( $_POST['location'] ) ) {
			throw new \InvalidArgumentException( "Location or adviser name required" );
		}
		
		if ( isset( $_POST['specialisms'] ) ) {
			$result = $this->flodesign_find_adviser_by_location( $_POST['location'], $_POST['specialisms'] );
		} else {
			$result = $this->flodesign_find_adviser_by_location( $_POST['location'] );
		}
		
		return $result;
	}
	
	
	/**
	 * Find adviser by location
	 *
	 * Function will find advisers ordered by distance from the input location.
	 *
	 * @param string $location User input of location
	 *
	 * @param array|null $specialisms Array of specialism terms from the search form
	 *
	 * @return array $results
	 */
	public function flodesign_find_adviser_by_location( $location, $specialisms = null ) {
		$query         = new \WP_Query();
		$codedLocation = $this->geocoder->flodesign_geocode_location( $location );
		
		if ( $specialisms !== null ) {
			$posts = $query->get_posts( [
				"post_type"   => "adviser",
				"numberposts" => - 1,
				"tax_query"   => [
					[
						'taxonomy' => "expertise",
						"field"    => "slug",
						"terms"    => array_map( function ( $term ) {
							return strtolower( $term );
						}, $specialisms )
					]
				]
			] );
		} else {
			$posts = $query->get_posts( [
				"post_type"   => "adviser",
				"numberposts" => - 1
			] );
		}
		
		foreach ( $posts as $post ) {
			//Determine distance from geocoded Lat/Lng
			$post->distance = $this->geocoder->flodesign_calculate_distance(
				$codedLocation['lat'],
				$codedLocation['lng'],
				$post->lat,
				$post->lng );
		}
		
		// Sort posts by distance
		usort( $posts, function ( $first, $second ) {
			if ( $first->distance == $second->distance ) {
				return 0;
			}
			
			return ( $first->distance < $second->distance ) ? - 1 : 1;
		} );
		
		return $posts;
	}
}
