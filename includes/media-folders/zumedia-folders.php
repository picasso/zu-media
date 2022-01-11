<?php
// Adds support for folders to the Media Library
// Author: Dmitry Rudakov
// Created: 17/07/2020

include_once('trait-folders-helpers.php');
include_once('trait-folders-ajax.php');
include_once('trait-folders-api.php');

class zu_MediaFolder extends zukit_Addon {

	private $folders_category= 'zumedia-folders';
	private $custom_key = 'zumedia_folders';

	// Taxonomy & Terms helpers
	use zu_MediaFolderHelpers;
	// Ajax actions
	use zu_MediaFolderAjax;
	// Getters, Cache, API functions
	use zu_MediaFolderAPI;

	protected function config() {
		return [
			'name'				=> $this->custom_key,
			'options'			=> [
				'add_rewrite'		=> true,
				'rewrite'			=> 'folder',
				'selectedId'		=> 0,
				'non_empty'			=> false,
				'hide_root'			=> false,
				'root_icon'			=> true,
				'inherit_privacy'	=> false,
				'anim_speed'		=> 200,
				'anim_easing'		=> 'swing',
				'color'				=> 'wp',
				'colored_tree'		=> true,
				'icons'				=> [
					'lock'			=> 'dashicons-lock',
					'unlock'		=> 'dashicons-unlock',
	            	'badge'			=> 'dashicons-admin-generic',
					'color'			=> 'dashicons-admin-appearance',
	                'edit'			=> 'dashicons-edit-large',
	            	'delete'		=> 'dashicons-trash',

	                'back'			=> 'dashicons-editor-break',
	                'breadcrumb'	=> 'dashicons-arrow-right-alt2',
	                'home' 			=> 'dashicons-admin-home',
	                'folder' 		=> 'dashicons-images-alt',
					'svg'			=> 'zu',
	            ],
			],
		];
	}

	protected function construct_more() {
		add_action('wp_ajax_folders_action', [$this, 'ajax_action']);
		add_action('pre_get_posts', [$this, 'pre_get_attachments_with_folders'], 0, 1);
		add_filter('wp_generate_attachment_metadata', [$this, 'after_upload'], 10, 3);

		add_action('pre_get_posts', [$this, 'tableview_select_folder']);
        add_action('restrict_manage_posts', [$this, 'tableview_category_filter']);
		// zu_log('plugins_loaded added');
		// add_action('plugins_loaded', [$this, 'update_cached']);
    }

	public function init() {
		$this->register_taxonomy();
		$this->update_cached();
		$this->add_folder_rewrite();
	}

	public function admin_init() {
		if(!session_id()) session_start();
		$this->set_selected_id();
	}

	public function admin_enqueue($hook) {
		// 'widgets.php' is $hook on customize.php... who knows why?
		if(in_array($hook, ['upload.php', 'post.php', 'post-new.php', 'customize.php', 'widgets.php'])) {

			$data = $this->collect_script_data();
			// cannot put the script in the footer otherwise the filter for the categories
			// will "bind" too late to Media Library -> therefore 'bottom' => false
			$this->admin_enqueue_script('folders', [
				'data'		=> $data,
				'bottom'	=> false,
				'deps'		=> [
					'plupload',
					'lodash',
					'jquery-ui-draggable',
					'jquery-ui-droppable',
					'jquery-ui-dialog',
				],
			]);
			// prefix will be added to script name automatically
			$this->admin_enqueue_style('folders');
		}
	}

	private function collect_script_data() {
        global $pagenow;

		$terms = $this->generate_sorted_tree();

		$attachment_terms = [
			[ 'id' => 0, 'label' => __('No Categories', 'zu-media'), 'slug' => '', 'parent_id' => 0],
		];

		foreach($terms as $term) {
	        $attachment_terms[] = [
				'id' 			=> $term->term_id,
				'label' 		=> $term->name,
				'parent_id' 	=> $term->category_parent,
				'depth' 		=> $term->depth,
				'meta'			=> $this->get_folder_meta($term->term_id),
			];
		}

		// this var should be available in JS
		$foders_data = [
			'jsdata_name'		=> $this->custom_key,

			'ajaxurl'           => admin_url('admin-ajax.php'),
			'ajax_nonce'     	=> $this->ajax_nonce(true),
			'query_marker'		=> $this->query_marker,
			'categories' 		=> $attachment_terms,
			'page' 				=> $pagenow === 'upload.php' ? 'library' : 'post',
			'viewmode'			=> $this->is_tableview() ? 'table' : 'grid',

			'options'			=> $this->options(),

			'lang' 				=> [
				'rootSelect'		=> __('No Categories', 'zu-media'),
				'rootTree'			=> __('Media Library', 'zu-media'),
				'backButton'		=> __('Back', 'zu-media'),
				'create'			=> __('Create', 'zu-media'),
				'createFolder'		=> __('Create Folder', 'zu-media'),
				'createPrompt'		=> __('New Folder', 'zu-media'),
				'createAlert'		=> __('Please give a name to the folder you are creating', 'zu-media'),
				'rename'			=> __('Rename', 'zu-media'),
				'renameFolder'		=> __('Rename Folder', 'zu-media'),
				'renameAlert'		=> __('Please give a new name to this folder', 'zu-media'),
				'delete'			=> __('Delete', 'zu-media'),
				'deleteFolder'		=> __('Delete folder?', 'zu-media'),
				'deleteAlert'		=> __('This will delete the folder "%s".', 'zu-media'),
			],
		];

		return $foders_data;
    }

	// Service ajax actions ---------------------------------------------------]

	public function ajax($action, $value) {
		if($action === 'zumedia_convert_taxonomy') return $this->convert_taxonomy(true);
		elseif($action === 'zumedia_fix_orphaned') return $this->fix_orphaned();
		elseif($action === 'zumedia_check_terms') return $this->check_existed_terms();
		else return null;
	}

	// Query modifications (folders filter) -----------------------------------]

	private function get_request_term_id() {
		if($this->is_tableview()) return (int)($_REQUEST[$this->custom_key] ?? $this->get_selected_id());
		return (int)($_REQUEST['query']['term_id'] ?? 0);
	}

	private function query_for_terms($term_id = 0) {

		$notInIds = [];
		if($term_id === 0) {
			$terms = get_terms(['taxonomy' => $this->folders_category, 'hide_empty' => false, 'hierarchical' => false]);
			foreach($terms as $term) {
				// if(!empty($term->term_id))
				$notInIds[] = $term->term_id;
			}
		}
		$tax_query = [
				'taxonomy' 			=> $this->folders_category,
				'field' 			=> 'term_id',
				'terms' 			=> $term_id === 0 ? $notInIds : $term_id,
				'include_children' 	=> false,
		];

		if($term_id === 0) $tax_query['operator'] = 'NOT IN';

		return $tax_query;
	}

	// Selecting attachments by folders category
	// $query is passed by reference, any changes are made directly
	// to the original object $query – no return value is necessary
	public function pre_get_attachments_with_folders($query) {

		if(!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] !== 'attachment') return;

		$taxonomies = apply_filters('attachment-category', get_object_taxonomies('attachment', 'objects'));
		if(!$taxonomies) return;

		// Наша категория не была создана/зарегистрирована?
		if(!isset($taxonomies[$this->folders_category])) return;

		// Если (нет маркера что это наш query 'OR' нет значения для term_id) 'AND' не 'is_tableview'
		// -> то ничего не делаем
		if((!isset($_REQUEST['query'][$this->query_marker]) || !isset($_REQUEST['query']['term_id']))
				&& !$this->is_tableview()
		) return;

		$term_id = $this->get_request_term_id();
		$query->set('tax_query', [ $this->query_for_terms($term_id) ]);
	}

	// Will be called after upload and here we can assign the current folder
	// to all the new attachments
	public function after_upload($metadata, $attachment_id, $context) {
		if($context === 'create') {
	        $parent_id = $this->get_selected_id();

	        if($parent_id) {
				$result = wp_set_object_terms($attachment_id, $parent_id, $this->folders_category, true);
				if(is_wp_error($result)) $this->logc('Something is wrong after uploading the attachment!', $result);
	        }
		}
        return $metadata;
    }

	// Support for the category filter in the table ---------------------------]

	private function is_tableview() {
		global $wp_list_table;
		// for table view 'WP_Media_List_Table' class will be used
		return !empty($wp_list_table) && get_class($wp_list_table) === 'WP_Media_List_Table';
	}

	public function tableview_select_folder($query) {

        if($this->is_tableview()) {
			$term_id = $this->get_request_term_id();
			$this->set_selected_id($term_id);
        }
    }

	// Dropdown filter for folders in tableview
	public  function tableview_category_filter() {
        if($this->is_tableview()) {
            $dropdown_options = [
				'show_option_none' 		=> __('No Categories', 'zu-media'),
				'option_none_value' 	=> 0,
				'hide_empty' 			=> false,
				'hierarchical' 			=> true,
				'orderby' 				=> 'name',
				'taxonomy' 				=> $this->folders_category,
				'class' 				=> 'mfs-categories',
				'name' 					=> $this->custom_key,
				'selected'				=> $this->get_request_term_id(),
			];

            wp_dropdown_categories($dropdown_options);
        }
    }
}
