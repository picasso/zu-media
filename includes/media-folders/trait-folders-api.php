<?php

// Init, Reset and Access helpers ---------------------------------------------]

trait zu_MediaFolderAPI {

	private $folders = [];
	private $galleries = [];
	private $private_images = [];
	private $public_images = [];

	public function update_cached() {
		// cache existing folders & galleries
		// folders should always be the first since 'private' images are created there
		$this->get_folders();
		$this->get_galleries();
	}

	public function add_folder_rewrite() {

		if($this->is_option('add_rewrite')) {
			global $wp_post_types;

			// we need to change the has_archive flag for 'post_type = attachment' to
			// the created WP_Query worked correctly with the is_archive() function

			// empirically, it was found that such changes work only within the 'init' action
			// but since 'add_folder_rewrite' is called from 'init', it is safe to change here
			$attachment_type = &$wp_post_types['attachment'];
			$attachment_type->has_archive = true;

			// Do note, if you do not see the new rewrite rules taking effect,
			// you might have to flush the rewrite rules, by calling the flush_rewrite_rules() function.
			// Flushing the rewrite rules, updates the rules in the database.
			// Your changes might not be recognized, until you do so. But this is an expensive operation.
			// So, it is best to call this rule when a plugin is activated and deactivated.
			$rewrite = $this->get_option('rewrite', '');
		    add_rewrite_rule(
		        "^{$rewrite}/([0-9]+)/?([^/]*)/?",
		        "index.php?post_type=attachment&{$rewrite}_id=\$matches[1]&{$rewrite}_sub=\$matches[2]",
		        'top');
		    add_rewrite_tag("%{$rewrite}_id%", '([^&]+)');
			add_rewrite_tag("%{$rewrite}_sub%", '([^&]+)');
		}
	}

	// Folders (Albums) -------------------------------------------------------]

	public function get_folder_by_attachment_id($image_id) {
		foreach($this->folders as $folder) {
			if(in_array(absint($image_id), $folder['images'] ?? null)) return $folder;
		}
		return [];
	}

	public function get_folder($folder_id) {
		return $this->folders[absint($folder_id)] ?? null;
	}

	// NOTE: старая версия!!
	// public function get_folder_by_id($folder_id, $get_parent_from = []) {
	// 	$as_parent_id = empty($get_parent_from) ? false : true;
	// 	$folder_id = absint($folder_id);
	// 	foreach(($as_parent_id ? $get_parent_from : $this->folders) as $folder) {
	// 		if($folder_id === $folder[$as_parent_id ? 'parent_id' : 'id']) return $folder;
	// 	}
	// 	return [];
	// }

	public function get_folder_props($folder_id, $keys, $single = false) {
		$folder = $this->get_folder($folder_id);
		if(is_array($folder)) {
			$props = $this->snippets('array_pick_keys', $folder, $keys, true);
			return $single ? $props[0] : $props;
		}
		return null;
	}

	private function get_childs($terms, $parent_id = 0) {
		$childs = [];
		foreach($terms as $folder) {
			if($folder->parent === $parent_id) {
				$childs[] = (int)$folder->term_id;
				$grand_childs = $this->get_childs($terms, (int)$folder->term_id);
				array_push($childs, ...$grand_childs);
			}
		}
		return $childs;
	}

	public function folder_exists($folder_id) {
		return array_key_exists(absint($folder_id), $this->folders); // column($this->folders, 'id');
		 // $ids =
		// return in_array($folder_id, $ids);
	}

	public function get_folder_permalink($folder_id, $with_check = true) {
		if($with_check && !$this->folder_exists($folder_id)) return false;
		return sprintf('/%2$s/%1$s/', $folder_id, $this->get_option('rewrite', '?'));
	}

	public function get_folders() {

		$folders = $this->call_parent('get_cached', 'folders');

		if($folders === false) {
			$folders = [];
			$terms = $this->generate_sorted_tree();
			$index = 1;
	        foreach($terms as $folder) {
		        $folder_id = (int)$folder->term_id;
				$folder_childs = $this->get_childs($terms, $folder_id);

	            $folder_images = get_objects_in_term($folder_id, $this->folders_category);
				if($this->is_error($folder_images)) return $folders;

	            $folders[$folder_id] = [
	            	'title' 		=> $folder->name,
	            	'id' 			=> $folder_id,
					'order'			=> $index++,
	            	'permalink' 	=> $this->get_folder_permalink($folder_id, false),
	            	'parent_id' 	=> (int) $folder->parent,
	            	'childs_count' 	=> count($folder_childs),
	            	'childs' 		=> $folder_childs,
	            	'images' 		=> wp_parse_id_list($folder_images),
					'meta'			=> $this->get_folder_meta($folder_id, false),
	            ];
		    }
			$this->call_parent('set_cached', 'folders', $folders);
		}

		$this->folders = $folders;
		$this->split_public_and_private_images($folders);
		return $folders;
	}

	public function get_all_images_in_folder($folder_id_or_folder, $include_subfolders = false) {
		$folder = isset($folder_id_or_folder['images']) ? $folder_id_or_folder : $this->get_folder($folder_id_or_folder);
		$images = $folder['images'] ?? [];
		if($include_subfolders) {
		    foreach($folder['childs'] ?? [] as $folder_id) {
				$child_images = $this->get_all_images_in_folder($folder_id, $include_subfolders);
				$images = array_merge($images, $child_images);
			}
		}
		return array_map('absint', $images);
	}

	// Private(locked) folders ------------------------------------------------]

	public function is_private_folder($folder_id) {
		if(!$this->folders) return false;
		$folder = $this->get_folder($folder_id);
		return empty($folder) ? false : $this->is_private($folder);
	}

	public function is_private_image($image_id) {
		return in_array(absint($image_id), $this->private_images);
	}

	public function get_all_images($private_only = false) {
		return $private_only ? $this->private_images : $this->public_images;
	}

	private function is_private($folder) {
		$is_private = $folder['meta']['lock'] ?? false;
		$need_recursion = $this->is_option('inherit_privacy') && $folder['parent_id'] !== 0;
		return $is_private ? true : ($need_recursion && $this->is_private_folder($folder['parent_id']));
	}

	private function split_public_and_private_images($all_folders = null) {
		$folders = $all_folders ?? $this->get_folders();
		$private = [];
		$public = [];
		foreach($folders as $folder) {
			if($this->is_private($folder)) {
				$private = array_merge($private, $this->get_all_images_in_folder($folder));
			} else {
				$public = array_merge($public, $this->get_all_images_in_folder($folder));
			}
		}
		$this->private_images = array_unique($private);
		$this->public_images = array_unique($public);
	}

	// Galleries --------------------------------------------------------------]

	public function get_galleries($post_id = null) {
		$galleries = $this->call_parent('get_cached', 'galleries');

		if($galleries === false) {
			$galleries = $images = [];
			$gallery_type = $this->get_option('gallery_type', 'pages');
			// 	$gallery_type can be:
			// 		- or 'posts' with the format set to 'gallery'
			// 		- or all 'pages' (highly inefficient)
			// 		- or 'pages' which have a parent and the parent 'slug' matches one of the options
			// 			selected by user (portfolio, gallery, photos, albums, images)
			if($gallery_type === 'posts') 	{

				$args = [];
				$args['post_type'] = 'post';
				$args['tax_query'] = [
		        	[
		            	'taxonomy' 	=> 'post_format',
						'field' 	=> 'slug',
						'terms' 	=> ['post-format-gallery'],
			        ]
			    ];

				if(!empty($post_id)) $args['include'] = $post_id;
				$pages = get_posts($args);
			} else {
				$pages = empty($post_id) ? get_pages() : get_pages(['include' => $post_id]);
			}

			foreach($pages as $page) {
				// except front_page
				if($gallery_type === 'pages' && is_front_page()) continue;
				// all 'pages' or only childs of 'portfolio', 'gallery', 'albums' and etc. slug
				else if($gallery_type !== 'pages' && !$this->snippets('is_child_of_slug', $gallery_type, $page->ID)) continue;

				$gallery = $this->snippets('get_post_gallery', $page->ID);
				if(!empty($gallery) && isset($gallery['ids'])) {

					$galleries[$page->ID] = [
						'title' 	=> $page->post_title,
						'permalink' => get_permalink($page->ID),
						'images' 	=> wp_parse_id_list($gallery['ids'])
					];
					// keep block name if presented
					if(isset($gallery['_block'])) $galleries[$page->ID]['_block'] = $gallery['_block'];

					$ids = array_map('strval', wp_parse_id_list($gallery['ids']));
					foreach($ids as $image_id) {
						if(isset($images[$image_id])) $images[$image_id][] = $page->ID;
						else $images[$image_id] = [$page->ID];
					}
				}
			}

			if(empty($post_id)) {
				$galleries['all'] = empty($this->private_images) ? $images : array_diff_key($images, array_flip($this->private_images));
				$this->call_parent('set_cached', 'galleries', $galleries);
			}
		}

		$this->galleries = $galleries;
		return empty($post_id) ? $galleries : (isset($galleries[$post_id]) ? $galleries[$post_id] : []);
	}

	public function get_gallery_by_attachment_id($image_id) {

		if(isset($this->galleries['all'][$image_id])) {
			$page_id = $this->galleries['all'][$image_id][0];
			return isset($this->galleries[$page_id]) ? $this->galleries[$page_id] : [];
		}
		return [];
	}

	public function stats() {
		$fcount = count($this->folders);
		// - 1 because we also have the 'all' key in galleries
		$gcount = count($this->galleries) - 1;
		// no accurate, but an easy way to find memory used by an cached objects
		$memory = $this->is_parent_option('disable_cache') ? 0 : strlen(serialize($this->folders)) + strlen(serialize($this->galleries));
		return [
			'folders' 		=> $fcount,
			'galleries' 	=> $gcount,
			'memory'		=> $memory,
			'info'			=> sprintf('%1$s folders, %2$s galleries', $fcount, $gcount),
		];
	}
}
