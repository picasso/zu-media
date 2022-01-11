<?php

// Taxonomy & Terms helpers ---------------------------------------------------]

trait zu_MediaFolderHelpers {

	private $orderby = 'name';
	private $order = 'ASC';
	private $query_marker = 'zumedia_taxonomy';
	private $convertible_category = 'wpmf-category';

	private function get_selected_id() {
		return $_SESSION['selectedId'] ?? 0;
	}

	private function set_selected_id($id = null) {
		if(is_null($id)) $id = $this->get_option('selectedId', 0);
		else $this->set_option('selectedId', $id);
		$_SESSION['selectedId'] = $id;
	}

	public function register_taxonomy($taxonomy = null) {
		if(empty($taxonomy)) $taxonomy = $this->folders_category;
		register_taxonomy($taxonomy, 'attachment', ['hierarchical' => true, 'show_in_nav_menus' => false, 'show_ui' => false]);
	}

	private function unregister_taxonomy($taxonomy = null) {
		if(empty($taxonomy)) $taxonomy = $this->folders_category;
		unregister_taxonomy_for_object_type($taxonomy, 'attachment');
		unregister_taxonomy($taxonomy);
	}

	private function reset_taxonomy($taxonomy = null, $andRegister = true, $terms = null) {

		if(empty($taxonomy)) $taxonomy = $this->folders_category;
		$terms = empty($terms) ? $this->generate_sorted_tree($taxonomy) : $terms;
		foreach($terms as $term) {
			wp_delete_term($term->term_id, $taxonomy);
		}
		$this->unregister_taxonomy($taxonomy);
		if($andRegister) $this->register_taxonomy($taxonomy);
	}

	private function generate_sorted_tree($taxonomy = null) {
		if(empty($taxonomy)) $taxonomy = $this->folders_category;
		$tree = $this->generate_tree(get_categories(['taxonomy' => $taxonomy, 'hide_empty' => false]));
		return $this->sort_parents($tree);
	}

	private function get_folder_terms($parent_id) {
		return get_terms([
			'taxonomy' 		=> $this->folders_category,
			'parent'		=> $parent_id,
			'orderby'		=> $this->orderby,
			'order'			=> $this->order,
			'hide_empty'	=> false
		]);
	}

	private function children_of_folder($parent_id, $collected = []) {
		$children_of_folder = $this->get_folder_terms($parent_id);
		if(count($children_of_folder) > 0) {
			foreach($children_of_folder as $child) {
				$collected[] = $child->term_id;
				$collected = $this->children_of_folder($child->term_id, $collected);
			}
		}
		return $collected;
	}

	private function generate_tree($terms, $parent = 0, $depth = 0, $limit = 0) {
		if($this->is_error($terms)) return [];
		if($limit > 100) return '';  // Prevent an endless recursion
		$tree = [];
		for($i = 0, $ni = count($terms); $i < $ni; $i++) {
			if(!empty($terms[$i])) {
				if($terms[$i]->parent == $parent) {
					$terms[$i]->name = $terms[$i]->name;
					$terms[$i]->depth = $depth;
					$tree[] = $terms[$i];
					$node = $this->generate_tree($terms, $terms[$i]->term_id, $depth + 1, $limit + 1);
					$tree = array_merge($tree, $node);
				}
			}
		}
		return $tree;
	}

	// sort parents before children
	// http://stackoverflow.com/questions/6377147/sort-an-array-placing-children-beneath-parents
	private function sort_parents($items, &$result = [], $parent = 0, $depth = 0) {
		foreach($items as $key => $item) {
			if($item->parent == $parent) {
				$item->depth = $depth;
				array_push($result, $item);
				unset($items[$key]);
				$this->sort_parents($items, $result, $item->term_id, $depth + 1);
			}
		}
		return $result;
	}

	private function fix_orphaned($remove = false) {

		$this->ajax_error(null);
		$report = [
			'fixed_taxonomy'		=> $this->folders_category,
			'fixed_attachments'		=> 0,
			'removed_attachments'	=> 0,
			'attachments'			=> 0,
			'errors'				=> 0,
		];
		// first create an array of all the 'id' values for the taxonomy folders
		$folders = array_map(function($folder) { return $folder['id']; }, $this->get_folders());

		// then search and fix orphaned attachments
        $attachments = $this->call_parent('get_attachments');
		$report['attachments'] = count($attachments);

        foreach($attachments as $attachment_id) {
			$item_terms = get_the_terms($attachment_id, $this->folders_category);
			if($item_terms === false) continue;
			if($this->is_error_with_report($item_terms, $report)) return false;
			foreach($item_terms as $term) {
				if(in_array($term->term_id, $folders)) continue;
				if($remove) {
					$result = wp_delete_attachment($attachment_id);
					if($this->is_error_with_report($result, $report)) return false;
					$report['removed_attachments'] += 1;
				} else {
					$result = wp_set_object_terms($attachment_id, 0, $this->folders_category, true);
					if($this->is_error_with_report($result, $report)) return false;
					$report['fixed_attachments'] += 1;
				}
			}
        }

		$message = sprintf('<span>%1$s</span> <strong>orphaned attachments</strong> were fixed%2$s (<strong>of %3$s</strong>)',
			$report['fixed_attachments'],
			$report['removed_attachments'] ? sprintf(', <span>%1$s</span> attachments were removed', $report['removed_attachments']) : '',
			$report['attachments']
		);

		return $this->create_notice('info', $message);
	}

	public function is_convertible($taxonomy = null) {
		if($this->is_option('was_converted')) return false;
		if(empty($taxonomy)) $taxonomy = $this->convertible_category;
		$check = $this->check_existed_terms(false, [$taxonomy]);
		return (bool)($check[$taxonomy] ?? 0);
	}

	private function check_existed_terms($ajax = true, $from = ['zumedia-folders', 'wpmf-category']) {

		$this->ajax_error(null);
		$report = [];

        $attachments = $this->call_parent('get_attachments');

		foreach($from as $taxonomy) {
			$report[$taxonomy] = 0;
			$not_registered = !taxonomy_exists($taxonomy);
			if($not_registered) $this->register_taxonomy($taxonomy);
			foreach($attachments as $attachment_id) {
				$item_terms = get_the_terms($attachment_id, $taxonomy);
				if($item_terms === false) continue;
				if($this->is_error_with_report($item_terms, $taxonomy)) return false;
				$report[$taxonomy] += 1;
	        }
			if($not_registered) $this->unregister_taxonomy($taxonomy);
        }

		if(!$ajax) return $report;

		$message = sprintf('Out of <span>%1$s</span> attachments:  %2$s',
			count($attachments),
			implode(', ', array_map(function($key, $value) {
				return sprintf('<span>%2$s</span> of <strong>%1$s</strong> taxonomy', $key, $value);
			}, array_keys($report), $report))
		);
		return $this->create_notice('info', $message);
	}

	private function convert_taxonomy($remove, $from_taxonomy = null) {

		if(empty($from_taxonomy)) $from_taxonomy = $this->convertible_category;

		$this->set_option('was_converted', false);
		$this->ajax_error(null);
		$report = [
			'converted_taxonomy'	=> $from_taxonomy,
			'converted_terms'		=> 0,
			'converted_items'		=> 0,
			'removed_taxonomy'		=> null,
			'removed_from_items'	=> 0,
			'errors'				=> 0,
		];

		// Maybe register '$from_taxonomy'
		if(!taxonomy_exists($from_taxonomy)) $this->register_taxonomy($from_taxonomy);
        $from_terms = $this->generate_sorted_tree($from_taxonomy);

		// Reset the current taxonomy to its original state
		$this->reset_taxonomy();
		$converted = ['0' => 0];
		// First create copies of all terms under the new taxonomy
		foreach($from_terms as $term) {
			$result = wp_insert_term($term->name, $this->folders_category, ['parent' => $converted[$term->parent]]);
			if($this->is_error_with_report($result, $report)) return false;
			$converted[$term->term_id] = $result['term_id'];
			$report['converted_terms'] += 1;
		}

		// Then update the slug for new terms
		foreach($from_terms as $term) {
			$converted_id = $converted[$term->term_id];
			$converted_term = get_term($converted_id, $this->folders_category);
			if($this->is_error_with_report($converted_term, $report)) return false;

			$slug = $this->snippets('translit', $converted_term->name);
			$result = wp_update_term($converted[$term->term_id], $this->folders_category, [
				'slug' => wp_unique_term_slug($slug, $converted_term),
			]);
			if($this->is_error_with_report($result, $report)) return false;
		}

		// and when all new terms are created and sorted -> update attachments
		$attachments = $this->call_parent('get_attachments');
        foreach($attachments as $attachment_id) {
			$item_terms = get_the_terms($attachment_id, $from_taxonomy);
			if($item_terms === false) continue;
			if($this->is_error_with_report($item_terms, $report)) return false;
			foreach($item_terms as $term) {
				$result = wp_set_object_terms($attachment_id, $converted[$term->term_id], $this->folders_category, true);
				if($this->is_error_with_report($result, $report)) return false;
				$report['converted_items'] += 1;
				if($remove) {
					wp_delete_object_term_relationships($attachment_id, $from_taxonomy);
					$report['removed_from_items'] += 1;
				}
			}
        }

		// Remove the previous category if $remove is true
		if($remove) {
			$this->reset_taxonomy($from_taxonomy, false, $from_terms);
			$report['removed_taxonomy'] = $from_taxonomy;
		}

		$message = sprintf('<span>%2$s</span> terms were converted from <strong>%1$s</strong> taxonomy, <span>%3$s</span> attachments were updated (<strong>of %4$s</strong>)%5$s',
			$report['converted_taxonomy'],
			$report['converted_terms'],
			$report['converted_items'],
			count($attachments),
			$remove ? sprintf('<br/>taxonomy <strong>%1$s</strong> was removed', $report['removed_taxonomy']) : ''
		);

		$this->set_option('was_converted', true);
		return $this->create_notice('success', $message);
	}

	private function is_error_with_report($error, &$report) {
		if($this->is_error($error)) {
			if(isset($report['errors'])) $report['errors'] += 1;
			$this->ajax_error($error, is_string($report) ? $report : null);
			return true;
		}
		return false;
	}
}
