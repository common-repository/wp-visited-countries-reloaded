=== WP Visited Countries Reloaded ===
Contributors: amellienet, JonasBreuer
Donate link: http://www.j-breuer.com
Tags: travel, traveling, travel blog, countries, visited countries, ammap, map, lived countries
Requires at least: 3.5
Tested up to: 5.8.2
Stable tag: 3.1.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Creates and shows your visited countries map to your visitors. Show them that you've conquered the world! :)

== Description ==

WP Visited Countries Reloaded is a WordPress plugin that allows you to easily create and publish a map of the countries you have visited and lived. 

* Interactive map
* Can be easily customized via the admin panel to suit your preferences. This includes map color, width, height, and many more!
* Can be added into a page, post, or widget
* Visited/lived countries are highlighted with different colors that can be set by the user.
* Each country is clickable when provided with a URL, allowing you to direct your visitors to the preferred posts in your blog/website
* Can be translated to other languages. Full i18n-Support through gnutext mo/po files
* Supports multisite

View a LIVE demo of the plugin at [mindofahitchhiker.com](https://mindofahitchhiker.com/hitchhiking-routes-trips-and-maps/)

This plugin uses [amMap](http://www.ammap.com/) for displaying a map with JavaScript.

Use the following shortcode to embed the map in your post:

`[wp-visited-countries width="600" height="300"]I've visited {num} out of {total} countries. That is {percent}.[/wp-visited-countries]`


== Installation ==

1. Install automatically through the `Plugins` > `Add New` menu in WordPress, or upload the entire `wp-visited-countries-reloaded` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress. A `Visited Countries` menu will then appear in your WordPress admin panel. Click on the menu to configure the settings and add new country
3. Use the following shortcode to embed the map in your post:
`[wp-visited-countries width="600" height="300"]I've visited {num} out of {total} countries. That is {percent}.[/wp-visited-countries]`


== Screenshots ==

1. Map of visited countries, added into a page
2. Admin for editing map settings
3. Admin for adding/editing a country


== Changelog ==


= 1.0.0 =
* This is the first release version.

= 1.1.0 =
* Supports multisite feature

= 1.1.1 =
* Fix some bugs

= 1.1.2 =
* Fix bugs when deleting

= 2.0.0 =
* Fatal error on plugin activation fixed
* Switched lived and visited legend fixed
* Fixed input escape
* Only delete options on uninstall

= 2.0.1 =
* Fixed error when non-admin is logging in

= 2.0.2 =
* Fixed bug where wrong map was shown

= 3.0.0 =
* Changed the ammap library to JavaScript
* Settings will only be deleted when deinstalling the plugin, not when deactivating
* Added missing countries
* Minor bugfixes

= 3.0.1 =
* Fixed a bug with the plugin updates

= 3.1.0 =
* Support for tiny countries in the map
* Map size can be entered in percent
* Fixed deprecated issues in PHP 7