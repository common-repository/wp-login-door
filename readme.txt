=== WP Login Door ===
Contributors: toxnico
Website: http://dirtymarmotte.net
Donate link: http://dirtymarmotte.net/en/donations/
Tags: security, login, hide, bruteforce, xmlrpc, disable
Requires at least: 4.0.0
Tested up to: 4.9
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Did you ever feel like your website or blog login page is ridiculously fragile and reachable, and could be easily broken in by an intruder?

Personally I hate to think of hundreds of people playing with my door lock hundreds of times a day. It's the same with my blog login page.

On Wordpress, there are two main potential vectors of bruteforce intrusion:
*  http://my-site.com/wp-login.php, which is the login page
*  http://my-site.com/xmlrpc.php, which is an API gateway for interacting with third party applications.

This plugin adds one security layer in front of your login page, and by the way you can also disable XML-RPC with a simple checkbox if you don't need it (XML-RPC is a _**WIDELY**_ used vector of attacks).

The idea is simple: you choose a pair of words, and when you want to access your login page, you just have to provide them in the URL like this: http://my-site.com/wp-login.php?word1=word2. That's all!
If you try to access your login page without this pair of words, you get a configurable error message, where you can insult the attacker as much as you want ;)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-login-door` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Wp Login Door screen to configure the plugin
4. Enjoy your new door :)

== Frequently Asked Questions ==

= What if I lose my pair of words? =

You can disable the plugin from your FTP server.
Then login as usual, reactivate the plugin, and check your word pair.

= Is that free? =

I don't know if the beerware license is GPL 2 compatible, but if you like this plugin and if we meet someday, you can buy me a beer.

= Is that all ? =

Yes!

== Changelog ==

= 1.5 =
* Let go the 'postpass' standard action (used when user types a password to open a protected post)

= 1.4 =
* Removed some php notices in the administration section.

= 1.1.1 =
* Bug correction. During refactoring, I misspelled the key name sanitization callback, and it could cause problems on some installations, such as key name field not stored.

= 1.1 =
* Added a new setting to redirect home instead of displaying an error message.

= 1.0 =
* First release
