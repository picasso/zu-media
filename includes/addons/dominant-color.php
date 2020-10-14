<?php
// Calculate & save the dominant color for the images in Media Library
// Based on plugin by: Sunny Ripert & Guillaume Morisseau
// https://github.com/theamnesic/dominant-color
// Created by DR: 30/06/2015

class zu_MediaDominant extends zukit_Addon {

	private static $def_dominant_color = '#333333';
	private $meta_key = 'mplus_dominant_color';
	private $accurate = false;

	protected function config() {
		return ['name'	=> 'dominant'];
	}

	public static function default_color() {
		return self::$def_dominant_color;
	}

	protected function construct_more() {
		add_action('add_attachment', [$this, 'attachment_save']);
		add_filter('attachment_fields_to_edit', [$this, 'add_attachment_field'], 10, 2);
		add_filter('attachment_fields_to_save', [$this, 'save_attachment_field'], 10, 2);
	}

	public function admin_enqueue($hook) {
		if(in_array($hook, ['upload.php', 'post.php', 'post-new.php'])) {
			$this->admin_enqueue_script('dominant-color');
		}
	}

	public function ajax($action, $value) {
		if($action === 'zumedia_update_dominants') return $this->update_all_images();
		else return null;
	}

	// Dominant color functionality -------------------------------------------]

	public function rgb2hex($rgb) {
		// RVB to HEX
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
		return $hex;
	}

	public function dominant_color($path) {
		// Calculate the dominant color
		// Thanks to @onion2k on http://forums.devnetwork.net/viewtopic.php?t=39594

		// 	$i = imagecreatefromjpeg($path);  // works for jpeg only
		$i = @imagecreatefromstring(file_get_contents($path));
		if($i === false) return self::default_color();

		$rTotal = 0;
		$gTotal = 0;
		$bTotal = 0;
		$total = 0;

		for($x=0;$x<imagesx($i);$x++) {
			for($y=0;$y<imagesy($i);$y++) {
			  $rgb = imagecolorat($i,$x,$y);
			  $r = ($rgb >> 16) & 0xFF;
			  $g = ($rgb >> 8) & 0xFF;
			  $b = $rgb & 0xFF;

			  $rTotal += $r;
			  $gTotal += $g;
			  $bTotal += $b;
			  $total++;
			}
		}

		if($total != 0) {
			$rAverage = round($rTotal/$total);
			$gAverage = round($gTotal/$total);
			$bAverage = round($bTotal/$total);
		} else {
			$rAverage = 0;
			$gAverage = 0;
			$bAverage = 0;
		}

		return $this->rgb2hex([$rAverage, $gAverage, $bAverage]);
	}

	public function attachment_save($attachment_id) {

		// Callback that saves the dominant color in the meta
		if(wp_attachment_is_image($attachment_id)) {
			$image = wp_get_attachment_image_src($attachment_id, $this->accurate ? 'full' : 'large');
			if($image === false) return false;
			$color = $this->dominant_color($image[0]);
			return update_post_meta($attachment_id, $this->meta_key, $color);
		}
		return false;
	}

	public function add_attachment_field($form_fields, $post) {

		// Add fields to media uploader
		$form_fields[$this->meta_key] = [
			'label' => 'Dominant Color',
			'input' => 'html', //'text',
			'value' => $this->get_dominant_by_attachment_id($post->ID),
			'helps' => 'Use "?" to regenerate dominant color of the image',
            'html'  =>
				sprintf(
					'<input
						name="attachments[%1$s][%2$s]"
						metaid="%1$s"
						id="attachments-%1$s-%2$s"
						type="text"
						class="text mplus_metaid"
						value="%3$s"
					>',
                $post->ID,
                $this->meta_key,
                $this->get_dominant_by_attachment_id($post->ID)
            )
		];
		return $form_fields;
	}

	public function save_attachment_field($post, $attachment) {

		// Save values in media uploader
		if(isset($attachment[$this->meta_key])) {
			$color = sanitize_text_field($attachment[$this->meta_key]);
			$color = ($color === '?') ? $this->dominant_color(get_attached_file($post['ID'])) : $color;
			update_post_meta($post['ID'], $this->meta_key, $color);
		}
		return $post;
	}

	// Useful functions -------------------------------------------------------]

	public function get_dominant_by_attachment_id($attachment_id) {

		$meta = get_post_meta($attachment_id, $this->meta_key, true);
		return empty($meta) ? self::default_color() : $meta;
	}

	public function update_dominant_by_attachment_id($attachment_id) {
		return $this->attachment_save($attachment_id);
	}

	private function update_all_images() {

		$attachments = get_posts([
	        'post_type'      		=> 'attachment',
	        'post_status' 			=> 'any',
	        'post_mime_type' 		=> 'image',
	        'post_parent' 			=> null,
	        'posts_per_page' 		=> -1,
	    ]);

		$images_count = 0;

		// $attachments = array_slice($attachments, 0, 15);

		if($attachments) {
			foreach($attachments as $post) {
				$result = $this->attachment_save($post->ID);
				if($result !== false) $images_count++;
			}
		}
		wp_reset_postdata();

		$message = sprintf(!$images_count ?
			'No new dominant colors were generated (<strong>of %2$s</strong>).' :
			'Dominant color was generated for <strong>%1$s image%3$s</strong> (of %2$s)',
			$images_count,
			count($attachments),
			$images_count > 1 ? 's' : ''
		);

		return $this->create_notice('info', $message);
	}
}
