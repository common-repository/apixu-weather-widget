=== Plugin Name ===
Contributors: apixu
Tags: widgets, sidebar, shortcode, apixu, weather, weather widget, forecast, global, temp, local weather,local forecast
Requires at least: 3.5
Tested up to: 5.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Beautiful, simple, responsive and customizable weather widgets for your beautiful site.

== Description ==
This plugin allows you to easily add Apixu weather widgets to your site.

Apixu Weather plugin is fully responsive (automatically adapt appearance for mobile displays), highly customizable (add as widget or shortcode), beautiful and simple to use.

The weather data is provided for free by [Apixu.com]https://www.apixu.com, they require an [API Key](https://www.apixu.com/signup.aspx) to access their weather. Once you have the API Key you can simply add it in `'Settings' -> 'Apixu Weather'` and you're ready to go.

Use the built in widget with all of its custom settings or add it to a page or theme with the shortcode: (all settings shown)

`[apixu-weather location='Minsk, Belarus' units='C' size='tall' forecast_days='3' scheme='1' hide_stats='1' inline_style='max-width: 300px' custom_bg_color='green' text_color='red']`

= Settings =

*   Location: Enter a string like "Minsk, Belarus" or just "London". You may also pass latitude and longitude in decimal degree. E.g: 53.1,-0.12
*   Units: C (default) or F
*   Size: tall (default) or wide
*   Forecast Days: How many days to show in the forecast bar (3 or 5)
*   Hide stats: Hide the text stats like humidity, wind, high and lows, etc
*   Inline style: styles for widget

*   Sample:
*   scheme="45"  means widget 5 style will be choosen.  
*   Custom background color and test color is only available for 81,1 widget codes and not active if other.

*   Widget styles shortlist. Code – widget style.
*   41 - Widget 1
*   42 - Widget 2
*   43 - Widget 3
*   44 - Widget 4
*   45 - Widget 5
*   72 - Widget 6
*   73 - Widget 7
*   81 - Widget (custom) 8
*   1 - Widget (custom) 9

*   Colors
*   custom_bg_color = 'green' also available code, for example '#000' (Black)
*   text_color = 'red' also available code, for example '#000' (Black)


== Installation ==

1. Add plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Register for an Apixu [API Key](https://www.apixu.com/signup.aspx)
1. Add your API Key to the settings field in `'Settings' -> 'Apixu Weather'` (added in version 1.0)
1. Use shortcode or widget to display apixu weather on your apixu site

== Screenshots ==
1. Basic tall layout (all stats)
2. Basic tall layout
3. Basic tall layout
4. Basic tall layout
5. Basic tall layout
6. Basic tall layout
7. Basic tall layout
8. Basic tall layout

== Changelog ==
= 1.0 =
* Initial load of the plugin.
= 1.1.0 =
* Change api logic.
= 1.2.0 =
* Add selection how to render location.
= 1.3.0 =
* Bugs and fixes
* SSL