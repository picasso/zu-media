<?php

// Ratio helpers --------------------------------------------------------------]

trait zu_MediaRatio {

	// deviations for comparison with ratio
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

	private $all_landscaped = [];

	private function init_media_ratio() {
		add_filter('attachment_fields_to_edit', [$this, 'fields_edit'], 10, 2);
		add_filter('attachment_fields_to_save', [$this, 'fields_save'], 10, 2);
	}

	private function check_ratio($name, $width, $height, $strict_check = false, $and_less = false) {
		// first we assume that the horizontal ratio '$name' is height to width, that is, like 3:2
		$value = $this->ratio_names[$name] ?? 0;
		$ratio = $width === 0 || $height === 0 ? 1 : ($height / $width);

		// if $value is 0, then we try the opposite (vertical) ratio, that is, width to height (like 2:3)
		if($value === 0) {
			$name_sizes = explode(':', $name);
			$name = count($name_sizes) === 2 ? $name_sizes[1].':'.$name_sizes[0] : '';
			$value = $this->ratio_names[$name] ?? 0;
			$ratio = $width / $height;
		}

		// if "strict" check, then the deviation is very small (ratio_e1)
		$e = $strict_check ? $this->ratio_e1 : $this->ratio_e2;
		// if $and_less is true, then $ratio will be whatever is less than the specified "ratio" (taking into account the deviation)
		// otherwise, return true only if the $ratio is equal to the specified "ratio" (plus/minus the deviation)
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

	private function save_ratio($attachment_id, $value) {
		update_post_meta($attachment_id, $this->field_key(true), $value);
	}

	public function fields_edit($form_fields, $post) {
		// Add a custom field to an attachment in WordPress
		$meta_key = $this->field_key();
		$meta_value = $this->get_ratio($post->ID);

		$meta_params = [
			'label'			=> __('Media Ratio', 'zu-media'),
			'show_in_edit' 	=> true,
			'show_in_modal' => true,
			'helps' 		=> '',
			'input' 		=> 'html',
			'html' 			=> zu_sprintf(
				'<input name="attachments[%1$s][%2$s]"
					metaid="%1$s"
					id="attachments-%1$s-%2$s"
					class="mplus_metaid"
					type="text"
					value="%3$s" readonly>',
				$post->ID,
				$meta_key,
				$meta_value
			)
		];

		$form_fields[$meta_key] = $meta_params;
		return $form_fields;
	}

	public function fields_save($post, $attachment) {
		// Save custom field to post_meta
		$meta_key = $this->field_key();
		if(isset($attachment[$meta_key])) $this->save_ratio($post['ID'], $attachment[$meta_key]);
		return $post;
	}

	// Public interface for ratio field ---------------------------------------]

	public function get_ratio($post_or_attachment_id = null) {
		$attachment_id = $this->snippets('get_attachment_id', $post_or_attachment_id);
		$ratio_value = get_post_meta($attachment_id, $this->field_key(true), true);
		if($ratio_value !== false && empty($ratio_value)) {
			$ratio_value = $this->get_ratio_name($attachment_id);
			$this->save_ratio($attachment_id, $ratio_value);
		}
		return $ratio_value;
	}

	public function is_landscape($post_or_attachment_id = null, $limit = '3:2') {
		$attachment_id = $this->snippets('get_attachment_id', $post_or_attachment_id);
		$metadata = wp_get_attachment_metadata($attachment_id);
		if(empty($metadata)) return false;
		return $this->is_landscape_ratio($metadata['width'], $metadata['height'], $limit);
	}

	public function is_landscape_ratio($width, $height, $limit = '3:2') {
		return $this->check_ratio($limit, absint($width), absint($height), false, true);
	}

	public function get_all_landscaped() {
		if(empty($this->all_landscaped)) {
			$this->all_landscaped = array_keys(array_filter($this->get_attachments(false),
				function($val) { return  $val['landscaped'] ?? false; }
			));
		}
		return $this->all_landscaped;
	}

}
