=== Tasdid Gateway ===
Donate link: https://tasdid.net
Tags: woocommerce, payments
Requires at least: 3.0.1
Tested up to: 5.5
Stable tag: 5.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Have your users pay there bills using there masterQi or Qi-Card


== Installation ==

installing plugin is so easy all what you need to do is:

1. Upload `tasdid-gateway.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates
1. go to woocommerce->settings->payments
1. click on Tasdid Gateway
1. enable the gateway and enter required data
1. go to tasdid.net
1. to your profile settings
1. set V2 webhook to mydomain.com/wp-json/tasdid/v1/orders

== Changelog ==

= 1.3.0 =
**Improve Plugin Performance and fix bugs**
* Fix: pay using tasdid message 
* Fix: thank you page **Pay Bill** issue

** Add ability to add custom service_id for each product**
= 1.4.0 =
**Add ability to use another currency in the store**
* add dollar currency support 
* add currency change ability
** Add webhook url in settings page**
* add webhook url so user can copy it easily 


== Upgrade Notice ==
