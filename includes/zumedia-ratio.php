<?php
// NOTE: get_all_landscaped??
// NOTE: разобраться с mplus_get_location_as_text()

// Ratio helpers --------------------------------------------------------------]

trait zu_MediaRatio {

	// погрешности сравнения ratio
	private $ratio_e1 = 0.02;
	private $ratio_e2 = 0.07;

	private $ratio_names = [
		'1:1'			=> 	1,
		'7:6'			=> 	0.86,
		'4:3'			=> 	0.75,
		'3:2'			=> 	0.67,
		'16:9'			=> 	0.56,
		'17:8'			=> 	0.47,
		'17:6'			=> 	0.35,
	];

	private function init_media_ratio() {
		add_filter('attachment_fields_to_edit', [$this, 'fields_edit'], 10, 2);
		add_filter('attachment_fields_to_save', [$this, 'fields_save'], 10, 2);
	}

	// куда эту девать?
	public function get_all_landscaped() {
		global $_mplus_all_landscaped;

		if(empty($_mplus_all_landscaped)) {
			$_mplus_all_landscaped = array_keys(array_filter($this->get_attachments(false),
				function($val) { return  $val['landscaped']; }
			));
		}

		return $_mplus_all_landscaped;
	}

	public function is_landscape($width, $height, $limit = '3:2') {
		return $this->check_ratio($limit, $width, $height, false, true);
	}

	private function check_ratio($name, $width, $height, $strict_check = false, $and_less = false) {

		$value = $this->ratio_names[$name] ?? 0;
		$ratio = $height / $width;

		if($value == 0) {
			$name_sizes = explode(':', $name);
			$name = count($name_sizes) === 2 ? $name_sizes[1].':'.$name_sizes[0] : '';
			$value = $this->ratio_names[$name] ?? 0;
			$ratio = $width / $height;
		}

		$e = $strict_check ? $this->ratio_e1 : $this->ratio_e2;
		return $and_less ? ($ratio < ($value + $e) ? true : false) : (($ratio < ($value + $e) && $ratio > ($value - $e)) ? true : false);
	}

	private function get_ratio_name($attachment_id) {

		$metadata = wp_get_attachment_metadata($attachment_id);

		if(empty($metadata)) {
			$is_landscape = false;
			$ratio = 1;
		} else {
			$is_landscape = absint($metadata['width']) > absint($metadata['height']) ? true : false;
			$ratio = $is_landscape ? ($metadata['height'] / $metadata['width']) : ($metadata['width'] / $metadata['height']);
		}

		$found_value = sprintf('unknown (%1$.2f)', $ratio);

		foreach($this->ratio_names as $name => $value) {

			$name_sizes = explode(':', $name);
			$ratio_name = $is_landscape ? $name : $name_sizes[1].':'.$name_sizes[0];

			if($ratio < ($value + $this->ratio_e1) && $ratio > ($value - $this->ratio_e1)) 	$found_value = $ratio_name;
			else if($ratio < ($value + $this->ratio_e2) && $ratio > ($value - $this->ratio_e2)) 	$found_value = '~ '.$ratio_name;
		}
		return $found_value;
	}

	private function field_key($for_meta = false) {
		return sprintf('%2$s%1$s_media_ratio', $this->prefix, $for_meta ? '_' : '');
	}

	public function fields_edit($form_fields, $post) {
		// Add a custom field to an attachment in WordPress
		$meta_key = $this->field_key();

		// NOTE: not used, only to show how get meta value
		// $meta_key_value = get_post_meta($post->ID, $this->field_key(true), true);

		$meta_params = [
			'label'			=> __('Media Ratio', 'zumedia'),
			'show_in_edit' 	=> true,
			'show_in_modal' => true,
			'helps' 		=> '',
			'input' 		=> 'html',
			'html' 			=> sprintf(
				'<input name="attachments[%1$s][%2$s]" metaid="%1$s" id="attachments-%1$s-%2$s" class="mplus_metaid" type="text" value="%3$s" readonly>',
				$post->ID,
				$meta_key,
				$this->get_ratio_name($post->ID)
			)
		];

		$form_fields[$meta_key] = $meta_params;

		// keep location values for JS
		// NOTE: разобраться с mplus_get_location_as_text
		// $form_fields[$meta_key]['html'] .=  sprintf('<div class="qtx-location" style="display:none">%1$s</div>', mplus_get_location_as_text($post->ID, -1));

		return $form_fields;
	}

	public function fields_save($post, $attachment) {
		// Save custom field to post_meta
		$meta_key = $this->field_key();
		if(isset($attachment[$meta_key])) update_post_meta($post['ID'], $this->field_key(true), $attachment[$meta_key]);
		return $post;
	}
}
