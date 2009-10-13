<div class="wrap">
	<h2><?php _e('WordPress Delicious Wishlist Plugin Options', 'wp-delicious-wishlist'); ?></h2>

	<h3><?php _e('Settings', 'wp-delicious-wishlist'); ?></h3>

	<div style="max-width: 850px;">
		<p class="alignright"><?php _e('The User Guide is <a href="#user-guide">below</a>.','wp-delicious-wishlist'); ?></p>
		<p class="alignleft"><?php _e('<strong>[*] = required fields.</strong>','wp-delicious-wishlist'); ?></p>
	</div>

	<form method="post" action="options.php" style="max-width: 850px;">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">

			<tr valign="top" style="background-color:#EAF3FA; padding:10px;">
				<th scope="row">
					<?php _e('Delicious Nickname: [*]', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_nickname" value="<?php echo get_option('wdw_delicious_nickname'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="padding:10px;">
				<th scope="row">
					<?php _e('Delicious Wishlist Tag: [*]', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_tag_wishlist" value="<?php echo get_option('wdw_delicious_tag_wishlist'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="background-color:#EAF3FA; padding:10px;">
				<th scope="row">
					<?php _e('Title for High Tag section:', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_title_high" value="<?php echo get_option('wdw_delicious_title_high'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="background-color:#EAF3FA; padding:10px;">
				<th scope="row">
					<?php _e('Delicious High Tag: [*]', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_tag_high" value="<?php echo get_option('wdw_delicious_tag_high'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="padding:10px;">
				<th scope="row">
					<?php _e('Title for Medium Tag section:', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_title_medium" value="<?php echo get_option('wdw_delicious_title_medium'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="padding:10px;">
				<th scope="row">
					<?php _e('Delicious Medium Tag: [*]', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_tag_medium" value="<?php echo get_option('wdw_delicious_tag_medium'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="background-color:#EAF3FA; padding:10px;">
				<th scope="row">
					<?php _e('Title for Low Tag section:', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_title_low" value="<?php echo get_option('wdw_delicious_title_low'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="background-color:#EAF3FA; padding:10px;">
				<th scope="row">
					<?php _e('Delicious Low Tag: [*]', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_tag_low" value="<?php echo get_option('wdw_delicious_tag_low'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="padding:10px;">
				<th scope="row">
					<?php _e('How many items:', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<input type="text" name="wdw_delicious_howmany" value="<?php echo get_option('wdw_delicious_howmany'); ?>" size="75">
				</td>
			</tr>
			<tr valign="top" style="background-color:#EAF3FA; padding:10px;">
				<th scope="row">
					<?php _e('Icons style:', 'wp-delicious-wishlist'); ?>
				</th>
				<td>
					<select name="wdw_delicious_icons" >
						<?php $my_wdw_style = get_option('wdw_delicious_icons'); ?>
						<option <?php selected('stars', $my_wdw_style); ?> value="stars"><?php _e('Stars', 'wp-delicious-wishlist'); ?></option>
						<option <?php selected('faces', $my_wdw_style); ?>value="faces"><?php _e('Faces', 'wp-delicious-wishlist'); ?></option>
					</select>
				</td>
			</tr>
		</table>

		<input type="hidden" name="action" value="update" />

		<input type="hidden" name="page_options" value="wdw_delicious_nickname,wdw_delicious_tag_wishlist,wdw_delicious_tag_high,wdw_delicious_tag_medium,wdw_delicious_tag_low,wdw_delicious_howmany,wdw_delicious_title_high,wdw_delicious_title_medium,wdw_delicious_title_low,wdw_delicious_icons" />

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-delicious-wishlist'); ?>" />
		</p>

	</form>

	<div style="max-width: 850px;">

		<h3 id="user-guide"><?php _e('User Guide', 'wp-delicious-wishlist'); ?></h3>

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

		<h3><?php _e('Credits', 'wp-delicious-wishlist'); ?></h3>

		<p>
			<?php _e('My thanks go to all people who contributed in revisioning and helping me in any form, and in particular to', 'wp-delicious-wishlist'); ?>
			 <a href="http://www.nicoladagostino.net/">Nicola D'Agostino</a> <?php _e('and to', 'wp-delicious-wishlist'); ?>
			 <a href="http://suzupearl.com/">Barbara Arianna Ripepi</a> <?php _e('for their great idea behind this work.', 'wp-delicious-wishlist'); ?>
		</p>

	</div>

</div>
