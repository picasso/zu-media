<?php
class zu_MediaAdminColors extends zukit_Addon {

	private $schemes = [
		'zu-wine'			=> 	'Zu Wine',
		'zu-green-gold'		=>	'Zu Green Gold',
		'zu-ocean'			=>	'Zu Ocean',
		'zu-ola'			=>	'Zu Ola',
	];

	private $color_keys = ['base', 'highlight', 'notification', 'action'];
	private $icon_keys = ['icon_base', 'icon_focus', 'icon_current'];
	private $file_format = '/admin/css/colors/%1$s.css';

	public function admin_init() {

		foreach($this->schemes as $color_scheme => $title) {

			$colors = $this->read_colors($color_scheme);
			if(empty($colors)) continue;

			wp_admin_css_color(
				$color_scheme,
				$title,
				$this->get_filename($color_scheme, true),
				array_values($colors[0]),
				array_values($colors[1])
			);
		}

		$this->sort_colors();
	}


	private function get_filename($file, $as_uri = false) {

		$filepath = $this->sprintf_dir($this->file_format, $file);
		$fileuri = $this->sprintf_uri($this->file_format, $file);
		$version = sprintf('?v=%s', file_exists($filepath) ? filemtime($filepath) : time());

		return $as_uri ? $fileuri.$version : $filepath;
	}

	private function get_color($key) {
		$regex = sprintf('/__%1$s\s*\{\s*color\s*\:([^\}]+)/i', $key);
		return preg_match($regex, $this->contents, $colors) ? $colors[1] : '';
	}

	private function read_colors($file) {

		$filename = $this->get_filename($file);
		if(!file_exists($filename)) return [];
		$this->contents = file_get_contents($filename);

		$colors = [];
		foreach($this->color_keys as $key) {
			$colors[$key] = $this->get_color($key);
		}
		$icons =[];
		foreach($this->icon_keys as $key) {
			$icons[$key] = $this->get_color($key);
		}

		return [$colors, $icons];
	}

	private function sort_colors() {
		global $_wp_admin_css_colors;
		$_wp_admin_css_colors = array_filter(array_merge(array_fill_keys(array_keys($this->schemes), ''), $_wp_admin_css_colors));
	}
}
