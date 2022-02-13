<?php
/*
Plugin Name: Zu Media
Plugin URI: https://wordpress.org/plugins/zu-media/
GitHub Plugin URI: https://github.com/picasso/zu-media
Description: Enhances WordPress Media Library with some features (folders, dominant color, location category and others).
Version: 2.3.3
Author: Dmitry Rudakov
Author URI: https://dmitryrudakov.ru/about/
Text Domain: zumedia
Domain Path: /lang/
Requires at least: 5.3.0
Requires PHP: 7.2.0
*/

// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');

// Always load Zukit even if we don't use it later ('wp_doing_ajax' or 'wp_doing_cron')
// as other plugins or themes may want to use it
require_once('zukit/load.php');

// Exit early if a WordPress heartbeat comes
if(wp_doing_ajax() && isset($_POST['action']) && ($_POST['action'] === 'heartbeat')) return;
// Let's not load plugin during cron events
if(wp_doing_cron()) return;

// Start! ---------------------------------------------------------------------]

// compatibility check for Zukit
if(Zukit::is_compatible(__FILE__)) {

	require_once('includes/zumedia-plugin.php');
	zumedia(__FILE__);
}
