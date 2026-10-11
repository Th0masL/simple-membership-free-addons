=== Simple Membership Custom Messages ===
Contributors: smp7, wp.insider
Donate link: https://simple-membership-plugin.com/
Tags: users, membership, custom, message, protection-message,
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 2.6
License: GPLv2 or later

Simple Membership Addon to customize various content protection messages.

== Description ==

This addon allows you to customize the content protection message that gets output from the membership plugin.

You will be able to specify your custom messages for different types of protection message.

This addon requires the [Simple Membership Plugin](https://wordpress.org/plugins/simple-membership/).

After you install this addon, go to the "Custom Message" menu from the admin dashboard to use it. 

Read [Usage Documentation](https://simple-membership-plugin.com/simple-membership-custom-messages-addon/)

== Installation ==

1. Upload `swpm-custom-message` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
None

== Screenshots ==
None

== Changelog ==

= 2.6 =
- Added new settings to allow customization of some of the partial protection addon's output.

= 2.5 = 
- Minor translation string output related update.
- CSS Updated for the settings field help text.
- Added sanitization to the tab parameter in the settings page.

= 2.4 =
* New settings added to allow customization of the mini/compact login form output.
* Added the option to use dynamic merge tags in the custom messages for logged-in members.
* Requires Simple Membership Plugin v4.5.0+

= 2.3 =
* Added link to the settings interface from the plugin's list page.

= 2.2 =
* Added three new message options in the settings interface for displaying custom messages.

= 2.1 =
* The more tag protection feature will also use the "Account Expired" message for expired members.

= 2.0 =
* Added a new system that will allow addition of dynamic merge tags. 
* Added a new dynamic merge tag {login_url}. This tag can be used in the settings of this addon and the the plugin will process it on page load.

= 1.9 =
* The older post protection message can now be customized via this addon.

= 1.8 =
* The "Email Activation" message can now be customized.

= 1.7 =
* Minor fix for a PHP warning if using the 'Full Page Protection addon message'

= 1.6 =
* Full Page Protection addon message can be customized now. Requires FPP v1.2+.

= 1.5 =
* Fixed a PHP notice warning.

= 1.4 =
* The "Registration Successful" message can now be customized using this addon.
* Requires at least Simple Membership plugin v3.3.2

= 1.3 =
* Added custom message option for expired members.
* You can now use HTML in the custom message. 

= 1.2 =
* Shortcodes in the protection message will also be parsed.

= 1.1 =
* First commit to wordpress.org

== Upgrade Notice ==
None
