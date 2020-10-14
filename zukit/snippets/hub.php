<?php
include_once('traits/slugs.php');
include_once('traits/thumbnails.php');
include_once('traits/lang.php');
include_once('traits/inline.php');
include_once('traits/minify.php');
include_once('traits/date.php');
include_once('traits/content.php');
include_once('traits/useful.php');

class zukit_Snippets extends zukit_Singleton {

	use zusnippets_Slugs,
		zusnippets_Thumbnails,
		zusnippets_Lang,
		zusnippets_InlineStyle,
		zusnippets_Minify,
		zusnippets_Date,
		zusnippets_Content,
		zusnippets_Useful;

	function construct_more() {
		$this->init_advanced_style();
	}

	// Classes manipulations --------------------------------------------------]

	public function split_classes($classes, $as_is = false) {

		$classes = is_array($classes) ? $classes : preg_split('/[\s,]+/', $classes);
		$classes = array_map('trim', $classes);

		return $as_is ? $classes : array_unique(array_filter($classes));
	}

	public function merge_classes($classes, $implode = true) {
		$classes = $this->split_classes($classes, $implode ? false : true);
		return $implode ? implode(' ', $classes) : $classes;
	}

	public function remove_classes($classes, $remove = [], $implode = true) {

		$classes = $this->split_classes($classes);
		foreach($remove as $test) if(in_array($test, $classes)) unset($classes[array_search($test, $classes)]);

		return $implode ? $this->merge_classes($classes) : $classes;
	}

	public function add_body_class($my_classes, $prefix = '') {
		add_filter('body_class', function($classes) use ($my_classes, $prefix) {

			$my_classes = $this->split_classes($my_classes);
			// add prefix to all classes
			if(!empty($prefix)) $my_classes = preg_filter('/^/', $prefix, $my_classes);
			// remove all already existing classes
			$my_classes = $this->remove_classes($my_classes, $classes, false);

			$classes[] = $this->merge_classes($my_classes);
			return $classes;
		});
	}

	public function add_admin_body_class($my_classes) {
		add_filter('admin_body_class', function($classes) use ($my_classes) {
			$classes = $this->split_classes($classes);
			$my_classes = $this->remove_classes($my_classes, $classes, false);
		    return $this->merge_classes(array_merge($classes, $my_classes));
		});
	}
}

// Common Interface to helpers ------------------------------------------------]

if(!function_exists('zu_snippets')) {
	function zu_snippets() {
		return zukit_Snippets::instance();
	}
}
