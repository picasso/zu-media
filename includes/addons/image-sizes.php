<?php
class zu_MediaImageSizes extends zukit_Addon {

	private $sizes;
	private $wp_sizes;
	private $modified_color = '#ce52b4';

	private static $backup_key = 'zumedia_backup_sizes';
	private static $zu_sizes_key = 'zumedia_image_sizes';
	private static $wp_keys = ['thumbnail', 'medium', 'medium_large', 'large'];

	public $full_key = 'full';

	// crop - bool|array
	// Array values must be in the format: array( x_crop_position, y_crop_position ) where:
    // 		x_crop_position accepts: 'left', 'center', or 'right'.
    // 		y_crop_position accepts: 'top', 'center', or 'bottom'.
	private static $zu_sizes = [
		'medium'		=> [
			'width'		=> 400,
			'height' 	=> 0,
			'crop'   	=> false,
		],
		'medium_large'	=> [
			'width'		=> 768,
			'height' 	=> 0,
			'crop'   	=> false,
		],
		'extra_large'	=> [
			'width'		=> 1200,
			'height' 	=> 0,
			'crop'   	=> false,
		],
		'full_hd'		=> [
			'width'		=> 1920,
			'height' 	=> 1080,
			'crop'   	=> false,
		],
	];

	protected function config() {
		return ['name'	=> 'zumedia_image_sizes'];
	}

	protected function construct_more() {
		$responsive_support = $this->is_parent_option('responsive');
		if($responsive_support) self::backup();
		else self::restore();
	}

	public function init() {
		$this->sizes = $this->get_all_cached_sizes();
		if($this->is_parent_option('responsive')) {
			$sizes_to_create = $this->get_sizes_to_create();
			$this->create_sizes($sizes_to_create);
			$this->sizes = array_merge($this->sizes, $sizes_to_create);
		}
	}

	public function media_size_full_key() {
		return $this->full_key;
	}

	private function get_sizes_to_create() {
		$hdkey = 'full_hd';
		$this->full_key = $this->is_parent_option($hdkey) ? $hdkey : 'full';
		$sizes_to_create = get_option(self::$zu_sizes_key, []);

		if(!empty($sizes_to_create)) {
			$key_exists = array_key_exists($hdkey, $sizes_to_create);
			$full_key_is_hdkey = $this->full_key === $hdkey;
			$update_wp_sizes = ($full_key_is_hdkey && !$key_exists) || (!$full_key_is_hdkey && $key_exists);
		    $sizes_to_create = $this->without_wp_sizes($update_wp_sizes);
		} else {
			$sizes_to_create = $this->without_wp_sizes();
		}
		// convert to table output format
		$zu_keys = array_keys(self::$zu_sizes);
		foreach($sizes_to_create as $size_key => $size) {
			$sizes_to_create[$size_key]['zu'] = in_array($size_key, $zu_keys);
			$sizes_to_create[$size_key]['wp'] = in_array($size_key, self::$wp_keys);
		}
		return $sizes_to_create;
	}

	private function create_sizes($sizes_to_create) {
		foreach($sizes_to_create as $name => $size) {

			if(empty($size) || !isset($size['width']) || !isset($size['height'])) continue;
			$crop = $size['crop'] ?? false;

			if(is_bool($crop) || is_numeric($crop)) {
				$crop = absint($crop) === 0 ? false : true;
			}
			// Create new images size
			add_image_size($name, $size['width'], $size['height'], $crop);
		}
	}

	private function without_wp_sizes($update_wp_sizes = true) {
		$zu_sizes = self::$zu_sizes;
		if(!$this->is_parent_option('full_hd')) unset($zu_sizes['full_hd']);

		// change standard WP sizes if required
		foreach($zu_sizes as $name => $size) {

			if(in_array($name, self::$wp_keys) && isset($this->sizes[$name])) {
				$width = absint($size['width']);
				$height = absint($size['height']);
				$crop = (bool) $size['crop'];

				if($width != $this->sizes[$name]['width']) {
					if($update_wp_sizes) update_option("{$name}_size_w", $width);
					$this->sizes[$name]['width'] = $width;
				}
				if($height != $this->sizes[$name]['height']) {
					if($update_wp_sizes) update_option("{$name}_size_h", $height);
					$this->sizes[$name]['height'] = $height;
				}
				if($crop != (bool)$this->sizes[$name]['crop']) {
					if($update_wp_sizes) update_option("{$name}_crop", $crop);
					$this->sizes[$name]['crop'] = $crop;
				}
				unset($zu_sizes[$name]);
			}
		}

		update_option(self::$zu_sizes_key, $zu_sizes);
		return $zu_sizes;
	}

	public function get_all_cached_sizes() {
		$sizes = $this->call_parent('get_cached', 'sizes');
		if($sizes !== false) return $sizes;

		$sizes = self::get_all_sizes();
		$this->call_parent('set_cached', 'sizes', $sizes);
		return $sizes;
	}

	// Create table with all sizes --------------------------------------------]

	public function ajax($action, $value) {
		if($action === 'zumedia_all_sizes') return $this->generate_table();
		else return null;
	}

	private function modified_style() {
		return ['color' => $this->modified_color];
	}

	private function generate_table() {

		function asSize($value) {
			$value = intval($value);
			return $value === 0 || $value === 9999 ? '`proportional`' : sprintf('**%s**&nbsp;px', $value);
		}

		function asKind($wp, $zu, $is_modified, $icon) {
			$tooltip = $wp ?
				($is_modified ? __('Wordpress [modified]', 'zu-media') : __('Wordpress', 'zu-media')) :
				($zu ? __('Zu Media', 'zu-media') : __('Third Party', 'zu-media'));

			return [
				'tooltip'	=> $tooltip,
				'dashicon'	=> $wp ? ($is_modified ? 'wordpress' : 'wordpress-alt') : ($zu ? null : 'admin-plugins'),
				'svg'		=> $zu ? $icon : null,
				'style'		=> $is_modified,
			];
		}

		function asCrop($crop, $modified_style) {
			$crop = is_numeric($crop) || is_bool($crop) ? (bool)$crop : (implode(' ,', $crop) ?? '?');
			$tooltip = is_string($crop) ? $crop : ($crop ? __('Yes', 'zu-media') : __('No', 'zu-media'));

			return [
				'tooltip'	=> $tooltip,
				'dashicon'	=> $crop ? 'yes' : 'no',
				'style'		=> $crop ? $modified_style : null,
			];
		}

		// define table columns and styles
		$table = new zukit_Table(['kind', 'name', 'width', 'height', 'crop']);
		$table->align(['kind', 'width', 'height'], 'center');
		$table->strong('name');
		$table->as_icon(['kind', 'crop']);

		$rows = [];
		$zu_icon = $this->get('appearance.icon', true);
		$original = get_option(self::$backup_key, null);

		foreach($this->sizes as $name => $size) {
			$is_modified = $original && $size['wp'] && $size['width'] != $original[$name]['width'];
			$table->icon_cell('kind', asKind($size['wp'], $size['zu'], $is_modified ? $this->modified_style() : null, $zu_icon));
			$table->cell('name', $name);
			$table->markdown_cell('width', asSize($size['width']), null, $is_modified ? $this->modified_style() : null);
			$table->markdown_cell('height', asSize($size['height']));
			$table->icon_cell('crop', asCrop($size['crop'], $this->modified_style()));

			$table->next_row();
		}

		return $this->create_notice('data', null, $table->get());
	}

	// ------------------------------------------------------------------------]
	// Static functions
	// 		can be used without creating an instance of 'zu_MediaImageSizes'
	// ------------------------------------------------------------------------]

	public static function clean_options() {
		delete_option(self::$backup_key);
		delete_option(self::$zu_sizes_key);
	}

	public static function get_all_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = [];
		$zu_keys = array_keys(self::$zu_sizes);
		// Get the sizes and their properties
		foreach(get_intermediate_image_sizes() as $size_key) {

			if(!in_array($size_key, self::$wp_keys) && !isset($_wp_additional_image_sizes[$size_key])) continue;

			$data = [
				'width'  	=> get_option("{$size_key}_size_w"),
				'height' 	=> get_option("{$size_key}_size_h"),
				'crop'   	=> get_option("{$size_key}_crop"),
				'zu'		=> false,
				'wp'		=> in_array($size_key, self::$wp_keys),
			];

			if(isset($_wp_additional_image_sizes[$size_key])) {
				$additional_size = $_wp_additional_image_sizes[$size_key];
				$data['zu'] = in_array($size_key, $zu_keys);
				if(isset($additional_size['width'])) $data['width'] = intval($additional_size['width']);
				if(isset($additional_size['height'])) $data['height'] = intval($additional_size['height']);
				if(isset($additional_size['crop'])) $data['crop'] = $additional_size['crop'];
			}

			$sizes[$size_key] = $data;
		}

		return $sizes;
	}

	public static function backup() {
		$backup = get_option(self::$backup_key, []);
		if(empty($backup)) {
			$backup = self::get_all_sizes();
			update_option(self::$backup_key, $backup);
		}
	}

	public static function restore() {

		$backup = get_option(self::$backup_key, []);
		if(empty($backup)) return;

		foreach($backup as $name => $size) {
			if(in_array($name, self::$wp_keys)) {
				update_option("{$name}_size_w", absint($size['width']));
				update_option("{$name}_size_h", absint($size['height']));
				update_option("{$name}_crop", (bool)$size['crop']);
			}
		}
		self::clean_options();
	}
}
