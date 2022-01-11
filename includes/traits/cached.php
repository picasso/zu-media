<?php

// 'Cached' helpers -----------------------------------------------------------]

trait zu_MediaCached {

	// cache them for 12 hours (recommended)
	private $cache_time = HOUR_IN_SECONDS * 12;

	private $cachekeys = [
		'attachments'	=> 'attachments',
		'sizes'			=> 'sizes',
		'folders'		=> 'folders',
		'galleries'		=> 'galleries',
	];

	private function init_cachekeys() {
		$prefix = $this->prefix;
		$versionkey = str_replace('.', '_', $this->version);

		$this->cachekeys['attachments'] = sprintf('%s_cad_%s', $prefix, $versionkey);
		$this->cachekeys['sizes'] = sprintf('%s_sizes_%s', $prefix, $versionkey);
		$this->cachekeys['folders'] = sprintf('%s_folders_%s_%s', $prefix, $versionkey, $this->snippets('get_lang', 'nolang'));
		$this->cachekeys['galleries'] = sprintf('%s_galleries_%s_%s', $prefix, $versionkey, $this->snippets('get_lang', 'nolang'));

		add_action('add_attachment', [$this, 'reset_cached']);			// reset all cached when new image added
		add_action('delete_attachment', [$this, 'reset_cached']);		// or deleted

		// reset all cached
		add_action('zumedia_reset_cached', [$this, 'reset_cached']);
		// reset cached collections only (folders, galleries)
		add_action('zumedia_reset_collections', [$this, 'reset_cached_collections']);
	}

	private function get_cached_memory($stats) {
		// no accurate, but an easy way to find memory used by an cached objects
		$attachments_cached = $this->is_option('disable_cache') ? 0 : strlen(serialize($this->get_attachments(false)));
		$sizes_cached = $this->is_option('disable_cache') ? 0 : $this->get_cached('sizes');
		if($sizes_cached !== 0) $sizes_cached = $sizes_cached !== false ? strlen(serialize($sizes_cached)) : 0;

		$cached_memory = ($stats['memory'] ?? 0) + $attachments_cached + $sizes_cached;
		return $this->snippets('format_bytes', $cached_memory, 1, true, '**%s** %s');
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

	public function reset_cached($collections_only = false) {
		$stats = $this->folders ? $this->folders->stats() : [];
		$cached_memory = $this->get_cached_memory($stats);
		$attachment_count = count($this->get_attachments());

		foreach($this->cachekeys as $key => $cachekey) {
			if($collections_only) {
				if(in_array($key, ['folders', 'galleries'])) delete_transient($cachekey);
			} else {
				delete_transient($cachekey);
			}
		}
		$deleted = [
			$collections_only ? null : "$attachment_count attachments",
			$stats['info'] ?? null, $cached_memory
		];
		$message = sprintf(
			'%1$s data were cleared (<strong>%2$s</strong>).',
			$collections_only ? 'Сached data of all collections' : 'All cached data',
			implode(', ', array_filter($deleted))
		);
		return $this->create_notice('success', $message);
	}

	public function reset_cached_collections() {
		return $this->reset_cached(true);
	}
}
