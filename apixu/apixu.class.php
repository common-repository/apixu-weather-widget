<?php

class apixuWeather
{
	const API_RORECAST = 'https://api.apixu.com/v1/forecast.json';
	const API_CURRENT = 'https://api.apixu.com/v1/current.json';
	const API_SEARCH = 'https://api.apixu.com/v1/search.json';
	private static $key = "";

	function __construct() {
	}

	public static function get_current_weather( $key = '', $city = 'minsk', $country = '' ) {
		$key = (!$key) ? self::$key : $key;
		$city = urlencode($city);
		$country = ($country) ? "&country=" . urlencode($country) : '';
		$url = self::API_CURRENT . "?key={$key}&q={$city}{$country}&=";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$json_output = curl_exec($ch);

		return json_decode($json_output);
	}

	public static function search( $key = '', $city = 'minsk' ) {
		
		$key = (!$key) ? self::$key : $key;
		$url = self::API_SEARCH . "?key={$key}&q={$city}";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$json_output = curl_exec($ch);

		return $json_output;
	}

	public static function get_forecast_weather( $key = '', $city = 'minsk', $country = '', $forcast_days = '3' ) {
		$key = (!$key) ? self::$key : $key;
		$city = urlencode($city);
		$country = ($country) ? "&country=" . urlencode($country) : '';
		$url = self::API_RORECAST . "?key={$key}&q={$city}{$country}&days={$forcast_days}&=\"";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$json_output = curl_exec($ch);

		return json_decode($json_output);
	}
}