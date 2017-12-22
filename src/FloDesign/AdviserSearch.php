<?php

namespace FloDesign;

class AdviserSearch {
	
	private $geocoder;
	
	public function __construct( Geocoder $geocoder ) {
		$this->geocoder = $geocoder;
	}
	
	public function init() {
		add_action( 'init', [ $this, 'init' ], 20 );
		add_action( "pre_get_posts", [ $this, "flodesign_adviser_search" ], 99 );
		add_filter( "posts_results", [ $this, "flodesign_sort_advisers_by_distance" ] );
	}
	
	public function flodesign_adviser_search( $query ) {
		if ( is_admin() || ! is_post_type_archive( 'advisers' ) ) {
			return;
		}
		
		if ( isset( $_POST['specialisms'] ) ) {
			$this->flodesign_find_adviser_by_location( $_POST['specialisms'], $query );
		} else {
			$this->flodesign_find_adviser_by_location( $query );
		}
	}
	
	
	/**
	 * Find adviser by location
	 *
	 * Function will find advisers ordered by distance from the input location.
	 *
	 * @param array|null $specialisms Array of specialism terms from the search form
	 *
	 * @param $query
	 *
	 * @return void $results
	 */
	public function flodesign_find_adviser_by_location( $specialisms = null, $query ) {
		
		if ( $specialisms !== null ) {
			$query->set( [
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
			$query->set( [
				"post_type"   => "adviser",
				"numberposts" => - 1
			] );
		}
		
	}
	
	public function flodesign_sort_advisers_by_distance( $posts ) {
		if ( ! is_admin() && is_post_type_archive( 'advisers' ) && isset( $_POST['location'] ) ) {
			$codedLocation = $this->geocoder->flodesign_geocode_location( $_POST['location'] );
			
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
}
