<?php
/*
Plugin Name: Zu Media
Plugin URI: https://github.com/picasso/zumedia
GitHub Plugin URI: https://github.com/picasso/zumedia
Description: Enhances WordPress Media Library with some  features (folders, dominant color, location category and others).
Version: 2.2.0
Author: Dmitry Rudakov
Author URI: https://dmitryrudakov.ru/about/
Text Domain: zumedia
Domain Path: /lang/
Requires at least: 5.1.0
Requires PHP: 7.0.0
*/

// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');
// Exit early if a WordPress heartbeat comes
if(wp_doing_ajax() && isset($_POST['action']) && ($_POST['action'] === 'heartbeat')) return;
// Let's not load plugin during cron events
if(wp_doing_cron()) return;

// Start! ---------------------------------------------------------------------]

require_once('zukit/load.php');

// compatibility check for Zukit
if(Zukit::is_compatible(__FILE__)) {

	require_once('includes/zumedia-plugin.php');
	zumedia(__FILE__);
}
