<?php
/*
	Plugin Name: Delicious Wishlist for WordPress
	Description:  Publish a wishlist using your Delicious bookmarks
	Plugin URI: http://www.aldolat.it/wordpress/wordpress-plugins/delicious-wishlist-for-wordpress/
	Author: Aldo Latino
	Author URI: http://www.aldolat.it/
	Version: 0.4
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


/*
 * TODO : Add widget for sidebar
 * TODO : Add uninstall function
 * TODO : Add the possibility to fetch the feed from an alternative source
 */

// Only on activation (or un plugin update) this function converts old options set to one row in the database
function wdw_conversion() {
	if (get_option('wdw_delicious_nickname')) {
		$wdw_prefs = array();
		$wdw_prefs['wdw_delicious_nickname'] = get_option('wdw_delicious_nickname');
			delete_option('wdw_delicious_nickname');
		$wdw_prefs['wdw_delicious_tag_wishlist'] = get_option('wdw_delicious_tag_wishlist');
			delete_option('wdw_delicious_tag_wishlist');
		$wdw_prefs['wdw_delicious_title_high'] = get_option('wdw_delicious_title_high');
			delete_option('wdw_delicious_title_high');
		$wdw_prefs['wdw_delicious_tag_high'] = get_option('wdw_delicious_tag_high');
			delete_option('wdw_delicious_tag_high');
		$wdw_prefs['wdw_delicious_title_medium'] = get_option('wdw_delicious_title_medium');
			delete_option('wdw_delicious_title_medium');
		$wdw_prefs['wdw_delicious_tag_medium'] = get_option('wdw_delicious_tag_medium');
			delete_option('wdw_delicious_tag_medium');
		$wdw_prefs['wdw_delicious_title_low'] = get_option('wdw_delicious_title_low');
			delete_option('wdw_delicious_title_low');
		$wdw_prefs['wdw_delicious_tag_low'] = get_option('wdw_delicious_tag_low');
			delete_option('wdw_delicious_tag_low');
		$wdw_prefs['wdw_delicious_howmany'] = get_option('wdw_delicious_howmany');
			delete_option('wdw_delicious_howmany');
		$wdw_prefs['wdw_delicious_icons'] = get_option('wdw_delicious_icons');
			delete_option('wdw_delicious_icons');

		update_option('wdw_options',$wdw_prefs);
	}
}
register_activation_hook(__FILE__, 'wdw_conversion');

// Preliminary functions
function wdw_init() {
	register_setting('wdw-options-group', 'wdw_options', 'wdw_options_validate');
}
add_action( 'admin_init', 'wdw_init' );

// Sanitize of some options
function wdw_options_validate($input) {
	// The items number must be integer and not greater than 100
	$input['wdw_delicious_howmany'] = intval($input['wdw_delicious_howmany']);
	if($input['wdw_delicious_howmany'] > 100) { $input['wdw_delicious_howmany'] = 100; }
	// Cache value must be integer and not minor than 1800
	$input['wdw_delicious_cache'] = intval($input['wdw_delicious_cache']);
	if($input['wdw_delicious_cache'] < 1800) { $input['wdw_delicious_cache'] = 1800; }
	return $input;
}

// Add the options page
function wdw_menu() {
	add_menu_page(__('Delicious Wishlist for WordPress Options', 'wp-delicious-wishlist'), __('Delicious Wishlist', 'wp-delicious-wishlist'), 'administrator', __FILE__, 'wdw_options_page', plugins_url('/images/wdw_icon.png', __FILE__));
}
add_action('admin_menu', 'wdw_menu');

// Override the standard 12 hours Wordpress cache time and set it to user's choice or to 1 hour.
function wdw_cache($age) {
	if(!empty($wdw_cache)) {
		// Further control on cache time: Delicious is very sensitive to this matter
		if($wdw_cache < 1800) { $wdw_cache = 1800; }
		return $wdw_cache;
	} else {
		return 3600;
	}
}

// Start the main function
function wp_delicious_wishlist() {

	// Let's collect some options from the plugin admin panel
	$wdws = array();
	$wdws = get_option('wdw_options');
	$wdw_nickname     = $wdws['wdw_delicious_nickname'];
	$wdw_tag_wishlist = $wdws['wdw_delicious_tag_wishlist'];
	$wdw_title_high   = $wdws['wdw_delicious_title_high'];
	$wdw_tag_high     = $wdws['wdw_delicious_tag_high'];
	$wdw_title_medium = $wdws['wdw_delicious_title_medium'];
	$wdw_tag_medium   = $wdws['wdw_delicious_tag_medium'];
	$wdw_title_low    = $wdws['wdw_delicious_title_low'];
	$wdw_tag_low      = $wdws['wdw_delicious_tag_low'];
	$wdw_maxitems     = $wdws['wdw_delicious_howmany'];
	$wdw_icons        = $wdws['wdw_delicious_icons'];
	$wdw_cache        = $wdws['wdw_delicious_cache'];

	// check if fields' values are blank
	if (
		empty($wdw_nickname) ||
		empty($wdw_tag_wishlist) ||
		empty($wdw_tag_high) ||
		empty($wdw_tag_medium) ||
		empty($wdw_tag_low)
	) {

		// if blank, print this
		$wdw_wishlist = '<p class="wdw_warning">'.
					  __('You have not properly configured the plugin. Please, setup it in the plugin panel admin, filling in all the required fields.', 'wp-delicious-wishlist').
					  '</p>';

	} else { // if required options aren't blank, then execute the following code

		// If $wdw_maxitems has not been declared, then we setup it to 5 items to retrieve
		if (empty($wdw_maxitems)) { $wdw_maxitems = "5"; }

		// Define my icons
		if($wdw_icons == "stars") { $wdw_icons = "stars"; } else { $wdw_icons = "faces"; }

		//***** Start 3 stars section *****\\

		// Change the standard WordPress feed cache time
		add_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');

		// Include native WordPress feed fetching features
		include_once(ABSPATH . WPINC . '/feed.php');

		// Retrieve the items from Delicious using nickname + tag wishlist + high tag + max number of items
		$wdw_rss = fetch_feed('http://feeds.delicious.com/v2/rss/'.$wdw_nickname.'/'.$wdw_tag_wishlist.'+'.$wdw_tag_high.'?count='.$wdw_maxitems);

		// if there is a problem with the feed...
		if(is_wp_error($wdw_rss)) {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');
			return __('There was a problem fetching your feed.', 'wp-delicious-wishlist');
		// ... else execute
		} else {

			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');

			$num_items = $wdw_rss->get_item_quantity($wdw_maxitems);

			// Build an array of items
			$wdw_items = $wdw_rss->get_items(0, $num_items);

			$wdw_wishlist = '<h3 id="high">';
				if ($wdw_title_high) {
					$wdw_wishlist .= $wdw_title_high;
				} else {
					$wdw_wishlist .= __('I need', 'wp-delicious-wishlist');
				}
			$wdw_wishlist .= '</h3>';
			$wdw_wishlist .= '<ul id="wishlist-high">';
				// If the first (high) section is blank, then let's write "Nothing in this moment"...
				if (empty($wdw_items)) {
					$wdw_wishlist .= '<li class="high-'.$wdw_icons.'">'.__('Nothing in this moment', 'wp-delicious-wishlist').'</li>';
				} else {
					// ... else start the loop
					foreach ( $wdw_items as $wdw_item ) :
						$wdw_wishlist .= '<li class="high-'.$wdw_icons.'">
							<a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'
								.$wdw_item->get_title().
							'</a><br />
							<div class="wishlist-description">'.$wdw_item->get_description().'</div>';
							// Parse the date into Unix timestamp
							$unixDate  = strtotime($wdw_item->get_date());
							$briefDate = strftime(__('%m/%d/%Y', 'wp-delicious-wishlist'), $unixDate);
							$longDate  = strftime(__('%A, %m/%d/%Y at %T', 'wp-delicious-wishlist'), $unixDate).' ('.sprintf(__('%s ago', 'wp-delicious-wishlist'), human_time_diff($unixDate)).')';
							$wdw_wishlist .= '<div class="wishlist-timestamp"><abbr title="'.$longDate.'">'.$briefDate.'</abbr></div>';
						$wdw_wishlist .= '</li>';
					endforeach;
				}
			$wdw_wishlist .= '</ul>';
		}

		//***** Start 2 stars section *****\\
		add_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');
		$wdw_rss = fetch_feed('http://feeds.delicious.com/v2/rss/'.$wdw_nickname.'/'.$wdw_tag_wishlist.'+'.$wdw_tag_medium.'?count='.$wdw_maxitems);
		if(is_wp_error($wdw_rss)) {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');
			return __('There was a problem fetching your feed.', 'wp-delicious-wishlist');
		} else {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');
			$num_items = $wdw_rss->get_item_quantity($wdw_maxitems);
			$wdw_items = $wdw_rss->get_items(0, $num_items);

			$wdw_wishlist .= '<h3 id="medium">';
				if ($wdw_title_medium) {
					$wdw_wishlist .= $wdw_title_medium;
				} else {
					$wdw_wishlist .= __('I\'d like', 'wp-delicious-wishlist');
				}
			$wdw_wishlist .= '</h3>';
			$wdw_wishlist .= '<ul id="wishlist-medium">';
				if (empty($wdw_items)) {
					$wdw_wishlist .= '<li class="medium-'.$wdw_icons.'">'.__('Nothing in this moment', 'wp-delicious-wishlist').'</li>';
				} else {
					foreach ( $wdw_items as $wdw_item ) :
						$wdw_wishlist .= '<li class="medium-'.$wdw_icons.'">
							<a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'
								.$wdw_item->get_title().
							'</a><br />
							<div class="wishlist-description">'.$wdw_item->get_description().'</div>';
							$unixDate  = strtotime($wdw_item->get_date());
							$briefDate = strftime(__('%m/%d/%Y', 'wp-delicious-wishlist'), $unixDate);
							$longDate  = strftime(__('%A, %m/%d/%Y at %T', 'wp-delicious-wishlist'), $unixDate).' ('.sprintf(__('%s ago', 'wp-delicious-wishlist'), human_time_diff($unixDate)).')';
							$wdw_wishlist .= '<div class="wishlist-timestamp"><abbr title="'.$longDate.'">'.$briefDate.'</abbr></div>';
						$wdw_wishlist .= '</li>';
					endforeach;
				}
			$wdw_wishlist .= '</ul>';
		}

		//***** Start 1 stars section *****\\
		add_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');
		$wdw_rss = fetch_feed('http://feeds.delicious.com/v2/rss/'.$wdw_nickname.'/'.$wdw_tag_wishlist.'+'.$wdw_tag_low.'?count='.$wdw_maxitems);
		if(is_wp_error($wdw_rss)) {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');
			return __('There was a problem fetching your feed.', 'wp-delicious-wishlist');
		} else {
			remove_filter('wp_feed_cache_transient_lifetime', 'wdw_cache');
			$num_items = $wdw_rss->get_item_quantity($wdw_maxitems);
			$wdw_items = $wdw_rss->get_items(0, $num_items);

			$wdw_wishlist .= '<h3 id="low">';
				if ($wdw_title_low) {
					$wdw_wishlist .= $wdw_title_low;
				} else {
					$wdw_wishlist .= __('I like', 'wp-delicious-wishlist');
				}
			$wdw_wishlist .= '</h3>';
			$wdw_wishlist .= '<ul id="wishlist-low">';
				if (empty($wdw_items)) {
					$wdw_wishlist .= '<li class="low-'.$wdw_icons.'">'.__('Nothing in this moment', 'wp-delicious-wishlist').'</li>';
				} else {
					foreach ( $wdw_items as $wdw_item ) :
						$wdw_wishlist .= '<li class="low-'.$wdw_icons.'">
							<a class="wishlist-link" href="'.$wdw_item->get_permalink().'" title="'.$wdw_item->get_title().'">'
								.$wdw_item->get_title().
							'</a><br />
							<div class="wishlist-description">'.$wdw_item->get_description().'</div>';
							$unixDate  = strtotime($wdw_item->get_date());
							$briefDate = strftime(__('%m/%d/%Y', 'wp-delicious-wishlist'), $unixDate);
							$longDate  = strftime(__('%A, %m/%d/%Y at %T', 'wp-delicious-wishlist'), $unixDate).' ('.sprintf(__('%s ago', 'wp-delicious-wishlist'), human_time_diff($unixDate)).')';
							$wdw_wishlist .= '<div class="wishlist-timestamp"><abbr title="'.$longDate.'">'.$briefDate.'</abbr></div>';
						$wdw_wishlist .= '</li>';
					endforeach;
				}
			$wdw_wishlist .= '</ul>';
		}
	}

	// Return the complete wishlist
	return $wdw_wishlist;
}
add_shortcode('my-delicious-wishlist', 'wp_delicious_wishlist');

// Load the options page
function wdw_options_page() { ?>

	<div class="wrap">
		<h2><?php _e('Delicious Wishlist for WordPress Options', 'wp-delicious-wishlist'); ?></h2>

		<p>
			<?php printf(__('%1$srequired fields%2$s','wp-delicious-wishlist'), '<strong>[*] = ', '.</strong>'); ?> &bull;
			<?php printf(__('The User Guide is %1$sbelow%2$s.','wp-delicious-wishlist'), '<a href="#user-guide">', '</a>'); ?>
		</p>

		<div class="clear" id="poststuff">
			<div class="postbox">
				<h3><?php _e('Settings', 'wp-delicious-wishlist'); ?></h3>
				<div class="inside">
					<br class="clear" />
					<form method="post" action="options.php">
						<?php settings_fields('wdw-options-group'); $wdws = array(); $wdws = get_option('wdw_options'); ?>

						<table class="widefat">

							<tr valign="top" class="alternate">
								<th scope="row">
									<?php _e('Delicious Nickname: [*]', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_nickname]" value="<?php echo $wdws['wdw_delicious_nickname']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php _e('Delicious Wishlist Tag: [*]', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_tag_wishlist]" value="<?php echo $wdws['wdw_delicious_tag_wishlist']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top" class="alternate">
								<th scope="row">
									<?php _e('Title for High Tag section:', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_title_high]" value="<?php echo $wdws['wdw_delicious_title_high']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php _e('Delicious High Tag: [*]', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_tag_high]" value="<?php echo $wdws['wdw_delicious_tag_high']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top" class="alternate">
								<th scope="row">
									<?php _e('Title for Medium Tag section:', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_title_medium]" value="<?php echo $wdws['wdw_delicious_title_medium']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php _e('Delicious Medium Tag: [*]', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_tag_medium]" value="<?php echo $wdws['wdw_delicious_tag_medium']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top" class="alternate">
								<th scope="row">
									<?php _e('Title for Low Tag section:', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_title_low]" value="<?php echo $wdws['wdw_delicious_title_low']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php _e('Delicious Low Tag: [*]', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_tag_low]" value="<?php echo $wdws['wdw_delicious_tag_low']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top" class="alternate">
								<th scope="row">
									<?php _e('How many items (max 100):', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_howmany]" value="<?php echo $wdws['wdw_delicious_howmany']; ?>" size="75">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php _e('Icons style:', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<select name="wdw_options[wdw_delicious_icons]" >
										<?php $my_wdw_style = $wdws['wdw_delicious_icons']; ?>
										<option <?php selected('stars', $my_wdw_style); ?> value="stars"><?php _e('Stars', 'wp-delicious-wishlist'); ?></option>
										<option <?php selected('faces', $my_wdw_style); ?> value="faces"><?php _e('Faces', 'wp-delicious-wishlist'); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top" class="alternate">
								<th scope="row">
									<?php _e('Feed cache time (in seconds, min 1800):', 'wp-delicious-wishlist'); ?>
								</th>
								<td>
									<input type="text" name="wdw_options[wdw_delicious_cache]" value="<?php echo $wdws['wdw_delicious_cache']; ?>" size="75">
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-delicious-wishlist'); ?>" />
						</p>

					</form>
					<!-- close form -->
				</div>
			</div>
		</div>

		<div id="poststuff">
			<div class="postbox">
				<h3 id="user-guide"><?php _e('User Guide', 'wp-delicious-wishlist'); ?></h3>
				<div class="inside">
					<br class="clear" />
					<h4><?php _e('Installation', 'wp-delicious-wishlist'); ?></h4>

					<p>
						<?php _e('This plugin allows you to publish in your blog a wishlist using your Delicious bookmarks. '.
						'In order to make this, when you visit a web page with something you like, tag that page with two different bookmarks: '.
						'<code>wishlist</code> and, if it is very important, <code>***</code> (three stars). '.
						'Then, when you visit a page with something less important, you could use <code>wishlist</code> and <code>**</code> (two stars), '.
						'and finally for a page with something even less important, you could use <code>wishlist</code> and <code>*</code> (one star). '.
						'It\'s not mandatory to use these exact tags: you can choose your own tags, but consider that you have to bookmark '.
						'a page with at least two different tags: one general to collect all your bookmarks relative to your wishlist, '.
						'and another to mark that page depending on the importance of the stuff for you.<br /><br />'.
						'When you are done with an item (you have bought it or someone gave it to you as a gift), '.
						'you can edit that bookmark on Delicious and remove the star(s), leaving only the main tag (e.g., <code>wishlist</code>), '.
						'so you can maintain in Delicious an archive of all desired items.<br /><br />'.
						'To start, fill in the fields in the form above.', 'wp-delicious-wishlist'); ?>
						<br />
						<?php _e('<strong>All fields are required (except for Titles).</strong> The values are not case sensitive.', 'wp-delicious-wishlist'); ?>
					</p>

					<p>
						<?php _e('When you are done filling those fields, clic on the "Save Changes" button '.
						'and create a new page and give it a title you want. '.
						'In the body of the page, paste the following shortcode:', 'wp-delicious-wishlist'); ?>
						<br /><br /><code style="font-size: 1.3em;">[my-delicious-wishlist]</code><br /><br />
						<?php _e('You can add some text before and/or after the shortcode. Publish the page and visit it on your blog. You are done!', 'wp-delicious-wishlist'); ?>
					</p>

					<h4><?php _e('Changing the style of the Wishlist page', 'wp-delicious-wishlist'); ?></h4>

					<p>
						<?php _e('The page is stylized using the css file included in the plugin directory. '.
						'If you want to restyle the page, you can put a css file in the root directory of the theme you are using, '.
						'create your styles, and name it <code>wdw.css</code>. '.
						'All future versions of this plugin will load only your own css file.', 'wp-delicious-wishlist'); ?>
					</p>

				</div>

			</div>
		</div>

		<div id="poststuff">
			<div class="postbox">
				<h3><?php _e('Credits', 'wp-delicious-wishlist'); ?></h3>
				<div class="inside">
					<br class="clear" />
					<p>
						<?php _e('My thanks go to all people who contributed in revisioning and helping me in any form, and in particular to', 'wp-delicious-wishlist'); ?>
						 <a href="http://www.nicoladagostino.net/">Nicola D'Agostino</a> <?php _e('and to', 'wp-delicious-wishlist'); ?>
						 <a href="http://suzupearl.com/">Barbara Arianna Ripepi</a> <?php _e('for their great idea behind this work.', 'wp-delicious-wishlist'); ?>
					</p>
				</div>
			</div>

		</div>

	</div>
	<!-- close wrap -->

<?php }

// Add the stylesheet
function wp_delicious_wishlist_stylesheets() {
	if(file_exists(TEMPLATEPATH.'/wdw.css')) {
		wp_enqueue_style('wp-delicious-wishlist', get_stylesheet_directory_uri().'/wdw.css', false, false, 'all');
	} else {
		wp_enqueue_style('wp-delicious-wishlist', plugins_url('delicious-wishlist-for-wordpress/wdw.css'), false, false, 'all');
	}
}
add_action('wp_print_styles', 'wp_delicious_wishlist_stylesheets');

// Make plugin available for i18n
// Translations must be archived in the /languages/ directory
setlocale(LC_ALL, get_locale().'.UTF8');
load_plugin_textdomain('wp-delicious-wishlist', false, 'delicious-wishlist-for-wordpress/languages');

?>