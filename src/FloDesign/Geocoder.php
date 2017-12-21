<?php


namespace FloDesign;


class Geocoder {
	
	protected const API_KEY = "AIzaSyAD6jOAYqEqnZwpH2V-rQnr8mG9hVkUP_c";
	protected const GEOCODE_URL = "https://maps.googleapis.com/maps/api/geocode/outputFormat?";
	
	public function flodesign_geocode_location( string $location ) {
		
		$url = self::GEOCODE_URL . "address=" . $location . "&key=" . self::API_KEY;
		
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		
		$result = curl_exec( $curl );
		
		return $result;
	}
	
	public function flodesign_calculate_distance( $lat1, $lon1, $lat2, $lon2, $unit = 'M' ) {
		
		$theta = $lon1 - $lon2;
		$dist  = sin( deg2rad( $lat1 ) ) * sin( deg2rad( $lat2 ) ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * cos( deg2rad( $theta ) );
		$dist  = acos( $dist );
		$dist  = rad2deg( $dist );
		$miles = $dist * 60 * 1.1515;
		$unit  = strtoupper( $unit );
		
		if ( $unit == "K" ) {
			return ( $miles * 1.609344 );
		} else if ( $unit == "N" ) {
			return ( $miles * 0.8684 );
		} else {
			return $miles;
		}
	}
	
}