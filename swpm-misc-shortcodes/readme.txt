=== Simple Membership Misc Shortcodes ===
Contributors: smp7, wp.insider
Donate link: https://simple-membership-plugin.com/
Tags: member, members, membership, access, level
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 2.7
License: GPLv2 or later

Misc shortcodes and Features addon for Simple WordPress Membership plugin

== Description ==

This addon has a collection of miscellaneous shortcodes and features for the Simple Membership plugin. Over time more shortcodes will be added to it.

Once this addon plugin is installed and activated, you can use the shortcodes and features explained on the following documentation page:

https://simple-membership-plugin.com/simple-membership-miscellaneous-shortcodes-addon/

== Installation ==

1. Upload `swpm-misc-shortcodes` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

None

== Screenshots ==

None

== Changelog ==

= 2.7 =
* Added a new action hook `swpm_msc_before_auto_downgrade_membership_level` to allow custom code to run before the auto downgrade check.
* Added a new action hook `swpm_msc_after_auto_downgrade_membership_level` to allow custom code to run after the auto downgrade task.
* Added a new filter hook `swpm_msc_update_level_to` to allow custom code to run before the level update is applied.

= 2.6 =
* Reworked the auto downgrade feature to check and apply the downgrade at "init" time (instead of using only the login action hook).
* Added an option to redirect the member to a custom page after the auto downgrade is applied.
* Added an action hook at the start of the auto downgrade check.
* Added output escaping to the update level shortcode
* Added documentation link in the settings page.