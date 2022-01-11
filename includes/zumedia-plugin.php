<?php
// Includes all traits --------------------------------------------------------]

include_once('traits/ajax.php');
include_once('traits/attachments.php');
include_once('traits/cached.php');
include_once('traits/location.php');
include_once('traits/ratio.php');

class zu_Media extends zukit_Plugin  {

	// Plugin addons
	private $folders = null;
	private $dominant = null;
	private $sizes = null;
	private $acolors = null;
	// private $clean = null;

	// Ratio & data, REST API, attachments and location helpers
	use zu_MediaRatio, zu_MediaAjax, zu_MediaCached, zu_MediaAttachments, zu_MediaLocation;

	protected function config() {
		return  [
			'prefix'			=> 'zumedia',
			'zukit'				=> true,

			'translations'		=> [
				'path'				=> 'lang',
				'domain'			=> 'zu-media',
			],

			'appearance'		=> [
				'colors'			=> [
					'backdrop'			=> '#f0f4fd',
					'header'			=> '#b0c5fd',
					'title'				=> '#283965',
				],
			],

			'options'			=> [
				'folders'				=> true,
				'dominant' 				=> true,
				'add_tags'				=> true,
				'tag_rewrite'			=> 'media-tag',
				'add_category'			=> false,
				'category_rewrite'		=> 'media-cat',
				'add_location'			=> true,

				'responsive'			=> false,
				'admin_colors' 			=> false,
				'media_ratio'			=> false,
				'gallery_type'			=> 'portfolio',
				'check_media'			=> false,
				'disable_cache'			=> false,
			],
		];
	}

	protected function extend_info() {
		$stats = $this->folders ? $this->folders->stats() : [];
		return [
			'folders' 		=> [
					'label'		=> __('Folders', 'zu-media'),
					'value'		=> empty($stats) ? null : $stats['folders'],
					'depends' 	=> 'folders',
			],
			'galleries' 	=> [
					'label'		=> __('Galleries', 'zu-media'),
					'value'		=> empty($stats) ? null : $stats['galleries'],
			],
			'images'		=> [
					'label'		=> __('Images', 'zu-media'),
					'value'		=> count($this->get_attachments()),
			],
			'memory'		=> [
					'label'		=> __('Cached Data', 'zu-media'),
					'value'		=> $this->get_cached_memory($stats),
					'depends' 	=> ['folders', 'disable_cache'],
			],
		];
	}

	protected function extend_actions() {
		return [
			[
				'label'		=> __('Convert Folders', 'zu-media'),
				'value'		=> 'zumedia_convert_taxonomy',
				'icon'		=> 'update',
				'color'		=> 'green',
				'help'		=> __('Folders from the "WP Media Folder" plugin'
									.' will be converted to work with "Zu Media".', 'zu-media'),

				'depends'	=> $this->folders && $this->folders->is_convertible() ? 'folders' : false,
			],
			[
				'label'		=> __('Update Dominants', 'zu-media'),
				'value'		=> 'zumedia_update_dominants',
				'icon'		=> 'admin-customizer',
				'color'		=> 'gold',
				'help'		=> __('Dominant Colors will be updated for all existing images'
									.' in Media Library if you press this button.', 'zu-media'),
				// the button will be visible only if this option is 'true'
				'depends'	=> 'dominant',
			],
			[
				'label'		=> __('Clean All Cached Data', 'zu-media'),
				'value'		=> 'zumedia_reset_cached',
				'icon'		=> 'dismiss',
				'color'		=> 'magenta',
				'help'		=> __('Clear all cached data referenced to attachments, galleries and folders.'
									.' Needs if you added gallery or folder.', 'zu-media'),
				'depends'	=> '!disable_cache',
			],
			[
				'label'		=> __('Flush Rewrite Rules', 'zu-media'),
				'value'		=> 'zumedia_flush_rewrite',
				'icon'		=> 'superhero',
				'color'		=> 'blue',
				'help'		=> __('Remove rewrite rules and then recreate rewrite rules.'
									.' Needs if you redefined tag or category rewrite rules.', 'zu-media'),
				'depends'	=> ['zumedia_folders_options.add_rewrite', 'add_tags', 'add_category'],
			],

			// пока не поддерживается!
			[
				'label'		=> __('Check Media', 'zu-media'),
				'value'		=> 'zumedia_checkup_media',
				'icon'		=> 'backup',
				'color'		=> 'blue',
				'help'		=> __('Checks all attachment images against the current image sizes and orphaned files.'
									.' Also checks file dimensions against meta dimensions.', 'zu-media'),
				'depends'	=> false,
			],
			[
				'label'		=> __('Clean Invalid Media', 'zu-media'),
				'value'		=> 'zumedia_cleanup_media',
				'icon'		=> 'trash',
				'color'		=> 'red',
				'help'		=> __('Removes all files which are no longer referenced to attachment.'
									.' Not dangerous for the valid attachments... maybe.', 'zu-media'),
				'depends'	=> false,
			],
		];
	}

	protected function extend_debug_options() {
		return [
			'show_id'	=> [
				'label'		=> __('Display Attachment Id', 'zu-media'),
				'value'		=> false,
			],
		];
	}

	protected function extend_debug_actions() {
		return $this->folders ? [
			[
				'label'		=> __('Fix Orphaned Attachments', 'zu-media'),
				'value'		=> 'zumedia_fix_orphaned',
				'icon'		=> 'hammer',
				'color'		=> 'blue',
			],
			[
				'label'		=> __('Check Existed Terms', 'zu-media'),
				'value'		=> 'zumedia_check_terms',
				'icon'		=> 'warning',
				'color'		=> 'gold',
			],
		] : [];
	}

	// Actions & Add-ons ------------------------------------------------------]

	public function init() {

		// Image Sizes Addon
		$this->sizes = $this->register_addon(new zu_MediaImageSizes());

		// Responsive Addon
		if($this->is_option('responsive')) {
			// $this->register_addon(new zu_MediaResponsive());
		}

		// Media Folders Addon
		if($this->is_option('folders')) {
			$this->folders = $this->register_addon(new zu_MediaFolder());
		}

		// Dominant Color Addon
		if($this->is_option('dominant')) {
			$this->dominant = $this->register_addon(new zu_MediaDominant());
		}

		// Admin colors Addon
		if($this->is_option('admin_colors')) {
			$this->acolors = $this->register_addon(new zu_MediaAdminColors());
		}

		// Register or create new taxonomies ----------------------------------]

		if($this->is_option('media_ratio')) $this->init_media_ratio();
		if($this->is_option('add_category')) {
			$rewrite = $this->get_option('category_rewrite') ?? '';
			register_taxonomy_for_object_type('category', 'attachment');
			add_rewrite_rule(
				"^{$rewrite}/([^/]*)/?",
				'index.php?post_type=attachment&category=$matches[1]',
				'top');
			add_rewrite_tag("%{$rewrite}%", '([^&]+)');
		}
		if($this->is_option('add_tags')) {
			$rewrite = $this->get_option('tag_rewrite') ?? '';
			register_taxonomy_for_object_type('post_tag', 'attachment');
			add_rewrite_rule(
				"^{$rewrite}/([^/]*)/?",
				'index.php?post_type=attachment&tag=$matches[1]',
				'top');
			add_rewrite_tag("%{$rewrite}%", '([^&]+)');
		}
		if($this->is_option('add_location')) $this->register_location();

		// Some internal 'inits' ----------------------------------------------]

		$this->init_cachekeys();
		$this->init_baseurl();
		$this->register_snippets();
	}

	// Reset admin color scheme -----------------------------------------------]

	protected function construct_more() {
		$this->safe_admin_color_scheme(true);
	}

	protected function on_deactivation() {
		$this->safe_admin_color_scheme();
	}

	// reset admin color scheme if 'Admin colors' add-on was deactivated
	private function safe_admin_color_scheme($on_plugins_loaded = false) {
		if($on_plugins_loaded) {
			add_action('plugins_loaded', function() {
				zu_MediaAdminColors::maybe_clean_color_scheme($this->is_option('admin_colors'));
			});
		} else {
			zu_MediaAdminColors::maybe_clean_color_scheme();
		}
	}

	// Custom menu position ---------------------------------------------------]

	protected function custom_admin_submenu() {

		return [
			'reorder'	=>	[
				[
					'menu'			=> 	'options-media.php',
					'new_index'		=>	$this->from_split_index(2),
				],
				[
					'menu'			=> 	$this->admin_slug(),
					'new_index'		=>	$this->from_split_index(3),
				],
			],
			'separator'	=>	[
				[
					'new_index'		=>	$this->from_split_index(1),
				],
			],
		];
	}

	// Script enqueue ---------------------------------------------------------]

	protected function should_load_css($is_frontend, $hook) {
		return $is_frontend === false && $this->ends_with_slug($hook);
	}

	protected function should_load_js($is_frontend, $hook) {
		return $is_frontend === false && $this->ends_with_slug($hook);
	}

	protected function enqueue_more($is_frontend, $hook) {
		// always add styles only for Settings Page (needed for Folders Preview)
		// we cannot do this in the add-on, since if it is not created (because
		// the 'folders' option is disabled), then the styles will not be loaded
		if(!$is_frontend && $this->ends_with_slug($hook)) {
			$this->admin_enqueue_style('zumedia-folders');
		}
	}

	// Public snippets --------------------------------------------------------]

	private function register_snippets() {
		$this->register_snippet('get_dominant_by_id', $this->dominant, $this->snippets('get_default_background_color'));
		$this->register_snippet('update_dominant_by_id', $this->dominant, false);
		$this->register_snippet('media_size_full_key', $this->sizes, 'full');

		$this->register_snippet('get_folders', $this->folders, null);
		$this->register_snippet('get_folder', $this->folders, null);
		$this->register_snippet('get_folder_props', $this->folders, null);
		$this->register_snippet('get_folder_by_attachment_id', $this->folders, null);

		$this->register_snippet('get_galleries', $this->folders, []);
		$this->register_snippet('get_gallery_by_attachment_id', $this->folders, []);
		$this->register_snippet('get_all_images_in_folder', $this->folders, []);
		$this->register_snippet('get_all_images', $this->folders, []);

		$this->register_snippet('get_folder_permalink', $this->folders, false);
		$this->register_snippet('folder_exists', $this->folders, false);
		$this->register_snippet('is_private_folder', $this->folders, false);
		$this->register_snippet('is_private_image', $this->folders, false);

		$this->register_snippet('get_all_landscaped', $this, []);
		$this->register_snippet('is_landscape', $this, true);
		$this->register_snippet('is_landscape_ratio', $this, true);

		$this->register_snippet('get_location');
		$this->register_snippet('get_media_taxonomy_link');
	}
}

// Entry Point ----------------------------------------------------------------]

function zumedia($file = null) {
	return zu_Media::instance($file);
}

// Additional Classes & Functions ---------------------------------------------]

require_once('addons/dominant-color.php');
require_once('addons/admin-colors.php');
require_once('addons/image-sizes.php');
require_once('media-folders/zumedia-folders.php');

// require_once('addons/zumedia-cleanup.php');

// Functions for backward compatibility with media-plus -----------------------]

if(!function_exists('mplus_instance')) {

	function mplus_get_album_by_id($folder_id, $get_parent_from = []) {
		return zumedia()->snippets('folder_by_id', $folder_id, $get_parent_from); }

	function mplus_check_landscape($width, $height, $limit = '3:2') {
		return zumedia()->snippets('is_landscape_ratio', $width, $height, $limit); }

	// function mplus_get_defaults() { return mplus_instance()->defaults(); }

	function mplus_get_dominant_by_id($post_or_attachment_id) {
		return zumedia()->snippets('get_dominant_by_id', $post_or_attachment_id); }
}
