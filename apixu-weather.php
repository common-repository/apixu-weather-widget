<?php
/*
Plugin Name: Apixu Weather Widget
Description: A weather widget that actually looks cool
Author: apixu
Author URI: https://profiles.wordpress.org/apixu/
Version: 1.3.1
Text Domain: apixu-weather
Domain Path: /languages

// CLEAR OUT THE TRANSIENT CACHE
add to your URL 'clear_apixu_widget'
For example: http://url.com/?clear_apixu_widget

*/

// INCLUDE Apixu class
require_once(dirname(__FILE__) . "/apixu/apixu.class.php");

// SETTINGS
$apixu_weather_sizes = apply_filters('apixu_weather_sizes', array('tall', 'wide'));

// SETUP
function apixu_weather_setup() {
	load_plugin_textdomain('apixu-weather', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	add_action('admin_menu', 'apixu_weather_setting_page_menu');
}

add_action('plugins_loaded', 'apixu_weather_setup', 99999);

// ENQUEUE CSS
function apixu_weather_wp_head( $posts ) {
	wp_enqueue_style('apixu-weather', plugins_url('/apixu-weather.css', __FILE__));

	$use_google_font = apply_filters('apixu_weather_use_google_font', true);
	$google_font_queuename = apply_filters('apixu_weather_google_font_queue_name', 'opensans-googlefont');

	if ($use_google_font) {
		wp_enqueue_style($google_font_queuename, 'https://fonts.googleapis.com/css?family=Open+Sans:400,300');
		wp_add_inline_style('apixu-weather', ".apixu-weather-wrap { font-family: 'Open Sans', sans-serif;  font-weight: 300; font-size: 16px; line-height: 14px; } ");
	}
}

add_action('wp_enqueue_scripts', 'apixu_weather_wp_head');

//THE SHORTCODE
add_shortcode('apixu-weather', 'apixu_weather_shortcode');
function apixu_weather_shortcode( $atts ) {
    /*return "<p>Hello!</p>";*/
	return apixu_weather_logic($atts);
}

// THE LOGIC
function apixu_weather_logic( $atts ) {
	global $apixu_weather_sizes;
	$rtn = "";
	$weather_data = array();
	$location = isset($atts['location']) ? $atts['location'] : false;
	$owm_city_id = isset($atts['owm_city_id']) ? $atts['owm_city_id'] : false;
	$size = (isset($atts['size']) AND $atts['size'] == "tall") ? 'tall' : 'wide';
	$units = (isset($atts['units']) AND strtoupper($atts['units']) == "C") ? "metric" : "imperial";
	$units_display = $units == "metric" ? __('C', 'apixu-weather') : __('F', 'apixu-weather');
	$days_to_show = isset($atts['forecast_days']) ? $atts['forecast_days'] : 5;
	$show_stats = (isset($atts['show_stats']) AND $atts['show_stats'] == 1) ? 1 : 0;
	//$show_link = (isset($atts['show_link']) AND $atts['show_link'] == 1) ? 1 : 0;
	$show_link = 1;
	$inline_style = isset($atts['inline_style']) ? $atts['inline_style'] : '';
	$widget_scheme = isset($atts['scheme']) ? $atts['scheme'] : 1;
	$location_case = isset($atts['location_case']) ? esc_attr($atts['location_case']) : 3;
	$locale = 'en';
	$sytem_locale = get_locale();
	$available_locales = apply_filters('apixu_weather_available_locales', array('en'));
	$custom_bg_color = ($atts['custom_bg_color']) ? $atts['custom_bg_color'] : "#f2f2f2";
	$text_color = ($atts['text_color']) ? $atts['text_color'] : "#20a5d6";


	// CHECK FOR LOCALE
	if (in_array($sytem_locale, $available_locales))
		$locale = $sytem_locale;

	// CHECK FOR LOCALE BY FIRST TWO DIGITS
	if (in_array(substr($sytem_locale, 0, 2), $available_locales))
		$locale = substr($sytem_locale, 0, 2);

	// OVERRIDE LOCALE PARAMETER
	if (isset($atts['locale']))
		$locale = $atts['locale'];

	// DISPLAY SYMBOL
	$units_display_symbol = apply_filters('apixu_weather_units_display', "&deg;");
	if (isset($atts['units_display_symbol']))
		$units_display_symbol = $atts['units_display_symbol'];

	// NO LOCATION, ABORT ABORT!!!!
	if (!$location)
		return apixu_weather_error();

	//FIND AND CACHE CITY ID
	if ($owm_city_id) {
		$city_name_slug = sanitize_title($location);
	} else if (is_numeric($location)) {
		$city_name_slug = sanitize_title($location);
	} else {
		$city_name_slug = sanitize_title($location);
	}

	$location_parts = explode(',',trim($location));
	$location_city = $location_parts[0];
	$location_country=trim(end($location_parts));
	
	// TRANSIENT NAME
	$weather_transient_name = 'awe_' . $city_name_slug . "_" . $days_to_show . "_" . strtolower($units) . '_' . $locale;

	// CLEAR THE TRANSIENT
	if (isset($_GET['clear_apixu_widget']))
		delete_transient($weather_transient_name);

	// GET KEY
	$key = apply_filters('apixu_weather_appid', awe_get_appid());

	// GET WEATHER DATA
	if (get_transient($weather_transient_name)) {
		$weather_data = get_transient($weather_transient_name);
	} else {
		$weather_data['now'] = array();
		$weather_data['forecast'] = array();
		$weather_data['location'] = array();
		if ($days_to_show != "hide") {
			// FORECAST
			$weather_apixu = apixuWeather::get_forecast_weather($key, $location_city, $location_country, $days_to_show + 1);
			$weather_data['now'] = $weather_apixu->current;
			$weather_data['forecast'] = $weather_apixu->forecast;
		} else {
			// NOW
			$weather_apixu = apixuWeather::get_current_weather($key, $location_city, $location_country);
			$weather_data['now'] = $weather_apixu->current;
		}
		$weather_data['location'] = $weather_apixu->location;

		if ($weather_data['now'] OR $weather_data['forecast']) {
			set_transient($weather_transient_name, $weather_data, apply_filters('apixu_weather_cache', 1800));
		}
	}

	// NO WEATHER
	if (!$weather_data OR !isset($weather_data['now']))
		return apixu_weather_error();

	// TODAYS TEMPS
	$today = $weather_data['now'];
	$today_temp = ($units == "imperial") ? (int)$today->temp_f : (int)$today->temp_c;
	$location = $weather_data['location'];

	// BACKGROUND DATA, CLASSES AND OR IMAGES
	$background_classes = array();
	$background_classes[] = "apixu-weather-wrap";
	$background_classes[] = "awecf";
	$background_classes[] = "awe_" . $size;

	// DATA
	//$header_title = $override_title ? $override_title : $today->name;

	// WIND
	$wind_direction = false;
	if (isset($today->wind_dir))
		$wind_direction = apply_filters('apixu_weather_wind_direction', __($today->wind_dir, 'apixu-weather'));

	$background_classes[] = ($show_stats) ? "awe_with_stats" : "awe_without_stats";

	// ADD WEATHER CONDITIONS CLASSES TO WRAP
	if (isset($today->condition)) {
		$weather_code = $today->condition->code;
		$weather_descr = explode(' ', strtolower(trim($today->condition->text)));
		$weather_description_slug = sanitize_title($weather_descr[ count($weather_descr) - 1 ]);
		$background_classes[] = "awe-code-" . $weather_code;
		$background_classes[] = "awe-desc-" . $weather_description_slug;
	}

	// EXTRA STYLES
	$background_class_string = @implode(" ", apply_filters('apixu_weather_background_classes', $background_classes));
	$today_sign = ($today_temp > 0) ? '+' : '';

	if (in_array($widget_scheme, array(41, 42, 43, 44, 45))){
		$widget_scheme_group = 'shm_4';
	} elseif (in_array($widget_scheme, array(71, 72, 73))) {
		$widget_scheme_group = 'shm_7';
	} elseif (in_array($widget_scheme, array(81, 82, 83))) {
		$widget_scheme_group = 'shm_8';
	} elseif (in_array($widget_scheme, array(91))) {
		$widget_scheme_group = 'shm_9';
	}

	if (in_array($widget_scheme, array(1,81))){
		$inline_style = "color:{$text_color};background:{$custom_bg_color}";
	}

	if ($inline_style){
		$inline_style = "style='{$inline_style}'";
	}

	$background_class_string .= ' ' . $widget_scheme_group;
	// DISPLAY WIDGET	
	$rtn .= "<div id=\"apixu-weather-{$city_name_slug}\" $inline_style class=\"{$background_class_string} scheme_{$widget_scheme}\">";

	$rtn .= "<div class=\"apixu-weather-cover\">";

	// Picture
	if (isset($today->condition->icon)) {
		$rtn1 = '<div class="apixu-weather-todays-stats-big-pict">';
		$folder = 'large';
		$pict_apend = 'lg';
		$pict = explode('/', $today->condition->icon);
		$day_case = $pict[ count($pict) - 2 ];
		$pict_code = (int)$pict[ count($pict) - 1 ];
		$path_apixu_pict = plugin_dir_url(__FILE__) . "img/{$folder}/{$pict_code}_{$day_case}_{$pict_apend}.png";
		$rtn1 .= "<img src=$path_apixu_pict>";
		$rtn1 .= '</div><!-- /.apixu-weather-todays-stats -->';
	}
	// Weather stat
	if ($widget_scheme_group !== 'shm_8') {
		$rtn1 .= '<div class="apixu_descr">' . $today->condition->text . '</div>';
	}

	//location
	$location_cases = array(
		1 => $location->name . ', ' . $location->region . ', ' . $location->country,
		2 => $location->name . ', ' . $location->region,
		3 => $location->name . ', ' . $location->country,
		4 => $location->name
	);
	$rtn2 = '<div class="apixu-weather-todays-stats-location">';
	if ($widget_scheme_group == 'shm_8') {
		$rtn2 .= '<div class="awe_desc">' . $location_cases[$location_case];
		$rtn2 .= '<div class="apixu_desc">' . $today->condition->text . '</div>';
		$rtn2 .= '</div>';
	} else {
		$rtn2 .= '<div class="awe_desc">' . $location_cases[$location_case] . '</div>';
	}
	$rtn2 .= '</div><!-- location -->';

	if ($widget_scheme_group == 'shm_7') {
		$rtn .= $rtn2 . $rtn1;
	} else {
		$rtn .= $rtn1 . $rtn2;
	}

	//current weather
	$rtn .= "<div class=\"apixu-weather-current-temp\"><span>{$today_sign}{$today_temp}{$units_display_symbol}{$units_display}</span></div><!-- /.apixu-weather-current-temp -->";

	//show stats
	if ($show_stats AND isset($weather_data['now'])) {
		$wind_speed = isset($today->wind_kph) ? $today->wind_kph / 3.6 : false;
		$feelslike = ($units == "imperial") ? (int)$today->feelslike_f : (int)$today->feelslike_c;
		$wind_speed_text = ($units == "imperial") ? __('mph', 'apixu-weather') : __('m/s', 'apixu-weather');
		$wind_speed_obj = apply_filters('apixu_weather_wind_speed', array(
			'text'      => apply_filters('apixu_weather_wind_speed_text', $wind_speed_text),
			'speed'     => round($wind_speed),
			'direction' => $wind_direction), $wind_speed, $wind_direction);

		// CURRENT WEATHER STATS
		$rtn .= '<div class="apixu-weather-todays-stats">';
		if (isset($today->condition->text))
			$rtn .= '<div class="awe_desc">' . __('Feels like:', 'apixu-weather') . ' ' .$today_sign.$feelslike.$units_display_symbol.$units_display  . ' </div>';
		if (isset($today->humidity))
			$rtn .= '<div class="awe_humidty">' . __('humidity:', 'apixu-weather') . " " . $today->humidity . '%</div>';
		if ($wind_speed AND $wind_direction)
			$rtn .= '<div class="awe_wind">' . __('wind:', 'apixu-weather') . ' ' . $wind_speed_obj['speed'] . $wind_speed_obj['text'] . ' ' . $wind_speed_obj['direction'] . '</div>';
		$rtn .= '</div><!-- /.apixu-weather-todays-stats -->';
	}

	//show forecast
	if ($days_to_show != "hide") {
		$rtn .= "<div class=\"apixu-weather-forecast awe_days_{$days_to_show} awecf\">";
		$c = 1;
		$dt_today = date('Ymd', current_time('timestamp', 0));
		$forecast = $weather_data['forecast'];
		$days_to_show = (int)$days_to_show;
		$days_of_week = apply_filters('apixu_weather_days_of_week', array(__('Sun', 'apixu-weather'), __('Mon', 'apixu-weather'), __('Tue', 'apixu-weather'), __('Wed', 'apixu-weather'), __('Thu', 'apixu-weather'), __('Fri', 'apixu-weather'), __('Sat', 'apixu-weather')));
		foreach ((array)$forecast->forecastday as $forecast) {
			if ($dt_today >= date('Ymd', $forecast->date_epoch)) {
				$c = 1;
				continue;
			}
			$forecast->temp = ($units == "imperial") ? (int)$forecast->day->avgtemp_f : (int)$forecast->day->avgtemp_c;
			$day_of_week = $days_of_week[ date('w', $forecast->date_epoch) ];

			if (isset($forecast->day->condition->icon)) {
				$rtn_ = '<div class="">';
				$folder = 'small';
				$pict_apend = 'sm';
				$pict = explode('/', $forecast->day->condition->icon);
				$day_case = $pict[ count($pict) - 2 ];
				$pict_code = (int)$pict[ count($pict) - 1 ];
				$path_apixu_pict_sm = plugin_dir_url(__FILE__) . "img/{$folder}/{$pict_code}_{$day_case}_{$pict_apend}.png";
				$rtn_ .= "<img src=$path_apixu_pict_sm>";
				$rtn_ .= '</div><!-- /.apixu-weather-todays-stats -->';
			}

			$rtn .= "
				<div class=\"apixu-weather-forecast-day\">
					<div class=\"apixu - weather - forecast - day - abbr\">{$day_of_week}</div>
					<div class=\"apixu-weather-forecast-day-abbr\">{$rtn_}</div>
					<div class=\"apixu - weather - forecast - day - temp\">{$today_sign}{$forecast->temp}<span>{$units_display_symbol}{$units_display}</span></div>
				</div>";
			if ($c == $days_to_show) {
				break;
			}
			$c++;
		}
		$rtn .= "</div><!-- /.apixu-weather-forecast -->";
	}

	if ($show_link) {
		$show_link_text = apply_filters('apixu_weather_extended_forecast_text', __('Apixu.com', 'apixu-weather'));
		$rtn .= "<div class=\"apixu-weather-more-weather-link\">";
		$rtn .= "<a href=\"https://www.apixu.com/\" target=\"_blank\" title=\"Free weather API\">{$show_link_text}</a>";
		$rtn .= "</div>";
	}

	$rtn .= "</div><!-- /.apixu-weather-cover -->";
	$rtn .= "</div> <!-- /.apixu-weather-wrap -->";

	return $rtn;
}

// RETURN ERROR
function apixu_weather_error( $msg = false ) {
	$error_handling = get_option('aw-error-handling');
	if (!$error_handling)
		$error_handling = "source";
	if (!$msg)
		$msg = __('No weather information available', 'apixu-weather');

	if ($error_handling == "display-admin") {
		// DISPLAY ADMIN
		if (current_user_can('manage_options')) {
			return "<div class='apixu-weather-error'>" . $msg . "</div>";
		}
	} else if ($error_handling == "display-all") {
		// DISPLAY ALL
		return "<div class='apixu-weather-error'>" . $msg . "</div>";
	} else {
		return apply_filters('apixu_weather_error', "<!-- APIXU WEATHER ERROR: " . $msg . " -->");
	}
}

// ENQUEUE ADMIN SCRIPTS
function apixu_weather_admin_scripts( $hook ) {
	if ('widgets.php' != $hook)
		return;
	wp_enqueue_style('jquery');
	wp_enqueue_style('underscore');
	wp_enqueue_style('wp-color-picker');
	wp_enqueue_script('wp-color-picker');
	wp_enqueue_script('apixu_weather_admin_script', plugin_dir_url(__FILE__) . '/apixu-weather-widget-admin.js', array('jquery', 'underscore'));
	wp_localize_script('apixu_weather_admin_script', 'awe_script', array(
			'no_owm_city'    => esc_attr(__("No city found in Apixu.", 'apixu-weather')),
			'one_city_found' => esc_attr(__('Only one location found. The ID has been set automatically above.', 'apixu-weather')),
			'confirm_city'   => esc_attr(__('Please confirm your city: &nbsp;', 'apixu-weather')),
		)
	);
}

add_action('admin_enqueue_scripts', 'apixu_weather_admin_scripts');

// GET APPID
function awe_get_appid() {
	return defined('APIXU_WEATHER_APPID') ? APIXU_WEATHER_APPID : get_option('open-weather-key');
}

// PING OPENWEATHER FOR OWMID
add_action('wp_ajax_awe_ping_owm_for_id', 'awe_ping_owm_for_id');
function awe_ping_owm_for_id() {
	$key = awe_get_appid();
	$location = urlencode($_GET['location']);
	$weather_apixu = apixuWeather::search($key, $location);
	header("Content-Type: application/json");
	echo $weather_apixu;
	die;
}

// WIDGET
require_once(dirname(__FILE__) . "/widget.php");

// SETTINGS
require_once(dirname(__FILE__) . "/apixu-weather-settings.php");