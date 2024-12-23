=== Honeypot for WP Comment ===
Contributors: prasidhda, sranzan
Tags: comments, spam, spam protection, anti spam, honeypot, comments security, email domain restriction, spam blockade
Requires at least: 4.5
Tested up to: 5.8
Stable tag: 2.2.3
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simple plugin to trap the spam comments using honeypot technique.

== Description ==

It is a very simple plugin to filter out the spam comments by using the popular technique called "Honeypot". This is not the 100% solution to every possible spam comment, however I believe that this simple pattern can avoid at least 50% of spams in your comment.

### Main Features
1. Option to user defined honeypot fields
2. Option to record logs
3. Option not to save trapped spammed comments to database (Complete removal)
4. Option to email domain restriction
5. Option to choose trapped spam comments status ( spam or trash )
6. Option to block comment with common spam words
7. Option to add your own spam words
7. Option to block comment if comment content includes any link.
7. Option to block comment from IP address

== Installation ==

1. Download and Upload the plugin folder 'honeypot-for-wp-comment'  to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Then, you are done. Everything will be handled automatically.
4. If you want to customize settings, you can navigate to the "Honeypot" side menu and further personalize the settings.

== Screenshots ==

1. Honeypot basic settings
2. Honeypot advanced settings
3. Honeypot logs

== Changelog ==
= 2.2.3 =
* WP 5.8 compatibility check

= 2.2.2 =
* fix: php8 compatibility
* WP 5.7 compatibility check

= 2.2.1 =
* fix: Undefined offset: 1 (Notice fix)

= 2.2.0 =
* feat: IP address blocking

= 2.1.2 =
* WP 5.6 compatibility check

= 2.1.1 =
* WP 5.5 compatibility check

= 2.1.0 =
* Blocking by common spam words
* Blocking if comment message includes any link
* Translation text domain issue fixed.

= 2.0.2 =
* Garbage cleaning

= 2.0.0 =
* User defined honeypot fields
* Enable / Disable honeypot
* Completely blocking
* Email domain restriction
* Logs system

= 1.0.0 =
* First Version

== Upgrade Notice ==
= 2.1.0 =
Version 2.1.0 includes additional features of spam words blocking and link content blocking.