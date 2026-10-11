=== Simple Membership Partial Protection ===
Contributors: smp7, wp.insider
Donate link: https://simple-membership-plugin.com/
Tags: membership, protection, restrict, partial, section
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 2.1
License: GPLv2 or later

Simple Membership Addon to apply partial or section protection to your posts or pages

== Description ==

If you want to selectively protect or hide specific sections of a post or page, the free Simple Membership Partial Protection addon is the perfect solution. 

This addon allows you to create engaging teaser content by displaying part of the page content for free, while keeping the remaining content accessible only to members.

This addon requires the [Simple Membership Plugin](https://wordpress.org/plugins/simple-membership/).

Read [Usage Documentation](https://simple-membership-plugin.com/apply-partial-section-protection/)

== Installation ==

1. Upload `swpm-partial-protection` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
None

== Screenshots ==
None

== Changelog ==

= 2.1 =
* Some of the default partial content protection messages can be customized using the Custom Messages addon.

= 2.0 =
* Added a new parameter for 'account_status' in the shortcode. It can be used to protect content based on the member's account status.
* NOTE: if you were using the 'expired' status in the visible_to parameter, you should now use 'expired' status in the account_status parameter.
* The 'for' and 'not_for' parameters can now have both comma (,) and hyphen (-) separators (for backward compatibility).

= 1.5.1 =
* Made the separator to be comma (,) for the member_id and visible_to parameters.

= 1.5 =
* The member_id parameter can now be used to specify multiple member IDs separated by a hyphen (-).
* The visible_to parameter can now be used to specify multiple member account status separated by a comma (,).

== Upgrade Notice ==
None
