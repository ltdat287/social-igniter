<form name="settings_update" id="settings_update" method="post" action="<?= base_url() ?>api/settings/modify" enctype="multipart/form-data">

<div class="content_wrap_inner">

	<h3>Images</h3>
	
	<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>Full</td>
		<td><input class="nullify" type="checkbox" name="images_sizes_full" value="<?= $settings['users']['images_sizes_full'] ?>"></td>		
		<td><input type="text" name="images_full_width" value="<?= $settings['users']['images_full_width'] ?>" size="3"> x <input type="text" name="images_full_height" value="<?= $settings['users']['images_full_height'] ?>" size="3"> px</td>
	</tr>
	<tr>
		<td>Large</td>
		<td><input class="nullify" type="checkbox" name="images_sizes_large" value="<?= $settings['users']['images_sizes_large'] ?>"></td>
		<td><input type="text" name="images_large_width" value="<?= $settings['users']['images_large_width'] ?>" size="3"> x <input type="text" name="images_large_height" value="<?= $settings['users']['images_large_height'] ?>" size="3"> px</td>
	</tr>
	<tr>
		<td>Medium</td>
		<td><input class="nullify" type="checkbox" name="images_sizes_medium" value="<?= $settings['users']['images_sizes_medium'] ?>"></td>
		<td><input type="text" name="images_medium_width" value="<?= $settings['users']['images_medium_width'] ?>" size="3"> x <input type="text" name="images_medium_height" value="<?= $settings['users']['images_medium_height'] ?>" size="3"> px</td>
	</tr>
	<tr>
		<td>Small</td>
		<td><input class="nullify" type="checkbox" name="images_sizes_small" value="<?= $settings['users']['images_sizes_small'] ?>"></td>	
		<td><input type="text" name="images_small_width" value="<?= $settings['users']['images_small_width'] ?>" size="3"> x <input type="text" name="images_small_height" value="<?= $settings['users']['images_small_height'] ?>" size="3"> px</td>
	</tr>
	<tr>
		<td>Original</td>
		<td><input class="nullify" type="checkbox" name="images_sizes_original" value="<?= $settings['users']['images_sizes_original'] ?>"></td>	
		<td>Keep original uploaded image</td>		
	</tr>	
	</table>

	<p><input type="text" name="images_formats" value="<?= $settings['users']['images_formats'] ?>" > formats allowed</p>	
	<p><input type="text" name="images_max_size" value="<?= $settings['users']['images_max_size'] ?>" size="5"> max file size</p>
	<p><input type="text" name="images_max_dimensions" value="<?= $settings['users']['images_max_dimensions'] ?>" size="5"> max image dimensions (px)</p>
	<p><?= base_url() ?><input type="text" name="images_folder" value="<?= $settings['users']['images_folder'] ?>" size="32"> images path</p>
	
</div>	

<span class="item_separator"></span>

<div class="content_wrap_inner">

	<h3>Settings</h3>

	<p>Editible Level
	<?= form_dropdown('settings_level', config_item('users_levels'), $settings['users']['settings_level']) ?>
	</p>

	<p>Redirect<br>
	<?= base_url() ?> <input type="text" size="30" name="settings_redirect" value="<?= $settings['users']['settings_redirect'] ?>" />
	</p>		
	
</div>	

<span class="item_separator"></span>

<div class="content_wrap_inner">

	<h3>Signup</h3>

	<div class="content_inner_top_right">	
		<p>Enabled
		<?= form_dropdown('signup', config_item('enable_disable'), $settings['users']['signup']) ?>
		</p>
	</div>	

	<p>ReCAPTCHA
	<?= form_dropdown('signup_recaptcha', config_item('enable_disable'), $settings['users']['signup_recaptcha']) ?></p>	
	
</div>	

<span class="item_separator"></span>

<div class="content_wrap_inner">

	<h3>Login</h3>

	<div class="content_inner_top_right">	
		<p>Enabled
		<?= form_dropdown('login', config_item('enable_disable'), $settings['users']['login']) ?>
		</p>
	</div>

	<p>ReCAPTCHA
	<?= form_dropdown('login_recaptcha', config_item('enable_disable'), $settings['users']['login_recaptcha']) ?></p>	

	<input type="hidden" name="module" value="users">

	<p><input type="submit" name="save" value="Save" /></p>
	
</div>

</form>

<?= $shared_ajax ?>