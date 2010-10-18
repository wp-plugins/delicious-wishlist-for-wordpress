<?php
/*
	Plugin Name: Delicious Wishlist for WordPress
	Description:  Publish a wishlist using your Delicious bookmarks
	Plugin URI: http://www.aldolat.it/wordpress/wordpress-plugins/delicious-wishlist-for-wordpress/
	Author: Aldo Latino
	Author URI: http://www.aldolat.it/
	Version: 2.2
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
    along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * On plugin activation (or on plugin update), it converts the old options set to only one row in the database
 * If the cache time is set to 1800 (from 0.4), the function will set it to 3600
 * A backlink to the author is activated
 *
 * @since 0.4
 * @since 0.5 Check of cache on plugin activation
 * @since 0.5 Add a backlink to the author (deactivable by the user)
 */
function wdw_conversion() {
	// Conversion of old settings before 0.4
	if ( get_option( 'wdw_delicious_nickname' ) ) {
		// These lines will be executed only in versions 0.3.5 and below
		$wdw_prefs = array();
		$wdw_prefs['wdw_delicious_nickname'] = get_option( 'wdw_delicious_nickname' );
			delete_option( 'wdw_delicious_nickname' );
		$wdw_prefs['wdw_delicious_tag_wishlist'] = get_option( 'wdw_delicious_tag_wishlist' );
			delete_option( 'wdw_delicious_tag_wishlist' );
		$wdw_prefs['wdw_delicious_title_high'] = get_option( 'wdw_delicious_title_high' );
			delete_option( 'wdw_delicious_title_high' );
		$wdw_prefs['wdw_delicious_tag_high'] = get_option( 'wdw_delicious_tag_high' );
			delete_option( 'wdw_delicious_tag_high' );
		$wdw_prefs['wdw_delicious_title_medium'] = get_option( 'wdw_delicious_title_medium' );
			delete_option( 'wdw_delicious_title_medium' );
		$wdw_prefs['wdw_delicious_tag_medium'] = get_option( 'wdw_delicious_tag_medium' );
			delete_option( 'wdw_delicious_tag_medium' );
		$wdw_prefs['wdw_delicious_title_low'] = get_option( 'wdw_delicious_title_low' );
			delete_option( 'wdw_delicious_title_low' );
		$wdw_prefs['wdw_delicious_tag_low'] = get_option( 'wdw_delicious_tag_low' );
			delete_option( 'wdw_delicious_tag_low' );
		$wdw_prefs['wdw_delicious_howmany'] = get_option( 'wdw_delicious_howmany' );
			delete_option( 'wdw_delicious_howmany' );
		$wdw_prefs['wdw_delicious_icons'] = get_option( 'wdw_delicious_icons' );
			delete_option( 'wdw_delicious_icons' );

		update_option( 'wdw_options', $wdw_prefs );
	}

	// Check of cache time and, in case it is empty or below 3600 seconds, let's change it
	// These lines will be executed on plugin activation only
	// @since 0.5
	$wdw_prefs = '';
	$wdw_prefs = array();
	$wdw_prefs = get_option( 'wdw_options' );
	if($wdw_prefs['wdw_delicious_cache'] == '' || $wdw_prefs['wdw_delicious_cache'] < 3600) {
		$wdw_prefs['wdw_delicious_cache'] = 3600;
	}

	// On activation let's create a backlink to the author
	// User can deactivate it via the option panel
	// These lines will be executed on plugin activation only
	// @since 0.5
	if(!$wdw_prefs['wdw_backlink']) {
		$wdw_prefs['wdw_backlink'] = 1;
	}
	update_option( 'wdw_options', $wdw_prefs );
}
register_activation_hook( __FILE__, 'wdw_conversion' );


/**
 * Preliminary actions
 *
 * @since 0.4
 */

function wdw_init() {
	// Create the options group
	register_setting( 'wdw-options-group', 'wdw_options', 'wdw_options_validate' );

	// Check if the main options have been setup by the user, otherwise print the admin notice
	$wdws = get_option( 'wdw_options' );
	if (
		!$wdws['wdw_delicious_nickname']     ||
		!$wdws['wdw_delicious_tag_wishlist'] ||
		!$wdws['wdw_delicious_tag_high']     ||
		!$wdws['wdw_delicious_tag_medium']   ||
		!$wdws['wdw_delicious_tag_low']
	) {
		add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>".sprintf( __( 'Delicious Wishlist for WordPress needs some settings on its <a href="%s">options</a> page.', 'wp-delicious-wishlist' ), admin_url( 'admin.php?page=delicious-wishlist-for-wordpress/wp-delicious-wishlist.php' ) )."</p></div>';" ) );
	}
}
add_action( 'admin_init', 'wdw_init' );


/**
 * Sanitize some options
 *
 * @since 0.4
 */

function wdw_options_validate( $input ) {
	// The items number must be integer and not greater than 100
	$input['wdw_delicious_howmany'] = intval($input['wdw_delicious_howmany']);
	if($input['wdw_delicious_howmany'] > 100) { $input['wdw_delicious_howmany'] = 100; }
	// Cache value must be integer and not minor than 3600
	$input['wdw_delicious_cache'] = intval($input['wdw_delicious_cache']);
	if($input['wdw_delicious_cache'] < 3600) { $input['wdw_delicious_cache'] = 3600; }
	return $input;
}


/**
 * Add the options page
 *
 * @since 0.1
 */

function wdw_menu() {
	add_menu_page( __( 'Delicious Wishlist for WordPress Options', 'wp-delicious-wishlist' ), __( 'Dlcs Wishlist', 'wp-delicious-wishlist' ), 'administrator', __FILE__, 'wdw_options_page', plugins_url( '/images/wdw_icon.png', __FILE__ ) );
}
add_action( 'admin_menu', 'wdw_menu' );


/**
 * Override the standard 12 hours Wordpress cache time and set it to user's choice or to 1 hour.
 *
 * @since 0.4
 * @param int @wdw_cache can be >= 3600
 */

function wdw_cache_time( $wdw_cache ) {
	$wdws = array();
	$wdws = get_option( 'wdw_options' );
	$wdw_cache = $wdws['wdw_delicious_cache'];
	if(!empty($wdw_cache)) {
		// Further (and probably useless in most cases) control on cache expiry time,
		// regardless of the value stored in database
		// Delicious is very sensitive to this matter!
		if($wdw_cache < 3600) {
			$wdw_cache = 3600;
		}
		return $wdw_cache;
	} else {
		return 3600;
	}
}


/**
 * The core function.
 *
 * @param $widget_maxitems contains the value od items to fetch for the widget
 * @param $widget_description defines if the description will appear in the widget or not
 * @since 0.1
 * @since 0.5 Alternative feeds
 * @since 0.6 Tag section
 */

function wp_delicious_wishlist( $widget_maxitems = '', $widget_description = false, $widget_page = '', $widget_count = true ) {

	// Let's collect some options from the plugin admin panel
	$wdws = array();
	$wdws = get_option( 'wdw_options' );
	$wdw_nickname     = $wdws['wdw_delicious_nickname'];
	$wdw_tag_wishlist = $wdws['wdw_delicious_tag_wishlist'];
	$wdw_title_high   = $wdws['wdw_delicious_title_high'];
	$wdw_tag_high     = $wdws['wdw_delicious_tag_high'];
	$wdw_title_medium = $wdws['wdw_delicious_title_medium'];
	$wdw_tag_medium   = $wdws['wdw_delicious_tag_medium'];
	$wdw_title_low    = $wdws['wdw_delicious_title_low'];
	$wdw_tag_low      = $wdws['wdw_delicious_tag_low'];
	$wdw_maxitems     = $wdws['wdw_delicious_howmany'];
	$wdw_truncate     = $wdws['wdw_delicious_truncate'];
	$wdw_more         = $wdws['wdw_delicious_more'];
	$wdw_pre_tags     = $wdws['wdw_delicious_pre_tags'];
	$wdw_pre_date     = $wdws['wdw_delicious_pre_date'];
	$wdw_icons        = $wdws['wdw_delicious_icons'];
	$wdw_cache        = $wdws['wdw_delicious_cache'];
	$wdw_altfeed_ht   = $wdws['wdw_delicious_alt_feed_ht'];
	$wdw_altfeed_mt   = $wdws['wdw_delicious_alt_feed_mt'];
	$wdw_altfeed_lt   = $wdws['wdw_delicious_alt_feed_lt'];
	$wdw_tags         = $wdws['wdw_tags'];
	$wdw_remove_tags  = $wdws['wdw_remove_tags'];
	$wdw_section      = $wdws['wdw_section'];
	$wdw_backlink     = $wdws['wdw_backlink'];
	$wdw_date         = $wdws['wdw_date'];
	$wdw_pre_section  = $wdws['wdw_pre_section'];

	// check if fields' values are blank
	if (
		empty( $wdw_nickname ) ||
		empty( $wdw_tag_wishlist ) ||
		empty( $wdw_tag_high ) ||
		empty( $wdw_tag_medium ) ||
		empty( $wdw_tag_low )
	) {

		// if blank, print this
		$wdw_wishlist  = '<p class="wdw_warning">';
		$wdw_wishlist .= sprintf( __( 'You have not properly configured the plugin. Please, setup it in the %1$splugin panel%2$s, filling in all the required fields.', 'wp-delicious-wishlist' ), '<a href="' . admin_url( 'admin.php?page=delicious-wishlist-for-wordpress/wp-delicious-wishlist.php' ) . '">', '</a>');
		$wdw_wishlist .= '</p>';

	} else { // if required options aren't blank, then execute the following code

		// If $wdw_maxitems has not been declared, then we setup it to 5 items to retrieve
		if ( empty( $wdw_maxitems ) ) {
			$wdw_maxitems = "5";
		}

		// Define my icons
		$wdw_icons == "stars" ? $wdw_icons = "stars" : $wdw_icons = "faces";

		//***** Start 3 stars section *****\\

		// Change the standard WordPress feed cache time
		add_filter( 'wp_feed_cache_transient_lifetime', 'wdw_cache_time' );

		// Include native WordPress feed fetching features
		include_once( ABSPATH . WPINC . '/feed.php' );

		// Retrieve the items from Delicious using nickname + tag wishlist + high tag + max number of items
		// If an alternative feed source is declared in the options panel, this source will be used insted
		if( $wdw_altfeed_ht ) {
			$wdw_rss = fetch_feed( $wdw_altfeed_ht );
		} else {
			$wdw_rss = fetch_feed( 'http://feeds.delicious.com/v2/rss/' . $wdw_nickname . '/' . $wdw_tag_wishlist . '+' . $wdw_tag_high . '?count=' . $wdw_maxitems );
		}

		// if there is a problem with the feed...
		if( is_wp_error( $wdw_rss ) ) {
			remove_filter( 'wp_feed_cache_transient_lifetime', 'wdw_cache_time' );
			// Tell me what's going wrong
			$wdw_wishlist = '<p>' . sprintf( __( 'There was a problem fetching your first feed. The problem is:%s', 'wp-delicious-wishlist' ), '<br /><strong>' . $wdw_rss->get_error_message() . '</strong>') . '</p>';
		// ... else execute
		} else {

			// Reset the standard WordPress feed cache time
			remove_filter( 'wp_feed_cache_transient_lifetime', 'wdw_cache_time' );

			// Figure out how many total items there are, but limit it to the $wdw_maxitems variable.
			$num_items = $wdw_rss->get_item_quantity( $wdw_maxitems );

			// Build an array of items
			if ( in_the_loop() ) {
				$wdw_items = $wdw_rss->get_items( 0, $num_items );
			} else {
				// If not in the loop (i.e. in the sidebar widget), take the number of items I set up for the widget
				$wdw_items = $wdw_rss->get_items( 0, $widget_maxitems );
			}

			$wdw_wishlist = '<h3 class="wishlist-title-high">';
				// if outside the loop (in the widget), add the number of total items for the section
				if ( ! in_the_loop() && $widget_count ) {
					$qty_for_widget = sprintf( _n( ' <span class="wdw-qty">(%s item)</span>', ' <span class="wdw-qty">(%s items)</span>', $num_items, 'wp-delicious-wishlist' ), $num_items );
				}
				if ( $wdw_title_high ) {
					$wdw_wishlist .= $wdw_title_high . $qty_for_widget;
				} else {
					$wdw_wishlist .= __( 'I need', 'wp-delicious-wishlist' ) . $qty_for_widget;
				}
			$wdw_wishlist .= '</h3>';
			$wdw_wishlist .= '<ul class="wishlist-high-list">';
				// If the first (high) section is blank, then let's write "Nothing in this moment."...
				if (empty($wdw_items)) {
					$wdw_wishlist .= '<li class="high-'.$wdw_icons.'">'.__('Nothing in this moment.', 'wp-delicious-wishlist').'</li>';
				} else {
					// ... else start the loop
					foreach ( $wdw_items as $wdw_item ) :
						$bookmark_url = $wdw_item->get_id();
						$wdw_wishlist .= '<li class="high-'.$wdw_icons.'">';
							$wdw_wishlist .= '<p class="wishlist-bookmark-title"><a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'.$wdw_item->get_title().'</a></p>';

							// if in the widget we do not want any description, we do not print it
							if ( in_the_loop() || $widget_description ) {
								$desc = $wdw_item->get_description();
								if ( $desc ) {
									if ( $wdw_truncate != 0 && strlen( $desc ) > $wdw_truncate ) {
										if ( !$wdw_more ) $wdw_more = __( 'Continue', 'wp-delicious-wishlist' );
										$desc = substr( $desc, 0, $wdw_truncate ) . '&hellip; <a href="' . $bookmark_url . '" title="' . sprintf( __('Continue reading &laquo;%s&raquo;', 'wp-delicious-wishlist'), $wdw_item->get_title() ) . ' on Delicious">' . $wdw_more . '</a>';
									}
									$wdw_wishlist .= '<p class="wishlist-description">'.$desc.'</p>';
								}
							}

							if ( $wdw_date ) {

								// Parse the date into Unix timestamp
								$unixDate  = strtotime($wdw_item->get_date());
								// This is the short date
								$briefDate = strftime(__('%m/%d/%Y', 'wp-delicious-wishlist'), $unixDate);
								// This is the long date displayed when the mouse overs on it
								// $longDate  = strftime(__('%A, %m/%d/%Y at %T', 'wp-delicious-wishlist'), $unixDate).' ('.sprintf(__('%s ago', 'wp-delicious-wishlist'), human_time_diff($unixDate)).')';
								// Let's build the final line
								// $wdw_wishlist .= '<div class="wishlist-timestamp"><abbr title="'.$longDate.'">'.$briefDate.'</abbr></div>';
								$wdw_wishlist .= '<p class="wishlist-timestamp">
									<span class="wishlist-pre-date">' . $wdw_pre_date . '</span>
									<a class="wishlist-date" href="' . $bookmark_url . '"
									title="' . sprintf( __('See the bookmark &laquo;%s&raquo; on Delicious', 'wp-delicious-wishlist'), $wdw_item->get_title() ) . '">'
									 . $briefDate . '</a></p>';
							}

							/*
							 * Tag section
							 *
							 * @since 0.6
							 */
							if($wdw_tags) {
								// Define $tags as an array and assign the content of get_item_tags
								$tags = ''; $tags = array(); $tags = $wdw_item->get_item_tags('', 'category');
								// If $tags has content
								if($tags) {
									// Make sure the new variable $mytags be empty
									$mytags = '';
									// for each content of the array...
									foreach($tags as $tag) {
										// assign to $mytags a comma and the subsection 'data' of $tag
										$mytags .= $tag['data'].',';
									}
									// Convert each value between two commas into an item of a new array.
									// 'explode' automagically converts them and converts $mytags into an array
									$mytags = explode(',',$mytags);
									// Now $mytags is an array of values, so let's create each final tag
									// If the user doesn't want to display the base wishlist tags, let's remove them
									if($wdw_remove_tags) {
										// Fill an array with the base Wishlist tags
										$tags_to_remove = array($wdw_tag_wishlist, $wdw_tag_high, $wdw_tag_medium, $wdw_tag_low);
										// Let's remove them from the tags to display
										$mytags = str_replace($tags_to_remove, '', $mytags);
									}
									// Take the domain to use it as the base url
									$myurl = $tag['attribs']['']['domain'];
									// Make sure that the new variable $all_tags be empty
									$all_tags = '';
									// This is the final loop for our Wishlist
									foreach($mytags as $mytag) {
										$all_tags .= '<a class="wishlist-tag" title="'
										. sprintf( __( 'See all my bookmarks tagged &laquo;%s&raquo;', 'wp-delicious-wishlist' ), $mytag ) .
										'" href="'.$myurl.$mytag.'">'.$mytag.'</a> ';
									}
									$wdw_wishlist .= '<p class="wishlist-tags"><span class="wishlist-pre-tags">'.$wdw_pre_tags.'</span> '.$all_tags.'</p>';
								}
							}

							/*
							 * Section for main wishlist Delicious tags
							 *
							 * @since 2.1
							 */

							if ( $wdw_section ) {
								$wdw_wishlist .= '<p class="wishlist-section">' . $wdw_pre_section . ' <a class="wishlist-section-link" href="http://www.delicious.com/' . $wdw_nickname . '/' . $wdw_tag_wishlist . '+' . $wdw_tag_high . '">' . $wdw_tag_wishlist . '+' . $wdw_tag_high . '</a></p>';
							}

						$wdw_wishlist .= '</li>';
					endforeach;
				}
			$wdw_wishlist .= '</ul>';
		}

		//***** Start 2 stars section *****\\
		add_filter('wp_feed_cache_transient_lifetime', 'wdw_cache_time');
		if($wdw_altfeed_mt) {
			$wdw_rss = fetch_feed($wdw_altfeed_mt);
		} else {
			$wdw_rss = fetch_feed('http://feeds.delicious.com/v2/rss/'.$wdw_nickname.'/'.$wdw_tag_wishlist.'+'.$wdw_tag_medium.'?count='.$wdw_maxitems);
		}
		if(is_wp_error($wdw_rss)) {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache_time');
			$wdw_wishlist .= '<p>' . sprintf( __( 'There was a problem fetching your second feed. The problem is:%s', 'wp-delicious-wishlist' ), '<br /><strong>' . $wdw_rss->get_error_message() . '</strong>') . '</p>';
		} else {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache_time');
			$num_items = $wdw_rss->get_item_quantity($wdw_maxitems);
			if ( in_the_loop() ) {
				$wdw_items = $wdw_rss->get_items( 0, $num_items );
			} else {
				$wdw_items = $wdw_rss->get_items( 0, $widget_maxitems );
			}

			$wdw_wishlist .= '<h3 class="wishlist-title-medium">';
				if ( ! in_the_loop() && $widget_count ) {
					$qty_for_widget = sprintf( _n( ' <span class="wdw-qty">(%s item)</span>', ' <span class="wdw-qty">(%s items)</span>', $num_items, 'wp-delicious-wishlist' ), $num_items );
				}
				if ($wdw_title_medium) {
					$wdw_wishlist .= $wdw_title_medium . $qty_for_widget;
				} else {
					$wdw_wishlist .= __('I\'d like', 'wp-delicious-wishlist') . $qty_for_widget;
				}
			$wdw_wishlist .= '</h3>';
			$wdw_wishlist .= '<ul class="wishlist-medium-list">';
				if (empty($wdw_items)) {
					$wdw_wishlist .= '<li class="medium-'.$wdw_icons.'">'.__('Nothing in this moment.', 'wp-delicious-wishlist').'</li>';
				} else {
					foreach ( $wdw_items as $wdw_item ) :
						$bookmark_url = $wdw_item->get_id();
						$wdw_wishlist .= '<li class="medium-'.$wdw_icons.'">';
							$wdw_wishlist .= '<p class="wishlist-bookmark-title"><a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'.$wdw_item->get_title().'</a></p>';

							if ( in_the_loop() || $widget_description ) {
								$desc = $wdw_item->get_description();
								if ( $desc ) {
									if ( $wdw_truncate != 0 && strlen( $desc ) > $wdw_truncate ) {
										if ( !$wdw_more ) $wdw_more = __( 'Continue', 'wp-delicious-wishlist' );
										$desc = substr( $desc, 0, $wdw_truncate ) . '&hellip; <a href="' . $bookmark_url . '" title="' . sprintf( __('Continue reading &laquo;%s&raquo;', 'wp-delicious-wishlist'), $wdw_item->get_title() ) . ' on Delicious">' . $wdw_more . '</a>';
									}
									$wdw_wishlist .= '<p class="wishlist-description">'.$desc.'</p>';
								}
							}

							if ( $wdw_date ) {
								$unixDate  = strtotime($wdw_item->get_date());
								$briefDate = strftime(__('%m/%d/%Y', 'wp-delicious-wishlist'), $unixDate);
								$wdw_wishlist .= '<p class="wishlist-timestamp">
									<span class="wishlist-pre-date">' . $wdw_pre_date . '</span>
									<a class="wishlist-date" href="' . $bookmark_url . '"
									title="' . sprintf( __('See the bookmark &laquo;%s&raquo; on Delicious', 'wp-delicious-wishlist'), $wdw_item->get_title() ) . '">'
									 . $briefDate . '</a></p>';
							}

							if($wdw_tags) {
								$tags = ''; $tags = array(); $tags = $wdw_item->get_item_tags('', 'category');
								if($tags) {
									$mytags = '';
									foreach($tags as $tag) {
										$mytags .= $tag['data'].',';
									}
									$mytags = explode(',',$mytags);
									if($wdw_remove_tags) {
										$tags_to_remove = array($wdw_tag_wishlist, $wdw_tag_high, $wdw_tag_medium, $wdw_tag_low);
										$mytags = str_replace($tags_to_remove, '', $mytags);
									}
									$myurl = $tag['attribs']['']['domain'];
									$all_tags = '';
									foreach($mytags as $mytag) {
										$all_tags .= '<a class="wishlist-tag" title="'
										. sprintf( __( 'See all my bookmarks tagged &laquo;%s&raquo;', 'wp-delicious-wishlist' ), $mytag ) .
										'" href="'.$myurl.$mytag.'">'.$mytag.'</a> ';
									}
									$wdw_wishlist .= '<p class="wishlist-tags"><span class="wishlist-pre-tags">'.$wdw_pre_tags.'</span> '.$all_tags.'</p>';
								}
							}

							if ( $wdw_section ) {
								$wdw_wishlist .= '<p class="wishlist-section">' . $wdw_pre_section . ' <a class="wishlist-section-link" href="http://www.delicious.com/' . $wdw_nickname . '/' . $wdw_tag_wishlist . '+' . $wdw_tag_medium . '">' . $wdw_tag_wishlist . '+' . $wdw_tag_medium . '</a></p>';
							}

						$wdw_wishlist .= '</li>';
					endforeach;
				}
			$wdw_wishlist .= '</ul>';
		}

		//***** Start 1 stars section *****\\
		add_filter('wp_feed_cache_transient_lifetime', 'wdw_cache_time');
		if($wdw_altfeed_lt) {
			$wdw_rss = fetch_feed($wdw_altfeed_lt);
		} else {
			$wdw_rss = fetch_feed('http://feeds.delicious.com/v2/rss/'.$wdw_nickname.'/'.$wdw_tag_wishlist.'+'.$wdw_tag_low.'?count='.$wdw_maxitems);
		}
		if(is_wp_error($wdw_rss)) {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache_time');
			$wdw_wishlist .= '<p>' . sprintf( __( 'There was a problem fetching your third feed. The problem is:%s', 'wp-delicious-wishlist' ), '<br /><strong>' . $wdw_rss->get_error_message() . '</strong>') . '</p>';
		} else {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache_time');
			$num_items = $wdw_rss->get_item_quantity($wdw_maxitems);
			if ( in_the_loop() ) {
				$wdw_items = $wdw_rss->get_items( 0, $num_items );
			} else {
				$wdw_items = $wdw_rss->get_items( 0, $widget_maxitems );
			}

			$wdw_wishlist .= '<h3 class="wishlist-title-low">';
				if ( ! in_the_loop() && $widget_count ) {
					$qty_for_widget = sprintf( _n( ' <span class="wdw-qty">(%s item)</span>', ' <span class="wdw-qty">(%s items)</span>', $num_items, 'wp-delicious-wishlist' ), $num_items );
				}
				if ($wdw_title_low) {
					$wdw_wishlist .= $wdw_title_low . $qty_for_widget;
				} else {
					$wdw_wishlist .= __('I like', 'wp-delicious-wishlist') . $qty_for_widget;
				}
			$wdw_wishlist .= '</h3>';
			$wdw_wishlist .= '<ul class="wishlist-low-list">';
				if (empty($wdw_items)) {
					$wdw_wishlist .= '<li class="low-'.$wdw_icons.'">'.__('Nothing in this moment.', 'wp-delicious-wishlist').'</li>';
				} else {
					foreach ( $wdw_items as $wdw_item ) :
						$bookmark_url = $wdw_item->get_id();
						$wdw_wishlist .= '<li class="low-'.$wdw_icons.'">';
							$wdw_wishlist .= '<p class="wishlist-bookmark-title"><a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'.$wdw_item->get_title().'</a></p>';

							if ( in_the_loop() || $widget_description ) {
								$desc = $wdw_item->get_description();
								if ( $desc ) {
									if ( $wdw_truncate != 0 && strlen( $desc ) > $wdw_truncate ) {
										if ( !$wdw_more ) $wdw_more = __( 'Continue', 'wp-delicious-wishlist' );
										$desc = substr( $desc, 0, $wdw_truncate ) . '&hellip; <a href="' . $bookmark_url . '" title="' . sprintf( __('Continue reading &laquo;%s&raquo;', 'wp-delicious-wishlist'), $wdw_item->get_title() ) . ' on Delicious">' . $wdw_more . '</a>';
									}
									$wdw_wishlist .= '<p class="wishlist-description">'.$desc.'</p>';
								}
							}

							if ( $wdw_date ) {
								$unixDate  = strtotime($wdw_item->get_date());
								$briefDate = strftime(__('%m/%d/%Y', 'wp-delicious-wishlist'), $unixDate);
								$wdw_wishlist .= '<p class="wishlist-timestamp">
									<span class="wishlist-pre-date">' . $wdw_pre_date . '</span>
									<a class="wishlist-date" href="' . $bookmark_url . '"
									title="' . sprintf( __('See the bookmark &laquo;%s&raquo; on Delicious', 'wp-delicious-wishlist'), $wdw_item->get_title() ) . '">'
									 . $briefDate . '</a></p>';
							}

							if($wdw_tags) {
								$tags = ''; $tags = array(); $tags = $wdw_item->get_item_tags('', 'category');
								if($tags) {
									$mytags = '';
									foreach($tags as $tag) {
										$mytags .= $tag['data'].',';
									}
									$mytags = explode(',',$mytags);
									if($wdw_remove_tags) {
										$tags_to_remove = array($wdw_tag_wishlist, $wdw_tag_high, $wdw_tag_medium, $wdw_tag_low);
										$mytags = str_replace($tags_to_remove, '', $mytags);
									}
									$myurl = $tag['attribs']['']['domain'];
									$all_tags = '';
									foreach($mytags as $mytag) {
										$all_tags .= '<a class="wishlist-tag" title="'
										. sprintf( __( 'See all my bookmarks tagged &laquo;%s&raquo;', 'wp-delicious-wishlist' ), $mytag ) .
										'" href="'.$myurl.$mytag.'">'.$mytag.'</a> ';
									}
									$wdw_wishlist .= '<p class="wishlist-tags"><span class="wishlist-pre-tags">'.$wdw_pre_tags.'</span> '.$all_tags.'</p>';
								}
							}

							if ( $wdw_section ) {
								$wdw_wishlist .= '<p class="wishlist-section">' . $wdw_pre_section . ' <a class="wishlist-section-link" href="http://www.delicious.com/' . $wdw_nickname . '/' . $wdw_tag_wishlist . '+' . $wdw_tag_low . '">' . $wdw_tag_wishlist . '+' . $wdw_tag_low . '</a></p>';
							}

						$wdw_wishlist .= '</li>';
					endforeach;
				}
			$wdw_wishlist .= '</ul>';
		}

		if ( ! in_the_loop() && $widget_page ) { // what a useful function! I twittered here: http://bit.ly/cImZne :)
			if ( ! is_page( $widget_page ) ) {
				$wdw_page_id   = get_page_by_title( $widget_page );
				$wdw_page_link = get_page_link( $wdw_page_id->ID );
				$wdw_wishlist .= '<p class="wdw-page">';
				$wdw_wishlist .= sprintf( __( 'Check out %1$smy complete list%2$s.', 'wp-delicious-wishlist' ), '<a href="'.$wdw_page_link.'">', '</a>' );
				$wdw_wishlist .= '</p>';
			}
		}

		if($wdw_backlink) {
			$wdw_wishlist .= '<p class="wdw_backlink">'.__('Created using','wp-delicious-wishlist').' <a href="http://wordpress.org/extend/plugins/delicious-wishlist-for-wordpress/">'.__('Delicious Wishlist for WordPress', 'wp-delicious-wishlist').'</a>.</p>';
		}

	}

	// Return the complete wishlist
	return $wdw_wishlist;
}
add_shortcode('my-delicious-wishlist', 'wp_delicious_wishlist');


/**
 * The Widget
 *
 * Add the widget for Delicious Wishlist for WordPress
 *
 * @since 2.0
 */

add_action( 'widgets_init', 'wdw_load_widget' );

function wdw_load_widget() {
	register_widget( 'WDW_Widget' );
}

class WDW_Widget extends WP_Widget {
	function WDW_Widget() {
		$widget_ops = array(
			'classname' => 'wdw_widget',
			'description' => __('Your personal wishlist proudly powered by Delicious', 'wp-delicious-wishlist')
		);
		$this->WP_Widget('wdw-widget', __('Delicious Wishlist Widget', 'wp-delicious-wishlist'), $widget_ops);
	}

	function widget($args, $instance) {
		extract( $args );

		$title = apply_filters('widget_title', $instance['title']);
		$widget_maxitems = $instance['maxitems'];
		$widget_description = isset( $instance['desc'] ) ? $instance['desc'] : false;
		$widget_page = $instance['page'];
		$widget_count = isset( $instance['count'] ) ? $instance['count'] : false;

		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;
		echo wp_delicious_wishlist( $widget_maxitems, $widget_description, $widget_page, $widget_count );
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['maxitems'] = strip_tags($new_instance['maxitems']);
		$instance['desc'] = $new_instance['desc'];
		$instance['page'] = strip_tags($new_instance['page']);
		$instance['count'] = $new_instance['count'];
		return $instance;
	}

	function form($instance) {
		$defaults = array(
			'title' => __( 'My Wishlist', 'wp-delicious-wishlist' ),
			'maxitems' => '1',
			'desc' => false,
			'page' => '',
			'count'=> true
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e('Title:', 'wp-delicious-wishlist'); ?>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('maxitems'); ?>">
					<?php _e('Max number of items per section:', 'wp-delicious-wishlist'); ?>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id('maxitems'); ?>" name="<?php echo $this->get_field_name('maxitems'); ?>" type="text" value="<?php echo $instance['maxitems']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('page'); ?>">
					<?php _e('Insert the title of your Wishlist page:', 'wp-delicious-wishlist'); ?>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>" type="text" value="<?php echo $instance['page']; ?>" />
			</p>
			<p>
				<input class="checkbox" type="checkbox" <?php if( $instance['desc'] ) { echo 'checked="checked"'; } ?> value="1" id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'desc' ); ?>">
					<?php _e('Display description?', 'wp-delicious-wishlist'); ?>
				</label>
			</p>
			<p>
				<input class="checkbox" type="checkbox" <?php if( $instance['count'] ) { echo 'checked="checked"'; } ?> value="2" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'count' ); ?>">
					<?php _e('Display the total number of items in titles?', 'wp-delicious-wishlist'); ?>
				</label>
			</p>
		<?php
	}
}


/**
 * Load the options page
 *
 * @since 0.1
 * @since 0.5 Alternative feeds
 * @since 1.0 Use or not the plugin CSS file
 */

function wdw_options_page() { ?>

	<div class="wrap">
		<h2><?php _e('Delicious Wishlist for WordPress Options', 'wp-delicious-wishlist'); ?></h2>

		<p>
			<?php printf( __( '<strong>[*] = Required fields.</strong> - The User Guide is %1$sbelow%2$s.', 'wp-delicious-wishlist' ), '<a href="#user-guide">', '</a>' ); ?>
		</p>

		<div class="clear" id="poststuff" style="max-width: 800px;">

			<div style="float: right; width: 25%">

				<div class="postbox">
					<h3 style="cursor: default;"><?php _e('Support me!', 'wp-delicious-wishlist'); ?></h3>
					<div class="inside">
						<p><?php _e('If you find this plugin useful, please help me continuing to develop it.', 'wp-delicious-wishlist'); ?></p>
						<p><?php _e('The easiest way to do this is to make a donation via PayPal, a well-known and secure method to make me happy!', 'wp-delicious-wishlist'); ?></p>
						<form style="text-align: center" action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="11189816">
							<input type="image" src="https://www.paypal.com/it_IT/IT/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="<?php _e('PayPal - The safer, easier way to pay online!', 'wp-delicious-wishlist'); ?>">
							<img alt="" border="0" src="https://www.paypal.com/it_IT/i/scr/pixel.gif" width="1" height="1">
						</form>
						<p style="text-align: center;">
							<a style="font-weight: bold;" href="http://www.aldolat.it/info/wishlist/">
								<?php _e('If you want, you may also visit my wishlist.', 'wp-delicious-wishlist').' &rarr;'; ?>
							</a>
						</p>
					</div>
				</div>

				<div class="postbox">
					<h3 style="cursor: default;"><?php _e('Help & feedback', 'wp-delicious-wishlist'); ?></h3>
					<div class="inside">
						<p>
							<?php _e('<strong>Need help?</strong> <a href="http://www.aldolat.it/support/forum/51">Visit my forums</a> (you can write in English too)', 'wp-delicious-wishlist'); ?>
						</p>
						<p>
							<?php //printf( __('<strong>Want to give a feedback?</strong> Come on %1$smy blog%2$s and drop a comment. It will be very appreciated.', 'wp-delicious-wishlist'), '<a href="http://www.aldolat.it/wordpress/wordpress-plugins/delicious-wishlist-for-wordpress/">', '</a>' ); ?>
							<?php _e('<strong>Want to give a feedback?</strong> Come on <a href="http://www.aldolat.it/wordpress/wordpress-plugins/delicious-wishlist-for-wordpress/">my blog</a> and drop a comment. It will be very appreciated.', 'wp-delicious-wishlist'); ?>
						</p>
					</div>
				</div>

				<div class="postbox">
					<h3 style="cursor: default;"><?php _e('Uninstall info', 'wp-delicious-wishlist'); ?></h3>
					<div class="inside">
						<p>
							<?php _e('If you decide to uninstall this plugin, it will delete any options it created. No further user action is required. Deactivating the plugin will not erase any data.', 'wp-delicious-wishlist'); ?>
						</p>
					</div>
				</div>

				<div class="postbox">
					<h3 style="cursor: default;"><?php _e('Credits', 'wp-delicious-wishlist'); ?></h3>
					<div class="inside">
						<p>
							<?php printf( __('My thanks go to all people who contributed in revisioning and helping me in any form, and in particular to %1$s and to %2$s for their great idea behind this work.', 'wp-delicious-wishlist'), '<a href="http://www.nicoladagostino.net/">Nicola D\'Agostino</a>', '<a href="http://suzupearl.com/">Barbara Arianna Ripepi</a>' ); ?>
						</p>
					</div>
				</div>

			</div>
			<!-- close container right -->

			<div style="width: 70%">

				<form method="post" action="options.php">
					<div class="postbox">
						<h3 style="cursor: default;"><?php _e('Base Settings', 'wp-delicious-wishlist'); ?></h3>
						<div class="inside">
							<?php settings_fields('wdw-options-group'); $wdws = array(); $wdws = get_option('wdw_options'); ?>
							<table class="widefat" style="clear: none;">
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Delicious Nickname [*]', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_nickname]" value="<?php echo $wdws['wdw_delicious_nickname']; ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Delicious Wishlist Tag [*]', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_tag_wishlist]" value="<?php echo $wdws['wdw_delicious_tag_wishlist']; ?>">
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Title for High Tag section', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_title_high]" value="<?php echo $wdws['wdw_delicious_title_high']; ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Delicious High Tag [*]', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_tag_high]" value="<?php echo $wdws['wdw_delicious_tag_high']; ?>">
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Title for Medium Tag section', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_title_medium]" value="<?php echo $wdws['wdw_delicious_title_medium']; ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Delicious Medium Tag [*]', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_tag_medium]" value="<?php echo $wdws['wdw_delicious_tag_medium']; ?>">
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Title for Low Tag section', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_title_low]" value="<?php echo $wdws['wdw_delicious_title_low']; ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Delicious Low Tag [*]', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_tag_low]" value="<?php echo $wdws['wdw_delicious_tag_low']; ?>">
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('How many items (max 100)', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_howmany]" value="<?php echo $wdws['wdw_delicious_howmany']; ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Truncate description (0 = do not truncate)', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_truncate]" value="<?php echo $wdws['wdw_delicious_truncate']; ?>">
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Read More text', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_more]" value="<?php echo $wdws['wdw_delicious_more']; ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Icons style', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<select name="wdw_options[wdw_delicious_icons]" >
											<?php $my_wdw_style = $wdws['wdw_delicious_icons']; ?>
											<option <?php selected('stars', $my_wdw_style); ?> value="stars">
												<?php _e('Stars', 'wp-delicious-wishlist'); ?>
											</option>
											<option <?php selected('faces', $my_wdw_style); ?> value="faces">
												<?php _e('Faces', 'wp-delicious-wishlist'); ?>
											</option>
										</select>
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Feed cache time (in seconds, min 3600)', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_cache]" value="<?php echo $wdws['wdw_delicious_cache']; ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Display date', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="checkbox" value="1" name="wdw_options[wdw_date]" id="wdw_tags"<?php if($wdws['wdw_date']) { echo ' checked="true"'; } ?> />
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Text before date', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_pre_date]" value="<?php echo $wdws['wdw_delicious_pre_date']; ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Display tags...', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="checkbox" value="1" name="wdw_options[wdw_tags]" id="wdw_tags"<?php if($wdws['wdw_tags']) { echo ' checked="true"'; } ?> />
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('... but do not display my Wishlist tags', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="checkbox" value="1" name="wdw_options[wdw_remove_tags]" id="wdw_remove_tags"<?php if($wdws['wdw_remove_tags']) { echo ' checked="true"'; } ?> />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Text before tags', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_delicious_pre_tags]" value="<?php echo $wdws['wdw_delicious_pre_tags']; ?>">
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Display Section', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="checkbox" value="1" name="wdw_options[wdw_section]" id="wdw_css"<?php if($wdws['wdw_section']) { echo ' checked="true"'; } ?> />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Text before section', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="text" name="wdw_options[wdw_pre_section]" value="<?php echo $wdws['wdw_pre_section']; ?>">
									</td>
								</tr>
								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Use plugin\'s CSS', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="checkbox" value="1" name="wdw_options[wdw_css]" id="wdw_css"<?php if($wdws['wdw_css']) { echo ' checked="true"'; } ?> />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<?php _e('Link to the author', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input type="checkbox" value="1" name="wdw_options[wdw_backlink]" id="wdw_backlink"<?php if($wdws['wdw_backlink']) { echo ' checked="true"'; } ?> />
									</td>
								</tr>
							</table>

							<p class="submit" style="padding: 0.5em 0;">
								<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-delicious-wishlist'); ?>" />
							</p>

						</div>
					</div>
					<!-- close postbox base settings -->

					<div class="postbox">
						<h3 style="cursor: default;"><?php _e('Alternative feed source', 'wp-delicious-wishlist'); ?></h3>
						<div class="inside">
							<p>
								<?php _e('If you experience problems in fetching your feeds directly from Delicious, you can use another service that fetches your feeds for you (such as FeedBurner or Yahoo! Pipes or other services). Enter here the alternative feed URLs, that this plugin will use instead of Delicious\' feeds.', 'wp-delicious-wishlist'); ?>
							</p>

							<table class="widefat" style="clear: none;">

								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Feed for High Tag section:', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input size="40" type="text" name="wdw_options[wdw_delicious_alt_feed_ht]" value="<?php echo $wdws['wdw_delicious_alt_feed_ht']; ?>">
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<?php _e('Feed for Medium Tag section:', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input size="40" type="text" name="wdw_options[wdw_delicious_alt_feed_mt]" value="<?php echo $wdws['wdw_delicious_alt_feed_mt']; ?>">
									</td>
								</tr>

								<tr valign="top" class="alternate">
									<th scope="row">
										<?php _e('Feed for Low Tag section:', 'wp-delicious-wishlist'); ?>
									</th>
									<td>
										<input size="40" type="text" name="wdw_options[wdw_delicious_alt_feed_lt]" value="<?php echo $wdws['wdw_delicious_alt_feed_lt']; ?>">
									</td>
								</tr>

							</table>

							<p class="submit" style="padding: 0.5em 0;">
								<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-delicious-wishlist'); ?>" />
							</p>
						</div>
					</div>
					<!-- close postbox alternative feed source -->

				</form>
				<!-- close form -->

				<div class="postbox">
					<h3 style="cursor: default;" id="user-guide"><?php _e('User Guide', 'wp-delicious-wishlist'); ?></h3>
					<div class="inside">
						<h4><?php _e('Installation', 'wp-delicious-wishlist'); ?></h4>

						<p>
							<?php _e('This plugin allows you to publish in your blog a wishlist using your Delicious bookmarks. In order to make this, when you visit a web page with something you like, tag that page with two different bookmarks: <code>wishlist</code> and, if it is very important, <code>***</code> (three stars). Then, when you visit a page with something less important, you could use <code>wishlist</code> and <code>**</code> (two stars), and finally for a page with something even less important, you could use <code>wishlist</code> and <code>*</code> (one star). It\'s not mandatory to use these exact tags: you can choose your own tags, but consider that you have to bookmark a page with at least two different tags: one general to collect all your bookmarks relative to your wishlist, and another to mark that page depending on the importance of the stuff for you.<br /><br /> When you are done with an item (you have bought it or someone gave it to you as a gift), you can edit that bookmark on Delicious and remove the star(s), leaving only the main tag (e.g., <code>wishlist</code>), so you can maintain in Delicious an archive of all desired items.<br /><br /> To start, fill in the fields in the form above.', 'wp-delicious-wishlist'); ?>
							<br />
							<?php _e('<strong>All required fields are marked with an asterisk [*].</strong> The values are not case sensitive.', 'wp-delicious-wishlist'); ?>
						</p>

						<p>
							<?php printf( __( 'When you are done filling those fields, clic on the "Save Changes" button, create a new page, and give it a title you want. In the body of the page, paste the following shortcode: %s', 'wp-delicious-wishlist'), '<br /><br /><code style="font-size: 1.3em;">[my-delicious-wishlist]</code><br /><br />' ); ?>
							<?php _e('You can add some text before and/or after the shortcode. Publish the page and visit it on your blog. You are done!', 'wp-delicious-wishlist'); ?>
						</p>

						<h4><?php _e('Changing the style of the Wishlist page', 'wp-delicious-wishlist'); ?></h4>

						<p>
							<?php _e('The page is stylized using the css file included in the plugin directory. If you want to restyle the page, you can put a css file in the root directory of the theme you are using, create your styles, and name it <code>wdw.css</code>. All future versions of this plugin will load only your own css file.', 'wp-delicious-wishlist'); ?>
						</p>

					</div>

				</div>

			</div>
			<!-- close container left -->

		</div>
		<!-- close poststuff -->

	</div>
	<!-- close wrap -->

<?php }


/**
 * Add the stylesheet
 *
 * @since 0.1
 * @since 1.0 The plugin can avoid the use of CSS. The user will stylize the output via theme's CSS.
 */

function wp_delicious_wishlist_stylesheets() {
	$wdws = array();
	$wdws = get_option( 'wdw_options' );
	$wdw_css = $wdws['wdw_css'];
	if( $wdw_css ) {
		if( file_exists( TEMPLATEPATH.'/wdw.css' ) ) {
			wp_enqueue_style( 'wp-delicious-wishlist', get_stylesheet_directory_uri() . '/wdw.css', false, false, 'all' );
		} else {
			wp_enqueue_style( 'wp-delicious-wishlist', plugins_url( 'delicious-wishlist-for-wordpress/wdw.css' ), false, false, 'all' );
		}
	}
}
add_action( 'wp_print_styles', 'wp_delicious_wishlist_stylesheets' );


/**
 * Make plugin available for i18n
 * Translations must be archived in the /languages/ directory
 *
 * @since 0.1
 */

setlocale(LC_ALL, get_locale().'.UTF8');
load_plugin_textdomain('wp-delicious-wishlist', false, 'delicious-wishlist-for-wordpress/languages');


/***********************************************************************
 *                            CODE IS POETRY
 **********************************************************************/
