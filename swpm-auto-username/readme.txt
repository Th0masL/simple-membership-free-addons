=== Simple Membership Auto Username ===
Contributors: smp7, wp.insider
Donate link: https://simple-membership-plugin.com/
Tags: membership, username, email, registration
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 1.0
License: GPLv2 or later

Simple Membership Addon to generate the usernames automatically, so members only use their email address.

== Description ==

When the option is enabled (Simple Membership > Settings > Addons Settings), the username field is hidden on the registration and profile forms of Simple Membership, and a unique username is generated for each new member, for example "member-k7q2x9mf". The login form asks for the email address: all members can log in with their email address. The login widget and the [swpm_mini_login] shortcode show the member's email address instead of the username.

The username is set on the server when the registration form is submitted, so a cached registration page or a username taken in the meantime never blocks a registration.

Developers can change the format with the `swpm_auto_generated_username` filter. It receives the default username and, when the form is submitted, the form data (email, first_name, last_name, membership_level). A filtered value is only used if it is a valid username (4 to 50 characters) that is not taken yet; otherwise the default one is used.

If the email address already belongs to a WordPress user, that user's login is used as the username, so Simple Membership handles it as usual: by default it refuses the registration and asks to contact the site admin (sites using the `swpm_allow_existing_wp_user_registration` filter link the new member to that WordPress user).

Limits: it covers the registration and profile forms of Simple Membership (not the Form Builder addon's forms, nor theme copies of the form templates). The default emails still show the username ({user_name}): edit them if you do not want to show it.

This addon requires the [Simple Membership Plugin](https://wordpress.org/plugins/simple-membership/) version 4.8.6 or later.

== Installation ==

1. Upload `swpm-auto-username` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enable it in Simple Membership > Settings > Addons Settings

== Changelog ==

= 1.0 =
- First version.
