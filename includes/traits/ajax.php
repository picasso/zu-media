<?php

// Ajax/REST API helpers ------------------------------------------------------]

trait zu_MediaAjax {

	public function ajax_more($action, $value) {
		if($action === 'zumedia_reset_cached') return $this->reset_cached();
		else return null;
	}

	// https://github.com/picasso/zukit/wiki/%5BBasics%5D-Ajax#extending-zudata
	protected function extend_zudata($key, $params) {

		$result = null;
		if($this->folders) {
			// collect data for REST API
			switch($key) {
				case 'folders':
					$folder_id = $params['folderId'] ?? null;
					if(!empty($folder_id)) $result = $this->folders->get_folder_by_id($folder_id);
					else {
						$result = [];
						foreach($this->folders->get_folders() as $index => $value) {
							$value['order'] = $index;
							$key = $value['id'];
						    $result[$key] = $value;
						}
					}
					break;

				case 'galleries':
					$post_id = $params['postId'] ?? null;
					if(!empty($folder_id)) {
						$result = $this->folders->get_galleries($post_id);
						$result['id'] = absint($post_id);
					} else {
						$result = $this->folders->get_galleries($post_id);
						unset($result['all']);
						foreach($result as $key => $value) {
							$result[$key]['id'] = $key;
						}
					}
					break;

				// NOTE: Not implemented, not tested... it is not clear if anyone needs it
				// case 'folder_by_image':
				// 	$image_id = $params['imageId'] ?? 0;
				// 	$result = $this->folders->get_folder_by_image_id($image_id);
				// 	break;
				//
				// case 'all_images_in_folder':
				// 	$folder_id = $params['folderId'] ?? 0;
				// 	$include_subfolders = $this->snippets('to_bool', $params['subfolders'] ?? true);
				// 	$result = $this->folders->get_all_images_in_folder($folder_id, $include_subfolders);
				// 	break;
			}
		}
		return $result;
	}
}
