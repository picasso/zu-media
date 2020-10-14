<?php

// Ajax actions ---------------------------------------------------------------]

trait zu_MediaFolderAjax {

	public function ajax_action() {

		$result = [];
		$params = $this->check_request(['operation']);

		if($params !== false) {
			switch($params['operation']) {
				case 'add_folder':
					$result = $this->add_folder();
					break;

				case 'edit_folder':
					$result = $this->edit_folder();
					break;

				case 'delete_folder':
					$result = $this->delete_folder();
					break;

				case 'select_folder':
					$result = $this->select_folder();
					break;

				case 'move_folder':
					$result = $this->move_folder();
					break;

				case 'move_files':
					$result = $this->move_files();
					break;

				default:
					$this->ajax_error(__('Unknown Ajax request', 'zumedia'), $params);
			}
		}

		$this->ajax_send($result);
	}

	// Check for missing params and cast them to required type
	private function check_request($params = []) {

		$this->ajax_error(null);
		$int_params = ['id', 'parent_id', 'receiving_id'];
		$string_params = ['name', 'operation', 'ids', 'path'];

		$missing = [];
		$converted = [];
		foreach($params as $key) {
			if(isset($_POST[$key])) {
				$converted[$key] =
					in_array($key, $int_params) ? (int)$_POST[$key] : (
					in_array($key, $string_params) ? esc_attr($_POST[$key]) : -1
				);
			} else {
				$missing[] = $key;
			}
		}

		if(empty($missing)) return $converted;

		return $this->ajax_error(__('Error in request parameters', 'zumedia'), $missing);
	}

	// Check for possible duplicates
	private function check_duplicates($name, $parent_id) {
        $children_of_parent = get_terms([
			'taxonomy' 	=> $this->folders_category,
			'fields' 	=> 'names',
			'get' 		=> 'all',
			'parent' 	=> $parent_id
		]);

		if(in_array($name, $children_of_parent)) {
			$this->ajax_error(__('This name already exists', 'zumedia'));
			return true;
		}
		return false;
	}

	// Reset cached folders data (called after the folders were modified)
	private function reset_cached_folders() {
		$this->call('delete_cached', 'folders');
	}

	// Add a new folder via Ajax
    private function add_folder() {

		$params = $this->check_request(['name', 'parent_id']);
		if($params === false) return false;

		extract($params, EXTR_PREFIX_ALL, 'term');

		if(empty($term_name)) $term_name = __('New folder', 'zumedia');

        $result = wp_insert_term($term_name, $this->folders_category, ['parent' => $term_parent_id]);

		// if WP_Error returned -> maybe the term existed prior
        if(is_wp_error($result)) return $this->ajax_error($result);

        $result = wp_update_term($result['term_id'], $this->folders_category);
        $term = get_term($result['term_id'], $this->folders_category);
		$this->reset_cached_folders();

		return [
			'status' 	=> true,
			'id'		=> $term->term_id,
			'parent_id'	=> $term->parent,
			'name'		=> $term->name,
		];
    }

    // Edit folder via Ajax
    private function edit_folder() {

		$params = $this->check_request(['name', 'id', 'parent_id']);
		if($params === false) return false;

		extract($params, EXTR_PREFIX_ALL, 'term');

		if($this->check_duplicates($term_name, $term_parent_id)) return false;

        $result = wp_update_term($term_id, $this->folders_category, ['name' => $term_name]);
		if(is_wp_error($result)) return $this->ajax_error($result);

        $term = get_term($result['term_id'], $this->folders_category);
		$this->reset_cached_folders();

		return [
			'status' 	=> true,
			'id'		=> $term->term_id,
			'name'		=> $term->name,
		];
    }

    // Delete folder via Ajax
    private function delete_folder() {

		$params = $this->check_request(['id', 'parent_id']);
		if($params === false) return false;

		extract($params, EXTR_PREFIX_ALL, 'term');

        $children = get_term_children($term_id, $this->folders_category);
        if(is_array($children) && count($children) > 0) {
			return $this->ajax_error(__('Unable to delete non-empty folder', 'zumedia'));
        }

		// Сейчас 'non_empty' не позволяет удалить фолдер с изображениями (без учета вложенных фолдеров)
		// если фолдер содержит другие фолдеры, то данная опция не влияет (пока)
		// и удалить фолдер с вложенными фолдерами сейчас невозможно (может изменится в будущем?)
		if(!$this->is_option('non_empty')) {
			$folder = $this->get_folder_by_id($term_id);
			if(count($folder['images'] ?? [])) {
				return $this->ajax_error(__('Unable to delete non-empty folder', 'zumedia'));
	        }
		}

        $children_of_parent = get_term_children($term_parent_id, $this->folders_category);
		$result = wp_delete_term($term_id, $this->folders_category);

		if($result === false || is_wp_error($result)) {
			return $this->ajax_error($result === false ? __('Requested folder does not exist', 'zumedia') : $result);
        }

		$this->reset_cached_folders();

		return [
			'status' 	=> true,
			'id'		=> $term_id,
			'parent_id'	=> $term_parent_id,
			'is_empty'	=> count($children_of_parent) === 1,
		];
    }

	// Move a folder via Ajax
    private function move_folder() {

		$params = $this->check_request(['id', 'receiving_id', 'name']);
		if($params === false) return false;

		extract($params, EXTR_PREFIX_ALL, 'term');

        $children_of_folder = $this->children_of_folder($term_id);
        if(in_array($term_receiving_id, $children_of_folder)) {
			return $this->ajax_error(__('Cannot move folder into itself', 'zumedia'));
        }

		if($this->check_duplicates($term_name, $term_receiving_id)) return false;

        $result = wp_update_term($term_id, $this->folders_category, ['parent' => $term_receiving_id]);
		if(is_wp_error($result)) return $this->ajax_error($result);

		$this->reset_cached_folders();

		return [
			'status' 			=> true,
			'id'				=> $term_id,
			'receiving_id'		=> $term_receiving_id,
		];
    }

	// Move a file via Ajax
    private function move_files() {

		$params = $this->check_request(['ids', 'receiving_id']);
		if($params === false) return false;

		extract($params, EXTR_PREFIX_ALL, 'term');

        $moved = true;
        foreach(explode(',', $term_ids) as $id) {
            wp_delete_object_term_relationships((int)$id, $this->folders_category);

            if($term_receiving_id === 0) continue;
			$result = wp_set_object_terms((int)$id, $term_receiving_id, $this->folders_category, true);
			if(is_wp_error($result)) {
				$this->ajax_error($result);
				$moved = false;
	        }
        }

		if($moved) $this->reset_cached_folders();

		return $moved ? [
			'status' 			=> true,
			'receiving_id'		=> $term_receiving_id,
			'ids'				=> $term_ids,

		] : false;
    }

	// Select current folder via Ajax
    private function select_folder() {

		$params = $this->check_request(['id']);
		if($params === false) return false;

		extract($params, EXTR_PREFIX_ALL, 'term');

		$this->set_selected_id($term_id);

		return [
			'status' 	=> true,
			'id'		=> $term_id,
		];
    }
}
