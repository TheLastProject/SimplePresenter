=== Simple Presenter ===
Contributors: sylviavanos
Tags: presentation, monitor, raspberry, pi, digital signage
Requires at least: 4.9.5
Tested up to: 4.9
Stable tag: 4.9.5
Requires PHP: 5.4.16
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A simple way to create presentations that can be viewed in a web browser, meant for usage in a company by displaying it on Raspberry Pi's.

== Description ==

Simple Presenter was born out of a request within one of the companies I was assigned to to replace the current digital signage solution. Due to the fact that WordPress was used by everyone who had to manage it, and the lack of finding any solution that really worked for us without a huge investment of time, it was decided to code up our own WordPress plugin. This is that plugin.

Simple Presenter allows you to:
- Define an infinite number of screens
- Set a logo image, background color and text color for each screen
- Show events from an infinite number of calendars (only Tribe via the JSON API is currently supported, max 5 events per calendar are shown)
- An infinite number of extra slides of practically any content (image, html, shortcodes, etc.)
- Choose exactly what to display on which screen

Simple Presenter is meant to be simple above powerful and is written for the purposes of a single company. However, it was decided the plugin is useful and generic enough to publish it for broader use.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/simple-presenter` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Simple Presenter screen to configure the plugin

== Frequently Asked Questions ==

= How can I best display these screens? =

There are many ways to go about it, but personally, we recommend [minimalKioskOS](https://github.com/TheLastProject/minimalKioskOS) on a Raspberry Pi.

The recommended settings are as follows:
- /boot/mutesound.txt: 10
- /boot/spamkey.txt: f (if you want videos to play fullscreen, otherwise empty)
- /boot/url.txt: The URL shown on "View screen X"

= Can I show video? =

Yes! Just make sure to change the slide length to the length of the video and put a YouTube/Vimeo/etc. embed code in the custom HTML field.

Some gotchas:
- Make sure to enable autoplay in the embed, or the video won't play
- You probably want to manually set the width and height of the iframe to a high value like 99999, Simple Presenter's CSS should keep it from getting bigger than the screen.

== Screenshots ==

1. Managing screens
2. Managing calendars
3. Managing extra slides
4. The generated output

== Changelog ==

= 1.2 =
* Enhancement - Error messages are now prettier
* Enhancement - Extra slides can now use WordPress shortcodes
* Enhancement - Can now change display length per extra slide
