<?php if (is_uri_value($this->uri->segment(2), config_item('user_settings'))): ?>
<h2 class="content_title"><img src="<?= $dashboard_assets ?>icons/users_32.png"> Your Settings</h2>
<ul class="content_navigation">
	<?= navigation_list_btn('settings/profile', 'Profile') ?>
	<?= navigation_list_btn('settings/details', 'Details') ?>
	<!-- <?= navigation_list_btn('settings/contact', 'Contact') ?> -->
	<?= navigation_list_btn('settings/password', 'Password') ?>
	<?= navigation_list_btn('settings/connections', 'Connections') ?>
	<!-- <?= navigation_list_btn('settings/advanced', 'Advanced') ?> -->
</ul>
<?php elseif (is_uri_value($this->uri->segment(2), config_item('core_modules'))): ?>
<h2 class="content_title">
	<img src="<?= $dashboard_assets.'icons/'.$this->uri->segment(2) ?>_32.png"> <?= ucwords($this->uri->segment(2)) ?> Settings
</h2>
<ul class="content_navigation">
	 <?= navigation_list_btn('settings/'.$this->uri->segment(2).'/widgets', 'Widgets') ?>
	 <?= navigation_list_btn('settings/'.$this->uri->segment(2), 'Settings') ?>
	<li><a href="<?= base_url() ?>settings/apps">Apps</a><span class="actions_normal action_sync nav_apps_back"></span></li>
</ul>
<?php elseif (is_uri_value($this->uri->segment(2), config_item('site_modules'))): ?>
<h2 class="content_title"><img src="<?= $dashboard_assets ?>icons/site_32.png">  Site</h2>
<ul class="content_navigation">
	<?= navigation_list_btn('settings/site', 'Settings') ?>
	<?= navigation_list_btn('settings/design', 'Design') ?>
	<?= navigation_list_btn('settings/themes', 'Themes') ?>
	<?= navigation_list_btn('settings/widgets', 'Widgets', $this->uri->segment(3)) ?>
	<?= navigation_list_btn('settings/services', 'Services') ?>
	<?= navigation_list_btn('settings/updates', 'Updates') ?>
</ul>
<?php elseif ($this->uri->segment(2) == 'api'): ?>
<h2 class="content_title">
	<img src="<?= $dashboard_assets ?>icons/api_32.png"> Api Settings
</h2>
<ul class="content_navigation">
	 <?= navigation_list_btn('home', 'Back To Home') ?>
</ul>
<?php elseif ($this->uri->segment(3) == 'categories'): ?>
<h2 class="content_title">
	<img src="<?= $modules_assets.$this->uri->segment(2) ?>_32.png"> <?= display_nice_file_name($this->uri->segment(2)) ?> Settings
</h2>
<ul class="content_navigation">
	<?php if (config_item($this->uri->segment(2).'_widgets') == 'TRUE') echo navigation_list_btn('settings/'.$this->uri->segment(2).'/widgets', 'Widgets'); ?>
	<?php if (config_item($this->uri->segment(2).'_categories') == 'TRUE') echo navigation_list_btn('settings/'.$this->uri->segment(2).'/categories', 'Categories', $this->uri->segment(4)); ?>
	<?= navigation_list_btn('settings/'.$this->uri->segment(2), 'Settings') ?>
	<li><a href="<?= base_url() ?>apps">Apps</a><span class="actions_normal action_sync nav_apps_back"></span></li>
</ul>
<?php elseif ($this->uri->segment(2) != 'apps'): ?>
<h2 class="content_title">
	<img src="<?= $modules_assets.$this_module ?>_32.png"> <?= display_nice_file_name($this_module) ?> Settings
</h2>
<ul class="content_navigation">
	<?php if (config_item($this_module.'_widgets') == 'TRUE') echo navigation_list_btn('settings/'.$this_module.'/widgets', 'Widgets'); ?>
	<?php if (config_item($this_module.'_categories') == 'TRUE') echo navigation_list_btn('settings/'.$this_module.'/categories', 'Categories', $this->uri->segment(4)); ?>
	<?= navigation_list_btn('settings/'.$this_module, 'Settings') ?>
	<li><a href="<?= base_url() ?>apps">Apps</a><span class="actions_normal action_sync nav_apps_back"></span></li>
</ul>
<?php endif; ?>