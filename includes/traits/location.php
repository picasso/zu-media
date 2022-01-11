<?php

// Location helpers -----------------------------------------------------------]

trait zu_MediaLocation {

	private function register_location() {

	    $labels = [
	        'name'              	=> 'Locations',
	        'singular_name'     	=> 'Location',
	        'search_items'      	=> 'Search Locations',
	        'all_items'        	 	=> 'All Locations',
	        'parent_item'       	=> 'Parent Location',
	        'parent_item_colon' 	=> 'Parent Location:',
	        'edit_item'         	=> 'Edit Location',
	        'update_item'       	=> 'Update Location',
	        'add_new_item'      	=> 'Add New Location',
	        'new_item_name'     	=> 'New Location Name',
	        'menu_name'         	=> 'Location',
	    ];

	    $args = [
	        'labels' 				=> $labels,
			'public'				=> true,
	        'hierarchical' 			=> true,
	        'query_var' 			=> 'location', //'true',
	        'rewrite' 				=> ['slug' => 'location'], //'true',
	        'show_admin_column' 	=> true, //'true',
			'args'					=> ['post_type'	=> 'attachment', 'post_status' => 'inherit'],
	    ];
	    register_taxonomy('location', 'attachment', $args);
// flush_rewrite_rules();
		// add_filter('attachment_fields_to_edit', [$this, 'location_field_edit'], 10, 2);
	}

	protected function get_location_terms($attachment_id) {

		$locations = [];
		$terms = wp_get_post_terms($attachment_id, ['location']);

		foreach($terms as $term) {
			// if the term have a parent, set the child term as attribute in parent term
			if($term->parent != 0)  {
				$locations[$term->parent] = $term;
			} else {
				// record the parent term
				$locations[0] = $term;
			}
		}

		$parent = 0;
		$sorted = [];

		do {
			if(!isset($locations[$parent])) break;

			$sorted[] = $locations[$parent];
			$parent = $locations[$parent]->term_id;

		} while($parent > 0);

		return $sorted;
	}

	protected function get_location_names($attachment_id, $as_array = false, $format = '%s', $with_link = false) {

		$terms = $this->get_location_terms($attachment_id);

		$locations = [];
		$names = [];
		foreach($terms as $term) {
			$names[$term->slug] = $term->name;
			if(isset($term->i18n_config)) {
				$locations[$term->slug] = $term->i18n_config['name']['ts'];
			} else {
				$locations[$term->slug] = $term->name;
			}
		}

		if($as_array) return $locations;

		$values = [];
		if(!empty($locations)) {
			$langs = $this->snippets('get_all_languages');
			if(empty($langs)) {
				// without languages
				foreach($locations as $slug => $location) {
					$location = sprintf($format, $location);
					if($with_link) $location = zu_sprintf(
						'<a href="%2$s" class="location-link">%1$s</a>',
						$location,
						get_term_link($slug, 'location')
					);
					$values[] = $location;
				}
			} else {
				// with languages
				foreach($locations as $slug => $location) {
					$text = '';
					foreach($langs as $lang) {
						$location_name = isset($location[$lang]) ? $location[$lang] : $names[$slug];
							// [:ru]Симметрия звука[:en]Symmetry of Sound[:]
						$text .= sprintf('[:%1$s]%2$s', $lang, $location_name);
					}
					$location = sprintf($format, $text .'[:]');
					if($with_link) $location = zu_sprintf(
						'<a href="%2$s" class="location-link">%1$s</a>',
						$location,
						get_term_link($slug, 'location')
					);
					$values[] = $location;
				}
			}
		}
		return $values;
	}

	public function get_location($attachment_id = null, $as_html = true, $lang = null, $glue = null) {
		if(empty($lang)) $lang = $this->snippets('get_lang');
		$glue = !is_null($glue) ? $glue : ($as_html ? '' : ', ');
		// if as text: $locations = $this->get_location_names($attachment_id, false, '%s', false);
		$locations = $this->get_location_names($attachment_id, false, $as_html ? '<span>%s</span>' : '%s', $as_html);
		$locations = implode($glue, $locations);
		return (empty($lang) || $lang == -1) ? $locations : $this->snippets('convert_lang_text', $locations, $lang);
	}

	public function get_media_taxonomy_link($term_or_folder, $params = null) {
		$params = $this->array_with_defaults($params, [
			'is_attachment'	=> false,
            'is_folder'		=> false,
		]);

		if($params['is_folder']) return $this->snippets('get_folder_permalink', $term_or_folder);
		if($term_or_folder instanceof WP_Term) {
			$url = get_term_link($term_or_folder);
			if($params['is_attachment'] && is_string($url)) {
				$tag_rewrite = $this->get_option('tag_rewrite', '');
				$category_rewrite = $this->get_option('category_rewrite', '');
				$url = str_replace(
					['/tag/', '/category/'],
					["/{$tag_rewrite}/", "/{$category_rewrite}/"],
					$url
				);
			}
			return is_string($url) ? $url : false;
		}
		return false;
	}

	public function location_field_edit($form_fields, $post) {

		// раньше это добавлялось вместе с ratio field но логично перенести это сюда (если еще нужно?)
		// $meta_key = $this->field_key();
		//
		// NOTE: разобраться с "keep location values for JS"
		// $form_fields[$meta_key]['html'] .=  sprintf(
		// '<div class="qtx-location" style="display:none">%1$s</div>', $this->get_location($post->ID, false, -1));

		// return $form_fields;
	}
}
