=== Delicious Wishlist for WordPress ===
Contributors: Aldo Latino
Donate link: http://www.aldolat.it/info/
Tags: delicious, wishlist, bookmarks
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 0.3

Adds a Wishlist page to your WordPress blog using your Delicious Bookmarks.

== Description ==

This plugin allows you to publish in your blog a wishlist using your Delicious bookmarks. In order to make this, when you visit a web page with something you like, tag that page with two different bookmarks: `wishlist` and, if it is very important, `***` (three stars). Then, when you visit a page with something less important, you could use `wishlist` and `**` (two stars), and finally for a page with something even less important, you could use `wishlist` and `*` (one star). It's not mandatory to use these exact tags: you can choose your own tags, but consider that you have to bookmark a page with at least two different tags: one general to collect all your bookmarks relative to your wishlist, and another to mark that page depending on the importance of the stuff for you. When you are done with an item (you have bought it or someone gave it to you as a gift), you can edit that bookmark on Delicious and remove the star(s), leaving only the main tag (e.g., `wishlist`), so you can maintain in Delicious an archive of all desired items.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload  the `delicious-wishlist-for-wordpress` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the Plugins menu in WordPress
1. Fill in all the fields in the options page of the plugin
1. Create a new page
1. Place `[my-delicious-wishlist]` in that page as content. You can add a custom text before the shortcode and/or after.

== Frequently Asked Questions ==

= May I tag a page using other tags besides those who I chose to tag my wishlists? =

Yes. Keep in mind that you have to choose a general tag to collect all your wishlisted bookmars on Delicious and three different tags to mark them depending on their importance for you. Also, besides them, you can add all the tags you desire.

= May I change the style of the page? =
Yes. The plugin comes with a css file that is used to stylize the page. The plugin, however, looks first in your theme directory: if it finds a file named `wdw.css`, it will use it **instead of** that one provided with the plugin. In this way, you can change the look of your wishlist page and you'll preserve it even if an update of the plugin will be released.

== Screenshots ==

1. The settings panel of the plugin
2. The rendered wishlist page

== Changelog ==

= 0.2 =
* Changed the plugin directory name to match the SVN name.

= 0.1 =
* First release of the plugin.

== Credits ==

My thanks go to all people who contributed in revisioning and helping me in any form, and in particular to [Nicola D'Agostino](http://www.nicoladagostino.net/ "Nicola's website") and to [Barbara Arianna Ripepi](http://suzupearl.com/ "Barbara's website") for their great idea behind this work.
