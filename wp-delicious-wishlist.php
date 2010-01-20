<?php
/*
	Plugin Name: Delicious Wishlist for WordPress
	Description:  Publish a wishlist using your Delicious bookmarks 
	Plugin URI: http://www.aldolat.it/wordpress/wordpress-plugins/delicious-wishlist-for-wordpress/
	Author: Aldo Latino
	Author URI: http://www.aldolat.it/
	Version: 0.3.4
*/

/*
	Copyright (C) 2009, 2010  Aldo Latino  (email : aldolat@gmail.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


// Make plugin available for i18n
// Translations must be archived in the /languages/ directory
load_plugin_textdomain('wp-delicious-wishlist', false, 'delicious-wishlist-for-wordpress/languages');

// Start the main function
function wp_delicious_wishlist($the_content) {

	include_once(ABSPATH . WPINC . '/feed.php');

	// Let's collect some options from the plugin admin panel
	$my_nickname     = get_option('wdw_delicious_nickname');
	$my_tag_wishlist = get_option('wdw_delicious_tag_wishlist');
	$my_title_high   = get_option('wdw_delicious_title_high');
	$my_tag_high     = get_option('wdw_delicious_tag_high');
	$my_title_medium = get_option('wdw_delicious_title_medium');
	$my_tag_medium   = get_option('wdw_delicious_tag_medium');
	$my_title_low    = get_option('wdw_delicious_title_low');
	$my_tag_low      = get_option('wdw_delicious_tag_low');
	$maxitems        = get_option('wdw_delicious_howmany');

	if(get_option('wdw_delicious_icons') == "stars") {
		$my_icons = "stars";
	} else {
		$my_icons = "faces";
	}

	// check if fields' values are blank
	if ($my_nickname == "" || $my_tag_wishlist == "" || $my_tag_high =="" || $my_tag_medium == "" || $my_tag_low == "") {

		// if blank, print this
		_e('You have not properly configured the plugin.<br />Please, setup it in the plugin panel admin, filling in all the required fields.', 'wp-delicious-wishlist');

	} else { // if required options aren't blank, then execute the following code

		// Start 3 stars section

		// If $maxitems has not been declared, then we setup it to 5 items to retrieve
		if ($maxitems == "") $maxitems == "5";

		// Retrieve the items from Delicious using nickname + tag wishlist + high tag + max number of items
		$wdw_rss = fetch_feed('http://feeds.delicious.com/v2/rss/'.$my_nickname.'/'.$my_tag_wishlist.'+'.$my_tag_high.'?count='.$maxitems);

		if(is_wp_error($wdw_rss)) { // Catch the error
			return __('There was a problem fetching your feed.', 'wp-delicious-wishlist');
		} else { 

			$num_items = $wdw_rss->get_item_quantity($maxitems); 

			// Build an array of items
			$wdw_items = $wdw_rss->get_items(0, $num_items);

			$mywishlist = '<h3 id="high">';
				if ($my_title_high) {
					$mywishlist .= $my_title_high;
				} else {
					$mywishlist .= __('I need', 'wp-delicious-wishlist');
				}
			$mywishlist .= '</h3>';
			$mywishlist .= '<ul id="wishlist-high">';
				// If the first (high) section is blank, then let's write "Nothing in this moment"...
				if (empty($wdw_items)) {
					$mywishlist .= '<li class="high-'.$my_icons.'">'.__('Nothing in this moment', 'wp-delicious-wishlist').'</li>';
				} else {
					// ... else start the loop
					foreach ( $wdw_items as $wdw_item ) :
						$mywishlist .= '<li class="high-'.$my_icons.'">
							<a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'
								.$wdw_item->get_title().
							'</a><br />
							<div class="wishlist-description">'.$wdw_item->get_description().'</div>';
							// Parse the date into Unix timestamp
							$time = strtotime($wdw_item->get_date());
							// Compare the current time to bookmark time
							if ((abs(time() - $time)) < 86400)
								$h_time = sprintf(__('%s ago', 'wp-delicious-wishlist'), human_time_diff($time));
							else
								$h_time = date(__('m/d/Y', 'wp-delicious-wishlist'), $time);
							$mywishlist .= sprintf('%s','<div class="wishlist-timestamp"><abbr title="'.date(__('m/d/Y H:i:s', 'wp-delicious-wishlist'), $time).'">'.$h_time.'</abbr></div>');
						$mywishlist .= '</li>';
					endforeach;
				}
			$mywishlist .= '</ul>';
		}

		// Start 2 stars section
		if ($maxitems == "") $maxitems == "5";
		$wdw_rss = fetch_feed('http://feeds.delicious.com/v2/rss/'.$my_nickname.'/'.$my_tag_wishlist.'+'.$my_tag_medium.'?count='.$maxitems);
		if(is_wp_error($wdw_rss)) { // Catch the error
			return __('There was a problem fetching your feed.', 'wp-delicious-wishlist');
		} else {
			$num_items = $wdw_rss->get_item_quantity($maxitems); 
			$wdw_items = $wdw_rss->get_items(0, $num_items);

			$mywishlist .= '<h3 id="medium">';
				if ($my_title_medium) {
					$mywishlist .= $my_title_medium;
				} else {
					$mywishlist .= __('I\'d like', 'wp-delicious-wishlist'); 
				}
			$mywishlist .= '</h3>';
			$mywishlist .= '<ul id="wishlist-medium">';
				if (empty($wdw_items)) {
					$mywishlist .= '<li class="medium-'.$my_icons.'">'.__('Nothing in this moment', 'wp-delicious-wishlist').'</li>';
				} else {
					foreach ( $wdw_items as $wdw_item ) :
						$mywishlist .= '<li class="medium-'.$my_icons.'">
							<a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'
								.$wdw_item->get_title().
							'</a><br />
							<div class="wishlist-description">'.$wdw_item->get_description().'</div>';
							$time = strtotime($wdw_item->get_date());
							if ((abs(time() - $time)) < 86400)
								$h_time = sprintf(__('%s ago', 'wp-delicious-wishlist'), human_time_diff($time));
							else
								$h_time = date(__('m/d/Y', 'wp-delicious-wishlist'), $time);
							$mywishlist .= sprintf('%s','<div class="wishlist-timestamp"><abbr title="'.date(__('m/d/Y H:i:s', 'wp-delicious-wishlist'), $time).'">'.$h_time.'</abbr></div>');
						$mywishlist .= '</li>';
					endforeach;
				}
			$mywishlist .= '</ul>';
		}

		// Start 1 star section
		if ($maxitems == "") $maxitems == "5";
		$wdw_rss = fetch_feed('http://feeds.delicious.com/v2/rss/'.$my_nickname.'/'.$my_tag_wishlist.'+'.$my_tag_low.'?count='.$maxitems);
		if(is_wp_error($wdw_rss)) { // Catch the error
			return __('There was a problem fetching your feed.', 'wp-delicious-wishlist');
		} else {
			$num_items = $wdw_rss->get_item_quantity($maxitems); 
			$wdw_items = $wdw_rss->get_items(0, $num_items);

			$mywishlist .= '<h3 id="low">';
				if ($my_title_low) {
					$mywishlist .= $my_title_low;
				} else {
					$mywishlist .= __('I like', 'wp-delicious-wishlist'); 
				}
			$mywishlist .= '</h3>';
			$mywishlist .= '<ul id="wishlist-low">';
				if (empty($wdw_items)) {
					$mywishlist .= '<li class="low-'.$my_icons.'">'.__('Nothing in this moment', 'wp-delicious-wishlist').'</li>';
				} else {
					foreach ( $wdw_items as $wdw_item ) :
						$mywishlist .= '<li class="low-'.$my_icons.'">
							<a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'
								.$wdw_item->get_title().
							'</a><br />
							<div class="wishlist-description">'.$wdw_item->get_description().'</div>';
							$time = strtotime($wdw_item->get_date());
							if ((abs( time() - $time)) < 86400)
								$h_time = sprintf(__('%s ago', 'wp-delicious-wishlist'), human_time_diff($time));
							else
								$h_time = date(__('m/d/Y', 'wp-delicious-wishlist'), $time);
							$mywishlist .= sprintf('%s','<div class="wishlist-timestamp"><abbr title="'.date(__('m/d/Y H:i:s', 'wp-delicious-wishlist'), $time).'">'.$h_time.'</abbr></div>');
						$mywishlist .= '</li>';
					endforeach;
				}
			$mywishlist .= '</ul>';
		}
	}

	// Add our wishlist to the end of the content	
	$the_content = $the_content.$mywishlist;
	
	// send the new version of $the_content back to WP
	return $the_content;
}
add_shortcode('my-delicious-wishlist', 'wp_delicious_wishlist');

add_action('admin_menu', 'wdw_menu');
function wdw_menu() {
	add_options_page(__('Delicious Wishlist for WordPress Options', 'wp-delicious-wishlist'), __('Delicious Wishlist', 'wp-delicious-wishlist'), 10, __FILE__, 'wdw_options_page');
}

// Load the options page
function wdw_options_page() {	
	include ('wdw-options.php');
}

// Add the stylesheet
add_action('wp_print_styles', 'wp_delicious_wishlist_stylesheets');
function wp_delicious_wishlist_stylesheets() {
	if(file_exists(TEMPLATEPATH.'/wdw.css')) {
		wp_enqueue_style('wp-delicious-wishlist', get_stylesheet_directory_uri().'/wdw.css', false, false, 'all');
	} else {
		wp_enqueue_style('wp-delicious-wishlist', plugins_url('delicious-wishlist-for-wordpress/wdw.css'), false, false, 'all');
	}
}

?>
