<?php
// CREATE THE SETTINGS PAGE
function apixu_weather_setting_page_menu() {
	add_options_page('Apixu Weather ', 'Apixu Weather', 'manage_options', 'apixu-weather', 'apixu_weather_page');
}

function apixu_weather_page() {
	?>
	<div class="wrap">
		<h2><?php _e('Apixu Weather Widget', 'apixu-weather'); ?></h2>

		<?php if (isset($_GET['apixu-weather-cached-cleared'])) { ?>
			<div id="setting-error-settings_updated" class="updated settings-error">
				<p><strong><?php _e('Weather Widget Cache Cleared', 'apixu-weather'); ?></strong></p>
			</div>
		<?php } ?>

		<form action="options.php" method="POST">
			<?php settings_fields('awe-basic-settings-group'); ?>
			<?php do_settings_sections('apixu-weather'); ?>
			<?php submit_button(); ?>
		</form>
		<hr>
		<p>
			<a href="options-general.php?page=apixu-weather&action=apixu-weather-clear-transients" class="button"><?php _e('Clear all Apixu Weather Widget Cache', 'apixu-weather'); ?></a>
		</p>
	</div>
	<?php
}

// SET SETTINGS LINK ON PLUGIN PAGE
function apixu_weather_plugin_action_links( $links, $file ) {
	$appid = apply_filters('apixu_weather_appid', awe_get_appid());

	if ($appid) {
		$settings_link = '<a href="' . admin_url('options-general.php?page=apixu-weather') . '">' . esc_html__('Settings', 'apixu-weather') . '</a>';
	} else {
		$settings_link = '<a href="' . admin_url('options-general.php?page=apixu-weather') . '">' . esc_html__('API Key Required', 'apixu-weather') . '</a>';
	}

	if ($file == 'apixu-weather/apixu-weather.php')
		array_unshift($links, $settings_link);

	return $links;
}

add_filter('plugin_action_links', 'apixu_weather_plugin_action_links', 10, 2);
add_action('admin_init', 'apixu_weather_setting_init');
function apixu_weather_setting_init() {
	register_setting('awe-basic-settings-group', 'open-weather-key');
	register_setting('awe-basic-settings-group', 'aw-error-handling');

	add_settings_section('awe-basic-settings', '', 'apixu_weather_api_keys_description', 'apixu-weather');
	add_settings_field('open-weather-key', __('ApixuWeather App Key', 'apixu-weather'), 'apixu_weather_openweather_key', 'apixu-weather', 'awe-basic-settings');
	add_settings_field('aw-error-handling', __('Error Handling', 'apixu-weather'), 'apixu_weather_error_handling_setting', 'apixu-weather', 'awe-basic-settings');

	if (isset($_GET['action']) AND $_GET['action'] == "apixu-weather-clear-transients") {
		apixu_weather_delete_all_transients();
		wp_redirect("options-general.php?page=apixu-weather&apixu-weather-cached-cleared=true");
		die;
	}
}

// DELETE ALL APIXU WEATHER WIDGET TRANSIENTS
function apixu_weather_delete_all_transients_save( $value ) {
	apixu_weather_delete_all_transients();

	return $value;
}

function apixu_weather_delete_all_transients() {
	global $wpdb;

	// DELETE TRANSIENTS
	$sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_awe_%'";
	$clean = $wpdb->query($sql);

	return true;
}

function apixu_weather_api_keys_description() {
}

function apixu_weather_openweather_key() {
	if (defined('APIXU_WEATHER_APPID')) {
		echo "<em>" . __('Defined in wp-config', 'apixu-weather-pro') . ": " . APIXU_WEATHER_APPID . "</em>";
	} else {
		$setting = esc_attr(apply_filters('apixu_weather_appid', get_option('open-weather-key')));
		echo "<input type='text' name='open-weather-key' value='$setting' style='width:70%;' />";
		echo "<p>";
		echo __("Apixu requires an APP ID key to access their weather data.", 'apixu-weather');
		echo " <a href='https://www.apixu.com/signup.aspx' target='_blank'>";
		echo __('Get your APPID', 'apixu-weather');
		echo "</a>";
		echo "</p>";
	}
}

function apixu_weather_error_handling_setting() {
	$setting = esc_attr(get_option('aw-error-handling'));
	if (!$setting)
		$setting = "source";

	echo "<input type='radio' name='aw-error-handling' value='source' " . checked($setting, 'source', false) . " /> " . __('Hidden in Source', 'apixu-weather') . " &nbsp; &nbsp; ";
	echo "<input type='radio' name='aw-error-handling' value='display-admin' " . checked($setting, 'display-admin', false) . " /> " . __('Display if Admin', 'apixu-weather') . " &nbsp; &nbsp; ";
	echo "<input type='radio' name='aw-error-handling' value='display-all' " . checked($setting, 'display-all', false) . " /> " . __('Display for Anyone', 'apixu-weather') . " &nbsp; &nbsp; ";

	echo "<p>";
	echo __("What should the plugin do when there is an error?", 'apixu-weather');
	echo "</p>";
}

if (!function_exists('mydebug')){
	function mydebug( $data ) {
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	}
}