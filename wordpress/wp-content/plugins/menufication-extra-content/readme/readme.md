# Menufication Extra Content ##
**Menufication Extra Content v1.0**

Thank you for purchasing our plugin - Menufication Extra Content. If you have any questions that are beyond the scope of this readme file, please feel free to email us via our user page contact form [here](http://codecanyon.net/user/iveo). Thanks so much!

## Table of contents
* [Features](#features)
* [Files Included](#files)
* [Setup](#setup)
* [Important Notes](#notes)
* [Browser Support](#support)
* [Dependencies](#deps)


## <p id="features">Features</p>

* Generates a responsive fly-out content area (similar to Facebook mobile applications).
* Swipe the screen to open/close the menu (iOS only in v1).
* Includes two beautiful themes, one dark and one light.
* Add any content! Shortcodes, images, text.
* Option to only generate the menu on predefined browser sizes.
* Option to only generate the menu on mobile devices.
* Utilizes CSS-tranforms for optimal performance. Falls back to jQuery animations when CSS-transforms are not supported.
* Only dependency is jQuery.


## <p id="files">Files Included</p>

##### PHP-files
This plugin	ships with one main php-file.

##### CSS-files
There are two CSS-files included, one for the styling of the actual menu and one for the admin-page. If you wish to make some changes to the styling of the plugin this should **not** be done in the `offcanvas.min.css`, but instead in the settings section.

##### JavaScript-files
There are three JavaScript-files included in this plugin. One is simply for hiding/showing the advanced settings in the admin-area. The main work is done by `jquery.offcanvas.min.js` and `offcanvas-setup.js`. 


## <p id="setup">Setup</p>

### <p id="setup-installation">Installation</p>

* Unzip the .zip-folder.
* Upload the unzipped folder to your plugin folder in wp-content.
* Activate the plugin on the plugins page in Wordpress Admin.

You can read more about manual installation of pugins [here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).


### <p id="setup-settings">Customize settings</p>

* To access the settings for Menufication Extra Content, click 'Settings' => 'Menufication Extra Content' in the admin-panel.


All other settings are explained on the settings-page for the plugin.

### <p id="setup-settings">Compatability with Menufication Plugin</p>

Menufication Extra Content works great with Menufication. When you install Menufication the extra content area will seamlessly integrate with Menufication and be shown on the opposite side of the menu.


## <p id="notes">Important notes</p>

Below are some important notes about this plugin.

**You should only have to worry about this section if you are an advanced user wanting to understand and change the behavior of the plugin.**

This plugin uses advanced CSS3-features to enable native-like performance.


## <p id="support">Browser Support</p>

The following browsers are officially supported and tested (Browser > Version):

##### <p id="support-desktop">Desktop</p>
* Chrome >= 20
* Mozilla Firefox >= 15
* Safari >= 5
* Opera >= 12
* Internet Explorer >= 9

##### <p id="support-mobile">Mobile</p>
* Chrome for Android / iOS
* Firefox for Android
* AndroidBrowser for Android
* Safari Mobile for iOS


Wordpress Menufication does not currently support Internet Explorer Mobile or Blackberry. Support will be added in upcoming versions.

** Nothing will break on unsupported browsers - the menu will just simply not appear. **


## <p id="deps">Dependencies</p>

* jQuery >= 1.7.0