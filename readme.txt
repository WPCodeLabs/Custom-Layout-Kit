=== Advanced Custom Layouts ===
Contributors: gaiusinvictus
Donate link: https://www.wpcodelabs.com
Tags: beaver builder, elementor, custom layouts
Requires at least: 4.0.1
Requires PHP: 5.6
Tested up to: 5.4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advanced Custom Layouts takes advantage of the action hooks available in popular plugins and themes to conditionally inject custom layouts into your website.

== Description ==

Advanced Custom Layouts is a WordPress plugin that takes advantage of popular theme and plugin hooks to inject content anywhere. From simple global elements, to complex conditionals, you have complete control.

ACL supports both Beaver Builder and Elementor page builders, as well as the WordPress block and classic editors, allowing you to design your custom layouts your way. Take Beaver Builder and Elementor outside of the content area!

= Features: =
* Attach your custom layouts to action hooks in your themes and plugins.
* Display layouts within widgets and with a handy shortcode.
* Conditionally display the templates on the desired pages, with both simple and complex display rules.
* Extend the action hooks for any theme with simple filters!

== Installation ==

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `advanced-custom-layouts.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `advanced-custom-layouts.zip`
2. Extract the `advanced-custom-layouts` directory to your computer
3. Upload the `advanced-custom-layouts` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

e.g.

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Changelog ==

= 1.0.0 =
* First public release.