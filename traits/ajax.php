<?php

// Plugin Ajax/REST API Trait -------------------------------------------------]
//
// const READABLE = 'GET'
// const CREATABLE = 'POST'
// const EDITABLE = 'POST, PUT, PATCH'
// const DELETABLE = 'DELETE'

trait zukit_Ajax {

	private $zukit_api_prefix = 'zukit';
	private $zukit_api_version = 1;
	private $zukit_routes;

	private $api_prefix;
	private $api_version;
	private $routes;

	private $nonce;
	private $ajax_error;

	private static $zukit_rest_registered = false;

	protected function api_routes() {}

	private function ajax_config() {

		$this->nonce = $this->get('nonce') ?? $this->prefix.'_ajax_nonce';
		$this->api_prefix = $this->get('api_prefix') ?? $this->prefix;
		$this->api_version = $this->get('api_version') ?? 1;

		$this->zukit_routes = [
			// make action via ajax call
			'action'		=> [
				'methods' 		=> WP_REST_Server::CREATABLE,
				'callback'		=> 'do_ajax',
				'args'			=> [
					'router'		=> [
						'required'			=> true,
						'sanitize_callback' => 'sanitize_key',
					],
					'key'		=> [
						'default'			=> false,
						'sanitize_callback' => 'sanitize_key',
					],
				],
				'permission'	=> 'edit_posts',
			],
			// get option for requested 'key'
			'option'		=> [
				'methods' 		=> WP_REST_Server::READABLE,
				'callback'		=> 'get_option_ajax',
				'args'			=> [
					'router'		=> [
						'required'			=> true,
						'sanitize_callback' => 'sanitize_key',
					],
					'key'		=> [
						'default'			=> false,
						'sanitize_callback' => 'sanitize_key',
					],
				],
				'permission'	=> 'edit_posts',
			],
			// set options for requested 'keys'
			// if value for 'key' is 'null' then this option will be deleted
			'options'		=> [
				'methods' 		=> WP_REST_Server::CREATABLE,
				'callback'		=> 'set_options_ajax',
				'args'			=> [
					'router'		=> [
						'required'			=> true,
						'sanitize_callback' => 'sanitize_key',
					],
					'keys'		=> [
						'default'			=> [],
						'sanitize_callback' => [$this, 'sanitize_paths'],
					],
					'values'	=> [
						'default'			=> [],
						// 'sanitize_callback' => 'sanitize_key',
					],
				],
				'permission'	=> 'edit_posts',
			],
			// get some data by key
			'zudata'		=> [
				'methods' 		=> WP_REST_Server::READABLE,
				'callback'		=> 'get_zudata',
				'args'			=> [
					'key'		=> [
						'default'			=> false,
						'sanitize_callback' => 'sanitize_key',
					],
				],
				'permission'	=> 'edit_posts',
			],
		];

		$this->routes = $this->api_routes() ?? [];

		add_action('rest_api_init' , [$this, 'init_zukit_api']);
		add_action('rest_api_init' , [$this, 'init_api']);
	}

	public function init_zukit_api() {
		// prevent 'register_rest_route' for Zukit be called many times from different plugins
		if(self::$zukit_rest_registered) return;
		$this->init_routes($this->zukit_routes, $this->zukit_api_prefix, $this->zukit_api_version);
		self::$zukit_rest_registered = true;
	}

	public function init_api() {
		$this->init_routes($this->routes, $this->api_prefix, $this->api_version);
	}

	private function init_routes($routes, $api_prefix, $api_version) {

		$namespace = sprintf('%1$s/v%2$s', $api_prefix, $api_version);
		foreach($routes as $route => $params) {

			$endpoint = sprintf('/%1$s', $route);

			register_rest_route($namespace, $endpoint, [
  		    	'methods'				=> $params['methods'],
  		    	'callback'				=> [$this, $params['callback']],
				'args'					=> $params['args'],
				'permission_callback' 	=> function() use($params) {
                    return empty($params['permission']) ? true : current_user_can($params['permission']);
                },
			]);
		}
	}

	// Sanitize helpers -------------------------------------------------------]

	public function sanitize_ids($ids) {
		$cleaned_ids = [];

		if(is_numeric($ids)) return [$ids];

		if(is_array($ids)) {
			foreach($ids as $id) {
				$cleaned_ids[] = absint($id);
			}
		}
		return $cleaned_ids;
	}

	public function sanitize_keys($keys) {

		if(is_string($keys)) return [sanitize_key($keys)];

		$cleaned_keys = [];
		if(is_array($keys)) {
			foreach($keys as $key) {
				$cleaned_keys[] = sanitize_key($key);
			}
		}
		return $cleaned_keys;
	}

	// Lowercase alphanumeric characters, dot and underscores are allowed.
	public function sanitize_path($path) {
	    $path = strtolower($path);
	    $path = preg_replace('/[^a-z0-9_.]/', '', $path);
    	return $path;
	}

	public function sanitize_paths($paths) {

		if(is_string($paths)) return [$this->sanitize_path($paths)];

		$cleaned_paths = [];
		if(is_array($paths)) {
			foreach($paths as $path) {
				$cleaned_paths[] = $this->sanitize_path($path);
			}
		}
		return $cleaned_paths;
	}

	public function floatval($value) {
		return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}

	// Basics -----------------------------------------------------------------]

	public function timestamp() {
		return intval(current_time('timestamp'));
	}

	// if '$as_bold' is string then this string will be emphasized in the message with the <strong> tag
	public function ajax_error($error, $params = null, $as_bold = null) {
		if(is_null($error)) $this->ajax_error = false;
		else {
			$message = is_string($error) ? $error : $error->get_error_message();
			if(!empty($as_bold)) {
				$message = preg_replace('/('.$as_bold.')/m', '<strong>$1</strong>', $message);
			}
			$this->ajax_error = [
				'status' 	=> false,
				'message'	=> $message,
				'params'	=> $params,
			];
		}
		return false;
	}

	public function ajax_nonce($create = false) {
		return $create ? wp_create_nonce($this->nonce) : $this->nonce;
	}

	// Generic Ajax Actions ---------------------------------------------------]

	public function ajax_send($result) {
		if($this->ajax_error !== false) {
			$this->ajax_error['more'] = $result ?? null;
			wp_send_json_success($this->ajax_error);
		} else {
			wp_send_json_success($result);
		}
	}

	public function ajax_reset_options() {
		$options = $this->reset_options();
		return $this->create_notice('infodata', // combine 'info' with 'data'
			sprintf( 'Plugin <strong>"%1$s"</strong> settings are reset to defaults', $this->data['Name']),
			$options
		);
	}

	public function ajax_more($action, $value) { return null; }

	// API Requests -----------------------------------------------------------]

	public function do_ajax($request) {

		$params =  $request->get_params();

		$key = $params['key'] ?? '';
		$value = $params['value'] ?? null;
		$result = null;

		// instead of $this, should use $router, because it defines the active plugin
		$router = $this->get_router($params);

		// collect data for REST API
		if(!is_null($router)) {
			switch($key) {
				case 'zukit_more_info':
					$result = $router->create_notice('data', null, $router->info());
					break;

				case 'reset_options':
					$result = $router->ajax_reset_options();
					break;

				// default debug actions
				case 'clear_log':
					$result = $router->debug_empty_log();
					break;

				case 'test_ajax':
					$result = $router->debug_ajax_test();
					break;

				default:
					$result = $router->ajax_more($key, $value);
					if($result === null) $result = $router->ajax_addons($key, $value);
			}
		}

		// if $result is empty - something went wrong - then return empty object
		if(empty($result)) return rest_ensure_response($this->ajax_error !== false ? $this->ajax_error : (object) null);

		return rest_ensure_response($result);
	}

	public function get_option_ajax($request) {

		$params =  $request->get_params();
		// instead of $this, should use $router, because it defines the active plugin
		$router = $this->get_router($params);

		$key = $params['key'];
		$value = is_null($router) ? null : $router->get_option($key, null);

		// if $result is null - something went wrong - then return null
		return rest_ensure_response($value === null ? (object) null : $value);
	}

	public function set_options_ajax($request) {

		$params =  $request->get_params();

		$keys = $params['keys'];
		$values = $params['values'];
		if(!is_array($values)) $values = array_fill_keys($keys, $values);

		// instead of $this, should use $router, because it defines the active plugin
		$router = $this->get_router($params);
		$result = is_null($router) ? false : true;

		if($result) {
			foreach($keys as $key) {
				// if value for 'key' is 'null' then call 'del_option' instead of 'set_option'
				if(array_key_exists($key, $values) && $values[$key] === null) $return = $router->del_option($key);
				// with set_option 'null' will be ignored, 'false' considered as failure
				else $return = $router->set_option($key, $values[$key] ?? null);

				if($return === false) $result = false;
			}
		}
		// if $result is false - something went wrong - then return null
		return rest_ensure_response($result ?? (object) null);
	}

	protected function extend_zudata($key) {}

	public function get_zudata($request) {

		$params =  $request->get_params();

		$key = $params['key'];
		$result = null;

		$zumedia_found = function_exists('zumedia');

		// collect data for REST API
		switch($key) {
			case 'folders':
			case 'albums':
				if($zumedia_found) {
					// get_folders
					$parent_id = $params['parentId'] ?? 0;
					$result = zu_mplus()->get_albums($parent_id);
				}
				break;

			case 'folder_by_image':
			case 'album_by_image':
				if($zumedia_found) {
					// get_folder_by_image_idss
					$image_id = $params['imageId'] ?? 0;
					$result = zu_mplus()->get_album_by_image_id($image_id);
				}
				break;

			case 'gallery_by_image':
				if($zumedia_found) {
					// get_gallery_by_image_id
					$image_id = $params['imageId'] ?? 0;
					$result = zu_mplus()->get_gallery_by_image_id($image_id);
				}
				break;

			case 'loaders':
				$duration = $params['duration'] ?? 0.8;
				$opacity = $params['opacity'] ?? 0.2;
				$result = $this->snippets('loader', -1, $duration, null, $opacity);
				break;

			// process 'zudata' from all loaded plugins/theme
			default:
				foreach($this->instance_by_slug() as $router) {
					$result = $router->extend_zudata($key) ?? null;
					if(!empty($result)) break;
				}
		}

		// if $result is empty - something went wrong - then return empty object
		return rest_ensure_response(empty($result) ? (object) null :  $result);
	}

	// Ajax Actions Helpers ---------------------------------------------------]

	private function reset_ajax_error() {
		$this->ajax_error = false;
	}

	// $rest_router serves to identify the plugin that currently uses the REST API,
	// since all plugins inherit the same Zukit_plugin class and identification
	// is required to determine which of the active plugins should respond to ajax requests
	private function get_router($params) {

		$this->reset_ajax_error();

		$router_slug = $params['router'] ?? '';
		$rest_router = $this->instance_by_slug($router_slug);

		if($rest_router instanceof zukit_Plugin) return $rest_router;
		$this->ajax_error(__('Active router not defined', 'zukit'), $params);
		return null;
	}

	public function get_ajax_error() {
		return $this->ajax_error;
	}

	public function create_notice($status, $message, $data = []) {
		return [
			'status'	=> $status,
			'content'	=> $message ?? 'Unknown',
			'data'		=> $data,
		];
	}
}
