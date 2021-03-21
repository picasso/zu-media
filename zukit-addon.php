<?php
// Plugin Addon Class ---------------------------------------------------------]

class zukit_Addon {

	protected $config;
	protected $plugin;
	protected $name;
	protected $options;
	protected $options_key;

	public function register($plugin) {

		$this->plugin = $plugin ?? null;
		if(empty($this->plugin)) {
			_doing_it_wrong(__FUNCTION__, '"Addon" cannot be used without plugin!');
		} else {
			$this->config = array_merge($this->config_defaults(), $this->config());
			$this->name = $this->get('name') ?? 'zuaddon';

			$this->options_key = $this->name.'_options';
			$this->init_options();
			$this->construct_more();
		}
	}

	// Configuration management -----------------------------------------------]

	protected function config() { return []; }
	protected function config_defaults() { return []; }

	// 'construct_more' is only called after the add-on is registered by the plugin!
	protected function construct_more() {}

	public function init() {}
	public function admin_init() {}

	public function enqueue() {}
	public function admin_enqueue($hook) {}
	public function clean() {}
	public function ajax($action, $value) { return null; }

	// Options management -----------------------------------------------------]

	public function init_options() {
		$options = $this->plugin->options();
		if(!isset($options[$this->options_key]) && !is_null($this->get('options'))) {
			$this->options = $this->get('options');
			$this->plugin->set_option($this->options_key, $this->options, true);
		} else {
			$this->options($options);
		}
	}

	public function options($options = null) {
		if(!is_null($options)) $this->options = $options[$this->options_key] ?? [];
		return $this->options;
	}

	protected function get_option($key, $default = '') {
		return $this->plugin->get_option($key, $default, $this->options);
	}

	protected function is_option($key, $check_value = true) {
		return $this->plugin->is_option($key, $check_value, $this->options);
	}

	protected function del_option($key) {
		$this->options = $this->plugin->del_option($key, $this->options);
		return $this->plugin->set_option($this->options_key, $this->options, true);
	}

	protected function set_option($key, $value, $rewrite_array = false) {
		$this->options = $this->plugin->set_option($key, $value, $rewrite_array, $this->options);
		return $this->plugin->set_option($this->options_key, $this->options, true);
	}

	protected function is_plugin_option($key, $check_value = true) {
		return $this->plugin->is_option($key, $check_value);
	}

	// Redirect to plugin methods ---------------------------------------------]

	protected function sprintf_dir(...$params) {
		return call_user_func_array([$this->plugin, 'sprintf_dir'], $params);
	}
	protected function sprintf_uri(...$params) {
		return call_user_func_array([$this->plugin, 'sprintf_uri'], $params);
	}
	protected function enqueue_style($file, $params = []) {
		// $is_style, $is_frontend, $params
		$params_with_defaults = $this->plugin->enforce_defaults(true, true, $params);
		return $this->plugin->enqueue_style($this->filename($file, $params), $params_with_defaults);
	}
	protected function enqueue_script($file, $params = []) {
		$params_with_defaults = $this->plugin->enforce_defaults(false, true, $params);
		return $this->plugin->enqueue_script($this->filename($file, $params), $params_with_defaults);
	}
	protected function admin_enqueue_style($file, $params = []) {
		$params_with_defaults = $this->plugin->enforce_defaults(true, false, $params);
		return $this->plugin->admin_enqueue_style($this->filename($file, $params), $params_with_defaults);
	}
	protected function admin_enqueue_script($file, $params = []) {
		$params_with_defaults = $this->plugin->enforce_defaults(false, false, $params);
		return $this->plugin->admin_enqueue_script($this->filename($file, $params), $params_with_defaults);
	}
	protected function ends_with_slug($hook, $slug = null) {
		return $this->plugin->ends_with_slug($hook, $slug);
	}
	protected function ajax_error($error, $params = null) {
		return $this->plugin->ajax_error($error, $params);
	}
	protected function check_error($error, $ajax = false, &$report = null) {
		return $this->plugin->check_error($error, $ajax, $report);
	}
	protected function ajax_nonce($create = false) {
		return $this->plugin->ajax_nonce($create);
	}
	protected function ajax_send($result) {
		return $this->plugin->ajax_send($result);
	}
	protected function create_notice($status, $message, $actions = []) {
		return $this->plugin->create_notice($status, $message, $actions);
	}
	protected function log_error($error, $context = null) {
		$this->plugin->log_error($error, $context, 1);
	}

	// Common interface to plugin methods with availability check -------------]
	// NOTE: only public functions can be called with this helper

	protected function call($func, ...$params) {
		if(method_exists($this->plugin, $func)) return call_user_func_array([$this->plugin, $func], $params);
		else return null;
	}

	protected function snippets(...$params) {
		return call_user_func_array([$this->plugin, 'snippets'], $params);
	}

	// Helpers ----------------------------------------------------------------]

	protected function prefix_it($str, $divider = '-') {
		// if $str starts with '!' then do not prefix it (could be an absolute path)
		if(substr($str, 0, 1) === '!') return $str;
		return $this->plugin->prefix_it($str, $divider);
	}

	protected function get($key, $from_plugin = false, $default_value = null) {
		return $this->plugin->get($key, $default_value, $from_plugin ? null : $this->config);
	}

	private function filename($file, $params) {
		$with_prefix = $params['add_prefix'] ?? true;
		return $with_prefix ? $this->prefix_it($file) : $file;
	}
}
