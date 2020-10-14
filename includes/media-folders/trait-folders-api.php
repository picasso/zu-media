<?php
// NOTE: решить с add_folder_rewrite???

// Init, Reset and Access helpers ---------------------------------------------]

trait zu_MediaFolderAPI {

	private $rewrite_key = 'albums';
	private $folders = [];
	private $galleries = [];

	private function update_cached() {
		// Cache existing folders & galleries
		$this->folders = $this->get_folders();
		$this->galleries = $this->get_galleries();
		$this->add_folder_rewrite();
	}

	public function add_folder_rewrite() {
	    add_rewrite_rule(
	        '^folder/([0-9]+)/?',
	        'index.php?pagename=folder&folder_id=$matches[1]',
	        'top');
	    add_rewrite_tag('%folder_id%', '([^&]+)');
	}

	// Folders (Albums) -------------------------------------------------------]

	public function get_folder_by_image_id($image_id) {

		foreach($this->folders as $folder) {
			if(in_array(strval($image_id), $folder['images'] ?? null)) return $folder;
		}
		return [];
	}

	public function get_folder_by_id($folder_id, $get_parent_from = []) {

		$as_parent_id = empty($get_parent_from) ? false : true;

		foreach(($as_parent_id ? $get_parent_from : $this->folders) as $folder) {
			if($folder_id == $folder[$as_parent_id ? 'parent_id' : 'id']) return $folder;
		}
		return [];
	}

	public function get_folders($parent_id = 0) {

		$folders = $this->call('get_cached', 'folders');

		if($folders !== false) return $parent_id == 0 ? $folders : $this->get_folder_by_id($parent_id, $folders);

		$folders = $have_childs = [];

        $terms = $this->get_folder_terms($parent_id);
        if($this->check_error($terms)) return $folders;

        foreach($terms as $folder) {
	        $folder_id = (int)$folder->term_id;
            $folder_childs = get_term_children($folder_id, $this->folders_category);
			if($this->check_error($folder_childs)) return $folders;

            $folder_images = get_objects_in_term($folder_id, $this->folders_category);
			if($this->check_error($folder_images)) return $folders;

			$have_childs = empty($folder_childs) ? $have_childs : array_merge($have_childs, [$folder_id]);
            $folders[] = [
            	'title' 		=> $folder->name,
            	'id' 			=> $folder_id,
            	'permalink' 	=> sprintf('/%2$s/%1$s/', $folder_id, $this->rewrite_key),
            	'parent_id' 	=> (int)$folder->parent,
            	'childs_count' 	=> count($folder_childs),
            	'childs' 		=> $folder_childs,
            	'images' 		=> $folder_images,
            ];
	    }

	    foreach($have_childs as $folder_id) {
			$child_folders = $this->get_folders($folder_id);
			$folders = array_merge($folders, $child_folders);
		}

		if($parent_id == 0) {
			$this->call('set_cached', 'folders', $folders);
		}

		return $folders;
	}

	public function get_all_images_in_folder($folder_id_or_folder, $include_subfolders = true) {

		$folder = isset($folder_id_or_folder['images']) ? $folder_id_or_folder : $this->get_folder_by_id($folder_id_or_folder);
		$images = $folder['images'] ?? [];
		if($include_subfolders) {
		    foreach($folder['childs'] ?? [] as $folder_id) {
				$child_images = $this->get_all_images_in_folder($folder_id);
				$images = array_merge($images, $child_images);
			}
		}
		return $images;
	}

	// Private folders --------------------------------------------------------]

	public function is_private_folder($folder) {
		// if album title is equal to '_private' returns 1, if album title only contains '_private' returns 2, otherwise false
		return (isset($folder['title'])
			&& stripos($folder['title'], '_private') !== false) ? (
				strlen($folder['title']) == 8 ? 1 : 2
			) : false;
	}

	public function get_private_images() {

		$folders = $this->get_folders();
		foreach($folders as $folder) {
			$images = array_map('absint', $this->get_all_images_in_folder($folder));
			if($this->is_private_folder($folder)) $private = empty($private) ? $images : array_merge($private, $images);
		}
		return empty($private) ? [] : array_unique($private);
	}

	public function is_private_image($image_id) {
		global $_mplus_private_images;

		if(empty($_mplus_private_images)) $_mplus_private_images = $this->get_private_images();

		return in_array($image_id, $_mplus_private_images);
	}

	// Galleries --------------------------------------------------------------]

	public function get_galleries($post_id = null) {

		$galleries = $this->call('get_cached', 'galleries');

		if($galleries !== false) return empty($post_id) ? $galleries : (isset($galleries[$post_id]) ? $galleries[$post_id] : []);

		$galleries = $images = [];
		$gallery_type = $this->get_option('gallery_type', 'pages');
		// 	$gallery_type может быть:
		// 		- либо 'posts' с установленным форматом 'gallery'
		// 		- либо все 'pages' (крайне неэффективно)
		// 		- либо 'pages' которые имеют родителя и у родителя 'slug' соответсвует одному из вариантов
 		// 			выбранному пользователем (portfolio, gallery, photos, albums, images)
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
			$private_images = $this->get_private_images();
			$galleries['all'] = empty($private_images) ? $images : array_diff_key($images, array_flip($private_images));
			$this->call('set_cached', 'galleries', $galleries);
		}
		return $galleries;
	}

	public function get_gallery_by_image_id($image_id) {

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
		$memory = $this->is_plugin_option('disable_cache') ? 0 : strlen(serialize($this->folders)) + strlen(serialize($this->galleries));
		return [
			'folders' 		=> $fcount,
			'galleries' 	=> $gcount,
			'memory'		=> $memory,
			'info'			=> sprintf('%1$s folders, %2$s galleries', $fcount, $gcount),
		];
	}
}
