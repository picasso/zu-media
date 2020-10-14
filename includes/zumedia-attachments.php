<?php
// NOTE: разобраться с zuplus_options_updated
// NOTE: разобраться с $_mplus_caq, $_mplus_cache_time

// Attachments helpers --------------------------------------------------------]

trait zu_MediaAttachments {

	private $full_key = 'full';
	private $attachment_baseurl = '';

	// cache them for 12 hours (recommended)
	private $cache_time = HOUR_IN_SECONDS * 12;

	private $cachekeys = [
		'attachments'	=> 'attachments',
		'folders'		=> 'folders',
		'galleries'		=> 'galleries',
	];

	private function init_cachekeys() {
		// global $_mplus_caq, $_mplus_cache_time;

		$prefix = $this->prefix;
		$versionkey = str_replace('.', '_', $this->version);

		$this->cachekeys['attachments'] = sprintf('%s_cad_%s', $prefix, $versionkey);
		$this->cachekeys['folders'] = sprintf('%s_folders_%s_%s', $prefix, $versionkey, $this->snippets('get_lang', 'none'));
		$this->cachekeys['galleries'] = sprintf('%s_galleries_%s_%s', $prefix, $versionkey, $this->snippets('get_lang', 'none'));

		// $_mplus_caq = $this->cachekeys['attachments'];
		// $_mplus_cache_time = $this->cache_time;

		add_action('add_attachment', [$this, 'reset_cached']);			// reset all cached when new image added
		add_action('delete_attachment', [$this, 'reset_cached']);		// or deleted
		// add_action('zuplus_options_updated', [$this, 'reset_cached']);	// or when options updated
	}

	private function init_baseurl() {
		$uploads_dir = wp_upload_dir();
		$this->attachment_baseurl = str_replace('http:', ':', $uploads_dir['baseurl'] . '/');
		$this->attachment_baseurl = str_replace('https:', ':', $this->attachment_baseurl);
	}

	private function get_cached_memory($stats) {
		// no accurate, but an easy way to find memory used by an cached objects
		$attachments_cached = $this->is_option('disable_cache') ? 0 : strlen(serialize($this->get_attachments(false)));
		$cached_memory = ($stats['memory'] ?? 0) + $attachments_cached;
		return $this->snippets('format_bytes', $cached_memory, 1);
	}

	public function get_cached($cachekey) {
		return $this->is_option('disable_cache') ? false : get_transient($this->cachekeys[$cachekey] ?? $this->cachekeys['folders']);
	}

	public function set_cached($cachekey, $data) {
		if($this->is_option('disable_cache')) return;
		set_transient($this->cachekeys[$cachekey] ?? $this->cachekeys['folders'], $data, $this->cache_time);
	}

	public function delete_cached($cachekey) {
		if(isset($this->cachekeys[$cachekey])) delete_transient($this->cachekeys[$cachekey]);
	}

	public function reset_cached() {
		$stats = $this->folders ? $this->folders->stats() : [];
		$cached_memory = $this->get_cached_memory($stats);

		$this->delete_cached('attachments');
		$this->delete_cached('folders');
		$this->delete_cached('galleries');

		$message = sprintf(
			'All cached data were cleared%1$s.',
			empty($stats) ? '' : sprintf(' (<strong>%1$s, %2$s</strong>)', $stats['info'], $cached_memory)
		);
		return $this->create_notice('success', $message);
	}

	public function get_attachments($keys_only = true) {

		// Get any existing copy of our transient data
		$attachments = $this->get_cached('attachments');

		// It wasn't there, so regenerate the data and save the transient
		if($attachments === false) {

			$query_args = [
				'post_type'   		=> 'attachment',
				'post_status' 		=> 'inherit',
				'fields'      		=> 'ids',
				'posts_per_page'	=> -1,
			];
			$attachment_query = new WP_Query($query_args);
			$attachment_query = $attachment_query->have_posts() ? $attachment_query->posts : [];

			$attachments = [];
			foreach($attachment_query as $post_id) {

				$meta = wp_get_attachment_metadata($post_id);
				// Returns an array (url, width, height, is_intermediate)
				$image = wp_get_attachment_image_src($post_id, $this->full_key);

				$attachments[$post_id] = [];
				$attachments[$post_id]['file'] = basename($meta['file']);
				$attachments[$post_id]['cropped'] = wp_list_pluck($meta['sizes'], 'file');
				$attachments[$post_id]['landscaped'] = ($image !== false && $this->is_landscape($image[1], $image[2])) ? true : false;
			}
			$this->set_cached('attachments', $attachments);
		}

		return empty($attachments) ? [] : ($keys_only ? array_keys($attachments) : $attachments);
	}

	public function attachment_id_from_url($url, $attachments = null) {

		$attachment_id = 0;
		$url = str_replace('&quot;', '', $url);

		// Is URL in uploads directory?
		if(strpos($url, $this->attachment_baseurl) !== false) {

			$file = basename($url);
			// maybe use '$attachments' from calling function
			$attachments = empty($attachments) ? $this->get_attachments(false) : $attachments;

			if(!empty($attachments)) {

				foreach($attachments as $post_id => $data) {
					if($data['file'] === $file || in_array($file, $data['cropped'])) {
						$attachment_id = $post_id;
						break;
					}
				}
			}
		}

		return $attachment_id;
	}

	public function attachment_id_from_class_or_url($image) {

		// Try in class first
	    $attachment_id = preg_match('/wp-image-([0-9]+)/i', $image, $class_id) ? absint($class_id[1]) : 0;
		// Then check if found id is in existing ids
		$attachments = $this->get_attachments(false);
	    $attachment_id = in_array($attachment_id, array_keys($attachments)) ? $attachment_id : 0;
		if($attachment_id == 0) {
			$imgsrc = preg_match('/src=[\"|\']([^\"|\']+)/i', $image, $imgsrc) ? $imgsrc[1] : '';
			list($imgsrc) = explode('?', $imgsrc);

			// Return early if we couldn't get the image source
			if(empty($imgsrc)) return 0;

			$attachment_id = $this->attachment_id_from_url($imgsrc, $attachments);
		}

		return $attachment_id;
	}

	public function delete_attachment($attachment_id) {

		$result = wp_delete_attachment($attachment_id);

		if($result === false) return $this->create_notice('error', sprintf('Failed to delete attachment with ID <strong>%1$s</strong>', $attachment_id));
		else {
			$title = $this->snippets('convert_lang_text', $result->post_title) ?? $result->post_title;
			$message = sprintf('Attachment <span>"%1$s"</span> with ID <strong>%2$s</strong> was deleted', $title, $attachment_id);
			return $this->create_notice('success', $message);
		}
	}

	public function detach_attachment($attachment_id) {

		// Update the post into the database
		$result = wp_update_post(['ID' => $attachment_id, 'post_parent' => 0]);

		if(is_wp_error($result)) {
			$this->create_notice('error', sprintf(
				'Failed to detach attachment with ID <strong>%1$s</strong> (%2$s)',
				$attachment_id,
				implode(' ', $result->get_error_messages())
			));
		} else {
			$title = get_the_title($attachment_id);
			$title = $this->snippets('convert_lang_text', $title) ?? $title;
			$message = sprintf('Attachment <span>"%1$s"</span> with ID <strong>%2$s</strong> was detached', $title, $attachment_id);
			return $this->create_notice('success', $message);
		}
	}
}
