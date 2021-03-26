=== Zu Media ===
Contributors: dmitryrudakov
Tags: gutenberg, folders, dominant color, admin, media library folders, media library
Requires at least: 5.1.0
Tested up to: 5.7
Stable tag: 2.2.1
License: GPLv2 or later
Requires PHP: 7.0.0

Enhances WordPress Media Library with some features (folders, dominant color, location category and others).

== Description ==

This plugin includes several enhancements to the WordPress Media Library that can be used within specialized themes or separately. The plugin allows users to quickly organize all their media into folders. You can easily drag and drop images into folders and change the tree view of the folders as you wish. The plugin also allows you to calculate the dominant color for all images in your Media Library (which can then be used in different blocks or shortcodes), add different categories and tags to images, create a set of media sizes to display Responsive Images, and much more.

### Folders Features

* Create unlimited folders in the Media Library
* Allows to rename and delete folders
* Drag and drop folders, change order, and hierarchy
* Bulk select images and media files and drag them to any folder
* Assign colors to selected folders
* Lock folders - images in locked folders cannot be used for random selection
* Customize the look and feel of your folders
* Monitor orphaned media library files
* Import folders from other plugins (WP Media Folder)
* Responsive layout

### Other Features

* Calculates the dominant color for all images in the Media Library
* Adds a field displaying the dominant color to the image detail
* Provides an API for getting the dominant color and updating it
* Allows you to add post categories and post tags for images
* Creates a new category 'location' and lets you manage it
* Adds a new meta with the calculated image ratio and displays it in the image detail
* Registers a set of media sizes to display Responsive Images
* Provides API for getting different image properties
* Adds several new color schemes for admin
* Compatible with the latest version of WordPress

== Installation ==

1. Upload the `zu-media` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin using the `Plugins` menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in `Settings > Zu Media`.
4. You can import folders form other plugins or create new folders in the Media Library and then easily organize your files with drag and drop.

== Screenshots ==

1. Plugin Settings Page
2. Media Folders Settings Section
3. Media Library with Folders
4. Bulk drag &amp; drop
5. Drag &amp; drop over folders tree
6. Folders Toolbar

== Changelog ==

### 2.2.1 ###
* added logic for icons in WP version up to 5.5
* improved JS and CSS for WP with version less than 5.5
* changed license to GPL-2.0
* tested for compatibility with WP 5.7
* small improvements

### 2.2.0 ###
* refactoring `ratio` getter and public API methods
* added icons, screenshots and readme
* deleted all direct references to CSS prefix
* improved css for some media breakpoints
* introduced `boxed` mode
* implemented Folders Preview for the Settings Page
* added support for default folder color
* implemented `locked` folders
* added colored folders for tree
* added `colors` panel for folders
* added support for SVG folders
* replaced deprecated jQuery methods
* fixed bug with `breadcrumb`
* fixed bug with `close` button
* adapted to the latest changes in __Zukit__
* added `Ajax` trait and implemented extension of `zudata` REST route
* refactoring source folder structure
* some optimization
* small improvements

### 2.1.1 ###
* changed text domain from `zumedia` to `zu-media`
* adapted to latest changes in __Zukit__
* small improvements

### 2.1.0 ###
* implemented stable version of `Media Folders`
* implemented `convert_taxonomy` via Ajax
* implemented `fix_orphaned` utility
* refactoring `ImageSizes` with new parent class
* refactoring `AdminColors` with new parent class
* moved `Location helpers` to trait
* moved `Attachments helpers` to trait
* replaced `zu()` calls to `snippets`
* refactoring `cached` methods
* added `disable_cache` option
* implemented version of `Settings` page with Gutenberg support (with __Zukit__ framework)

### 2.0.0 ###
* starting `folders` implementation

### 1.9.7 ###
* added `reset_cached` when plugin options updated (saved)

### 1.9.6 ###
* improved `attachements ID` in media popup
* fixed work under Wordpress 5.3

### 1.9.5 ###
* added `onlight` attribute to `gallery` shortcode

### 1.9.4 ###
* added language code to `cachekey` for galleries and folders

### 1.9.3 ###
* fixed bug if `$_post_id` does not exist

### 1.9.2 ###
* added check for `post_parent` in `All Attachements` meta box
* added `Detach Image` action
* added `detach_attachment()` function

### 1.9.1 ###
* improved CDN support for generated HTML

### 1.8.9 ###
* improved `Media Actions` meta box (added spinner to all actions)
* fixed bug when searching galleries for all `pages`

### 1.8.8 ###
* fixed bug in `All Attachements` table (when Folder is `root`)

### 1.8.7 ###
* improved `All Attachements` table
* modified `is_private_album()` function to return `1` or `2` depending on album privacy

### 1.8.6 ###
* added check for `_private` in folder name inside Media Library (all images in such folders considered as `private`)
* added `is_private_album()` and `get_private_images()` functions to work with `private` images
* added `get_all_images_in_album()` function which collects all images in folder including subfolders
* gallery shortcode: `ajaxed` attribute was renamed to `lazyload`
* added `no_lazyload` attribute to `gallery` shortcode
* improved `alt` transformation in `figure` repeater

