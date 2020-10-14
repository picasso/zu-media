<?php
/*
Plugin Name: Zu Media
Plugin URI: https://github.com/picasso/zumedia
GitHub Plugin URI: https://github.com/picasso/zumedia
Description: Enhances WordPress Media Library with some  features (folders, dominant color, location category and others).
Version: 2.1.0
Author: Dmitry Rudakov
Author URI: https://dmitryrudakov.ru/about/
Text Domain: zumedia
Domain Path: /lang/
*/

// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');

// Start! ---------------------------------------------------------------------]

add_action('plugins_loaded', function() { 	//  All 'Zukit' classes are loaded now

	// _dbug_change_log_location(__FILE__, 2);
	// Check - maybe all parent classes were loaded in other plugin?
	if(!class_exists('zukit_Plugin')) require_once('zukit/zukit-plugin.php');

	require_once('includes/zumedia-plugin.php');
	zumedia(__FILE__);
});
