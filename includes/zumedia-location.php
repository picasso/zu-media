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
	        'hierarchical' 			=> true,
	        'query_var' 			=> 'true',
	        'rewrite' 				=> 'true',
	        'show_admin_column' 	=> 'true',
	    ];
	    register_taxonomy('location', 'attachment', $args);
	}

	protected function get_location_terms($post_id) {

		$locations = [];
		$terms = wp_get_post_terms($post_id, ['location']);

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

	protected function get_location_names($post_id, $as_array = false, $format = '%s', $with_link = false) {

		$terms = $this->get_location_terms($post_id);

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
					if($with_link) $location = sprintf(
						'<a href="%2$s" class="zu-location-link">%1$s</a>',
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
					if($with_link) $location = sprintf(
						'<a href="%2$s" class="zu-location-link">%1$s</a>',
						$location,
						get_term_link($slug, 'location')
					);
					$values[] = $location;
				}
			}
		}
		return $values;
	}

	protected function get_location_as_html($post_id, $lang = null, $glue = '') {

		if(empty($lang)) $lang = $this->snippets('get_lang');

		$locations = $this->get_location_names($post_id, false, '<span>%s</span>', true);
		$locations = implode($glue, $locations);
		return empty($lang) ? $locations : $this->snippets('convert_lang_text', $locations, $lang);
	}

	protected function get_location_as_text($post_id, $lang = null, $glue = ', ') {

		if(empty($lang)) $lang = $this->snippets('get_lang');

		$locations = $this->get_location_names($post_id, false, '%s', false);
		$locations = implode($glue, $locations);

		return (empty($lang) || $lang == -1) ? $locations : $this->snippets('convert_lang_text', $locations, $lang);
	}
}
