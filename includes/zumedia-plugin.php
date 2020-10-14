<?php
// Includes all traits --------------------------------------------------------]

include_once('zumedia-ratio.php');
include_once('zumedia-attachments.php');
include_once('zumedia-location.php');

class zu_Media extends zukit_Plugin  {

	// NOTE: удалить??
	// private $mplus_class = 'media_plus';
	// private $mplus_size_def = 'medium';

	// Addons
	private $folders = null;
	private $dominant = null;
	private $sizes = null;
	private $clean = null;

	// Ratio & data, attachments, location helpers
	use zu_MediaRatio, zu_MediaAttachments, zu_MediaLocation;

	protected function config() {
		return  [
			'prefix'			=> 'zumedia',
			'zukit'				=> true,

			'options'			=> [
				'folders'			=> true,
				'dominant' 			=> true,
				'add_tags'			=> true,
				'add_category'		=> false,
				'add_location'		=> true,

				'responsive'		=> false,
				'admin_colors' 		=> false,
				'media_ratio'		=> false,
				'gallery_type'		=> 'portfolio',
				'check_media'		=> false,
				'disable_cache'		=> false,
				'svg'				=> false,
			],
		];
	}

	public function init() {

		// Add info rows & debug actions --------------------------------------]

		add_filter('zukit_plugin_info', function() {
			$stats = $this->folders ? $this->folders->stats() : [];
			return [
				'folders' 		=> empty($stats) ? null : [
						'label'		=> __('Folders', 'zumedia'),
						'value'		=> $stats['folders'],
						'depends' 	=> 'folders',
				],
				'galleries' 	=> empty($stats) ? null : [
						'label'		=> __('Galleries', 'zumedia'),
						'value'		=> $stats['galleries'],
				],
				'images'		=> [
						'label'		=> __('Images', 'zumedia'),
						'value'		=> count($this->get_attachments()),
				],
				'memory'		=> [
						'label'		=> __('Cached Data', 'zumedia'),
						'value'		=> $this->get_cached_memory($stats),
						'depends' 	=> ['folders', 'disable_cache'],
				],
			];
		});

		add_filter('zukit_debug_actions', function($debug_actions) {
			if($this->folders) {
				$debug_actions[] = [
						'label'		=> __('Fix Orphaned Attachments', 'zumedia'),
						'value'		=> 'zumedia_fix_orphaned',
						'icon'		=> 'hammer',
						'color'		=> 'blue',
				];
				$debug_actions[] = [
						'label'		=> __('Check Existed Terms', 'zumedia'),
						'value'		=> 'zumedia_check_terms',
						'icon'		=> 'warning',
						'color'		=> 'gold',
				];
			}
			return $debug_actions;
		});

		// Image Sizes Addon --------------------------------------------------]

		$this->sizes = $this->register_addon(new zu_MediaImageSizes());

		// Responsive Addon ---------------------------------------------------]

		if($this->is_option('responsive')) {
			// $this->register_addon(new zu_MediaResponsive());
		}

		// Media Folders ------------------------------------------------------]

		if($this->is_option('folders')) {
			$this->folders = $this->register_addon(new zu_MediaFolder());
		}

		// Dominant color Addon -----------------------------------------------]

		if($this->is_option('dominant')) {
			$this->dominant = $this->register_addon(new zu_MediaDominant());
		}

		// Admin colors Addon -------------------------------------------------]

		if($this->is_option('admin_colors')) {
			$this->register_addon(new zu_MediaAdminColors());
		}

		// Register or create taxonomies --------------------------------------]

		if($this->is_option('media_ratio')) $this->init_media_ratio();
		if($this->is_option('add_category')) register_taxonomy_for_object_type('category', 'attachment');
		if($this->is_option('add_tags')) register_taxonomy_for_object_type('post_tag', 'attachment');
		if($this->is_option('add_location')) $this->register_location();

		// Some internal 'inits' ----------------------------------------------]

		$this->init_cachekeys();
		$this->init_baseurl();
	}

	public function ajax_more($action, $value) {
		if($action === 'zumedia_reset_cached') return $this->reset_cached();
		else return null;
	}

	// Custom menu position ---------------------------------------------------]

	protected function custom_admin_submenu() {

		return [
			'reorder'	=>	[
				[
					'menu'			=> 	'options-media.php',
					'new_index'		=>	$this->menu_split_index + 2,
				],
				[
					'menu'			=> 	$this->admin_slug(),
					'new_index'		=>	$this->menu_split_index + 3,
				],
			],
			'separator'	=>	[
				[
					'new_index'		=>	$this->menu_split_index + 1,
				],
			],
		];
	}

	// Script enqueue ---------------------------------------------------------]

	protected function js_data($is_frontend, $default_data) {
		return  $is_frontend ? [] : array_merge($default_data, [
			'jsdata_name'	=> 'zumedia_settings',
			// 'data'			=> $this->data,
			'actions' 		=> [
				[
					'label'		=> __('Convert Folders', 'zumedia'),
					'value'		=> 'zumedia_convert_taxonomy',
					'icon'		=> 'update',
					'color'		=> 'green',
					'help'		=> 'Folders from the "WP Media Folder" plugin will be converted to work with "Zu Media".',

					'depends'	=> $this->folders && $this->folders->is_convertible() ? 'folders' : false,
				],
				[
					'label'		=> __('Update Dominants', 'zumedia'),
					'value'		=> 'zumedia_update_dominants',
					'icon'		=> 'admin-customizer',
					'color'		=> 'gold',
					'help'		=> 'Dominant Colors will be updated for all existing images'
										.' in Media Library if you press this button.',
					// the button will be visible only if this option is 'true'
					'depends'	=> 'dominant',
				],
				[
					'label'		=> __('Clean All Cached Data', 'zumedia'),
					'value'		=> 'zumedia_reset_cached',
					'icon'		=> 'dismiss',
					'color'		=> 'magenta',
					'help'		=> 'Clear all cached data referenced to attachments, galleries and albums.'
										.' Needs if you added gallery or album.',
					'depends'	=> '!disable_cache',
				],
				[
					'label'		=> __('Check Media', 'zumedia'),
					'value'		=> 'zumedia_checkup_media',
					'icon'		=> 'backup',
					'color'		=> 'blue',
					'help'		=> 'Checks all attachment images against the current image sizes and orphaned files.'
										.' Also checks file dimensions against meta dimensions.',
					'depends'	=> false,
				],
				[
					'label'		=> __('Clean Invalid Media', 'zumedia'),
					'value'		=> 'zumedia_cleanup_media',
					'icon'		=> 'trash',
					'color'		=> 'red',
					'help'		=> 'Removes all files which are no longer referenced to attachment.'
										.' Not dangerous for the valid attachments... maybe.',
					'depends'	=> false,
				],
			],
		]);
	}

	protected function should_load_css($is_frontend, $hook) {
		return $is_frontend === false && $this->ends_with_slug($hook);
	}

	protected function should_load_js($is_frontend, $hook) {
		return $is_frontend === false && $this->ends_with_slug($hook);
	}

	// Dominant Colors --------------------------------------------------------]

	public function get_dominant_by_id($post_or_attachment_id = null) {
		if($this->dominant) {
			$attachment_id = $this->snippets('get_attachment_id', $post_or_attachment_id);
			return $this->dominant->get_dominant_by_attachment_id($attachment_id);
		} else {
			return zu_MediaDominant::default_color();
		}
	}

	public function update_dominant_by_id($post_or_attachment_id = null) {
		if($this->dominant) {
			$attachment_id = $this->snippets('get_attachment_id', $post_or_attachment_id);
			return $this->dominant->update_dominant_by_attachment_id($attachment_id);
		} else {
			return false;
		}
	}

	// Image Sizes ------------------------------------------------------------]

	public function full_key() {
		return $this->sizes->full_key;
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

// require_once('addons/zumedia-functions.php');
// require_once('addons/zumedia-responsive.php');
// require_once('addons/zumedia-admin.php');
// require_once('addons/zumedia-extend-media.php');
// require_once('addons/zumedia-replace-image.php');
// require_once('addons/zumedia-cleanup.php');
