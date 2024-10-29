<?php

// APIXU WEATHER WIDGET, WIDGET CLASS, SO MANY WIDGETS
class ApixuWeatherWidget extends WP_Widget
{
	function ApixuWeatherWidget() {
		parent::__construct(false, $name = 'Apixu Weather Widget');
	}

	function widget( $args, $instance ) {
		extract($args);

		$location = isset($instance['location']) ? $instance['location'] : false;
		$widget_title = isset($instance['widget_title']) ? $instance['widget_title'] : false;
		$units = isset($instance['units']) ? $instance['units'] : false;
		$size = isset($instance['size']) ? $instance['size'] : false;
		$forecast_days = isset($instance['forecast_days']) ? $instance['forecast_days'] : false;
		$widget_scheme = isset($instance['scheme']) ? $instance['scheme'] : 1;
		$show_stats = (isset($instance['show_stats']) AND $instance['show_stats'] == 1) ? 1 : 0;
		$location_case = isset($instance['location_case']) ? esc_attr($instance['location_case']) : 3;
		//$show_link = (isset($instance['show_link']) AND $instance['show_link'] == 1) ? 1 : 0;
		$show_link = 1;
		$custom_bg_color = isset($instance['custom_bg_color']) ? $instance['custom_bg_color'] : false;
		$text_color = isset($instance['text_color']) ? $instance['text_color'] : "#20a5d6";

		echo $before_widget;
		if ($widget_title != "")
			echo $before_title . $widget_title . $after_title;
		echo apixu_weather_logic(array(
			'location'        => $location,
			'size'            => $size,
			'units'           => $units,
			'forecast_days'   => $forecast_days,
			'scheme'          => $widget_scheme,
			'show_stats'      => $show_stats,
			'show_link'       => $show_link,
			'custom_bg_color' => $custom_bg_color,
			'text_color'      => $text_color,
			'location_case'      => $location_case
		));
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['location'] = strip_tags($new_instance['location']);
		$instance['widget_title'] = strip_tags($new_instance['widget_title']);
		$instance['units'] = strip_tags($new_instance['units']);
		$instance['size'] = strip_tags($new_instance['size']);
		$instance['forecast_days'] = strip_tags($new_instance['forecast_days']);
		$instance['scheme'] = strip_tags($new_instance['scheme']);
		$instance['custom_bg_color'] = strip_tags($new_instance['custom_bg_color']);
		$instance['text_color'] = strip_tags($new_instance['text_color']);
		$instance['show_stats'] = (isset($new_instance['show_stats']) AND $new_instance['show_stats'] == 1) ? 1 : 0;
		$instance['location_case'] = isset($new_instance['location_case']) ? esc_attr($new_instance['location_case']) : 3;
		//$instance['show_link'] = (isset($new_instance['show_link']) AND $new_instance['show_link'] == 1) ? 1 : 0;
		$instance['show_link'] = 1;

		return $instance;
	}

	function form( $instance ) {
		global $apixu_weather_sizes;
		$location = isset($instance['location']) ? esc_attr($instance['location']) : "";
		$widget_title = isset($instance['widget_title']) ? esc_attr($instance['widget_title']) : "";
		$selected_size = isset($instance['size']) ? esc_attr($instance['size']) : "tall";
		$units = (isset($instance['units']) AND strtoupper($instance['units']) == "F") ? "F" : "C";
		$forecast_days = isset($instance['forecast_days']) ? esc_attr($instance['forecast_days']) : 3;
		$show_stats = (isset($instance['show_stats']) AND $instance['show_stats'] == 1) ? 1 : 0;
		//$show_link = (isset($instance['show_link']) AND $instance['show_link'] == 1) ? 1 : 0;
		$show_link = 1;
		$custom_bg_color = isset($instance['custom_bg_color']) ? esc_attr($instance['custom_bg_color']) : "";
		$text_color = isset($instance['text_color']) ? esc_attr($instance['text_color']) : "#ffffff";
		$widget_scheme = isset($instance['scheme']) ? esc_attr($instance['scheme']) : 1;
		$location_case = isset($instance['location_case']) ? esc_attr($instance['location_case']) : 3;

		$appid = apply_filters('apixu_weather_appid', awe_get_appid());
		$wp_theme = wp_get_theme();
		$wp_theme = $wp_theme->get('TextDomain');
		?>

		<style>
			.awe-suggest {
				font-size: 0.9em;
				border-bottom: solid 1px #ccc;
				padding: 5px 1px;
				font-weight: bold;
			}

			.awe-size-options {
				padding: 1px 10px;
				background: #efefef;
			}
		</style>


		<?php if (!$appid) { ?>
			<div style="background: #dc3232; color: #fff; padding: 10px; margin: 10px;">
				<?php
				echo __("As of October 2015 Apixu weather requires an APP ID key to access their weather data.", 'apixu-weather');
				echo " <a href='https://www.apixu.com/signup.aspx' target='_blank' style='color: #fff;'>";
				echo __('Get your APPID', 'apixu-weather');
				echo "</a> ";
				echo __("and add it to the new settings page.");
				?>
			</div>
		<?php } ?>

		<p>
			<label for="<?php echo $this->get_field_id('location'); ?>">
				<?php _e('Search for Your Location:', 'apixu-weather'); ?><br/>
				<small><?php _e('(i.e: Minsk,BY or - London,UK)', 'apixu-weather'); ?></small>
			</label>
			<input data-cityidfield="<?php echo $this->get_field_id('owm_city_id'); ?>" data-unitsfield="<?php echo $this->get_field_id('units'); ?>" class="widefat  awe-location-search-field-apixu" style="margin-top: 4px;"
				   id="<?php echo $this->get_field_id('location'); ?>" name="<?php echo $this->get_field_name('location'); ?>" type="text" value="<?php echo $location; ?>"/>
		</p>

		<span id="awe-owm-spinner-<?php echo $this->get_field_id('location'); ?>" class="hidden"><img src="<?=admin_url( 'images/spinner.gif');?>"></span>
		<div id="owmid-selector-<?php echo $this->get_field_id('location'); ?>"></div>
		<p>
			<label for="<?php echo $this->get_field_id('units'); ?>"><?php _e('Units:', 'apixu-weather'); ?></label> &nbsp;
			<input id="<?php echo $this->get_field_id('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>" type="radio" value="F" <?php if ($units == "F")
				echo ' checked="checked"'; ?> /> F &nbsp; &nbsp;
			<input id="<?php echo $this->get_field_id('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>" type="radio" value="C" <?php if ($units == "C")
				echo ' checked="checked"'; ?> /> C
		</p>

		<div class="awe-size-options">

			<?php if ($wp_theme == "twentytwelve") { ?>
				<div class="awe-suggest"> Suggested settings: Wide, 5 Days</div><?php } ?>
			<?php if ($wp_theme == "twentythirteen") { ?>
				<div class="awe-suggest"> Suggested settings: Tall, 3 Days</div><?php } ?>
			<?php if ($wp_theme == "twentyfourteen") { ?>
				<div class="awe-suggest"> Suggested settings: Tall, 3 Days</div><?php } ?>
			<?php if ($wp_theme == "twentyfifteen") { ?>
				<div class="awe-suggest"> Suggested settings: Tall, 3 Days</div><?php } ?>
			<?php if ($wp_theme == "twentysixteen") { ?>
				<div class="awe-suggest"> Suggested settings: Wide, 5 Days</div><?php } ?>

			<p>
				<label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Size:', 'apixu-weather'); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>">
					<?php foreach ($apixu_weather_sizes as $size) { ?>
						<option value="<?php echo $size; ?>"<?php if ($selected_size == $size)
							echo " selected=\"selected\""; ?>><?php echo $size; ?></option>
					<?php } ?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('forecast_days'); ?>"><?php _e('Forecast:', 'apixu-weather'); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('forecast_days'); ?>" name="<?php echo $this->get_field_name('forecast_days'); ?>">
					<option value="5"<?php if ($forecast_days == 5)
						echo " selected=\"selected\""; ?>>5 Days
					</option>
					<option value="3"<?php if ($forecast_days == 3)
						echo " selected=\"selected\""; ?>>3 Days
					</option>
					<option value="hide"<?php if ($forecast_days == 'hide')
						echo " selected=\"selected\""; ?>>Don't Show
					</option>
				</select>
			</p>

		</div>

		<p class="scheme>
			<label for="<?php echo $this->get_field_id('scheme'); ?>"><?php _e('Sample №:', 'apixu-weather'); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('scheme'); ?>" name="<?php echo $this->get_field_name('scheme'); ?>">
			<?php
			$widget_scheme_ids = array(1 => 41, 2 => 42, 3 => 43, 4 => 44, 5 => 45, 6 => 72, 7 => 73, 8 => 81, 9 => 1, /*10=>91*/);
			foreach ($widget_scheme_ids as $k => $v) {
				$selected = ($widget_scheme == $v) ? 'selected="selected"' : '';
				if ($k==8 || $k==9) $custom = '(custom)';
				echo "<option value=\"{$v}\" {$selected}>Widget {$custom} {$k}</option>";
			}
			?>
		</select>
		</p>
		<p class="location-case">
			<label for="<?php echo $this->get_field_id('location_case'); ?>"><?php _e('Show location as:', 'apixu-weather'); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('location_case'); ?>" name="<?php echo $this->get_field_name('location_case'); ?>">
			<?php
			$widget_location_case_ids = array(1 => 'City Name, State Name, Country', 2 => 'City Name, State Name', 3 => 'City Name, Country', 4 => 'Only City Name');
			foreach ($widget_location_case_ids as $k => $v) {
				$selected = ($location_case == $k) ? 'selected="selected"' : '';
				echo "<option value=\"{$k}\" {$selected}>{$v}</option>";
			}
			?>
		</select>
		</p>
		<div class="<?=$this->get_field_id('scheme')?>" <?php if ( !in_array($widget_scheme, array(1, 81))) { echo 'style="display: none;"'; }?>>
		<p class="<?php echo $this->get_field_id('scheme'); ?>">
			<label for="<?php echo $this->get_field_id('custom_bg_color'); ?>"><?php _e('Custom Background Color:', 'apixu-weather'); ?></label><br/>
			<!--<small>--><?php //_e('overrides color changing', 'apixu-weather'); ?><!--: #7fb761 or rgba(0,0,0,0.5)</small>-->
			<input class="widefat color-picker" id="<?php echo $this->get_field_id('custom_bg_color'); ?>" name="<?php echo $this->get_field_name('custom_bg_color'); ?>" type="text" value="<?php echo $custom_bg_color; ?>"/>
		</p>

		<p class="<?php echo $this->get_field_id('scheme'); ?>">
			<label for="<?php echo $this->get_field_id('text_color'); ?>" style="display:block;"><?php _e('Text Color', 'apixu-weather'); ?></label>
			<input class="widefat color-picker" id="<?php echo $this->get_field_id('text_color'); ?>" name="<?php echo $this->get_field_name('text_color'); ?>" type="text" value="<?php echo esc_attr($text_color); ?>"/>
		</p>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function($){
				jQuery('#<?php echo $this->get_field_id('text_color'); ?>').on('focus',function(){
					var parent = jQuery(this).parent();
					jQuery(this).wpColorPicker();
					parent.find('.wp-color-result').click();
				});
				jQuery('#<?php echo $this->get_field_id('text_color'); ?>').wpColorPicker();

				jQuery('#<?php echo $this->get_field_id('custom_bg_color'); ?>').on('focus',function(){
					var parent = jQuery(this).parent();
					jQuery(this).wpColorPicker();
					parent.find('.wp-color-result').click();
				});
				jQuery('#<?php echo $this->get_field_id('custom_bg_color'); ?>').wpColorPicker();

				jQuery('#<?php echo $this->get_field_id('scheme'); ?>').on('change',function(){
					if (jQuery(this).val() == 1 || jQuery(this).val() == 81){
						jQuery('div.<?php echo $this->get_field_id('scheme'); ?>').show();
					} else {
						jQuery('div.<?php echo $this->get_field_id('scheme'); ?>').hide();
					}
				});
			});
		</script>
		<p>
			<input id="<?php echo $this->get_field_id('show_stats'); ?>" name="<?php echo $this->get_field_name('show_stats'); ?>" type="checkbox" value="1" <?php if ($show_stats)
				echo ' checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('show_stats'); ?>"><?php _e('Additional data', 'apixu-weather'); ?></label>
		</p>

		<!--<p>-->
		<!--	<input id="--><?php //echo $this->get_field_id('show_link'); ?><!--" name="--><?php //echo $this->get_field_name('show_link'); ?><!--" type="checkbox" value="1" --><?php //if ($show_link)
		//		echo ' checked="checked"'; ?><!-- />-->
		<!--	<label for="--><?php //echo $this->get_field_id('show_link'); ?><!--">--><?php //_e('Link to Apixu weather', 'apixu-weather'); ?><!--</label> &nbsp;-->
		<!--</p>-->

		<p>
			<label for="<?php echo $this->get_field_id('widget_title'); ?>"><?php _e('Widget Title: (optional)', 'apixu-weather'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo $widget_title; ?>"/>
		</p>
		<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("ApixuWeatherWidget");'));