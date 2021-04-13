# Zu Media: Media Library with folders, dominant colors and more.

<!-- [![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/zu-media?style=for-the-badge)](https://wordpress.org/plugins/zu-media/)
[![WordPress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/zu-media?color=4ab866&style=for-the-badge)](https://wordpress.org/plugins/zu-media/)
[![WordPress Plugin Required PHP Version](https://img.shields.io/wordpress/plugin/required-php/zu-media?color=bc2a8d&style=for-the-badge)](https://www.php.net/) -->

[![WordPress Plugin Version](https://img.shields.io/badge/plugin-v2.2.2-007ec6.svg?style=for-the-badge)]()
[![WordPress Plugin: Tested WP Version](https://img.shields.io/badge/wordpress-v5.7.0%20tested-4ab866.svg?style=for-the-badge)]()
[![WordPress Plugin Required PHP Version](https://img.shields.io/badge/php->=7.0.0-bc2a8d.svg?style=for-the-badge)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-fcbf00.svg?style=for-the-badge)](https://github.com/picasso/zu-media/blob/master/LICENSE)

 <!-- ![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/zu-media?color=00aced&style=for-the-badge) -->

Enhances WordPress Media Library with some features (folders, dominant color, location category and others).


![Zu Media - Media Library with folders, dominant colors and more.](https://user-images.githubusercontent.com/399395/111074321-bfc89100-84e2-11eb-8a40-09671bff8da4.png)


## Description

This plugin includes several enhancements to the WordPress Media Library that can be used within specialized themes or separately. The plugin allows users to quickly organize all their media into folders. You can easily drag and drop images into folders and change the tree view of the folders as you wish. The plugin also allows you to calculate the dominant color for all images in your Media Library (which can then be used in different blocks or shortcodes), add different categories and tags to images, create a set of media sizes to display Responsive Images, and much more.

> &#x1F383; In addition to the main functionality, the plugin implements many small functions for working with the Media Library, which I found it possible to include in the plugin for ease of developing WordPress themes.

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

<!--
### Connect

-   [Download on WordPress.org](https://wordpress.org/plugins/zu-media/)
-   [Follow on Twitter](https://twitter.com/??)
-   [Like me on Facebook](https://www.facebook.com/??/)
-->

## Download

<!-- + [Zu Media on WordPress.org](https://downloads.wordpress.org/plugin/zu-media.zip) -->
+ [Zu Media on GitHub](https://github.com/picasso/zu-media/archive/master.zip)

## Installation

1. Upload the `zu-media` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin using the `Plugins` menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in `Settings > Zu Media`.
4. You can import folders form other plugins or create new folders in the Media Library and then easily organize your files with drag and drop.


## Public API methods

Basically, using the API to access plugin methods is not meant for casual users. This is necessary for developers of themes or plugins. While this plugin is used only by me, then all these descriptions are just *memos* for myself.

+ __get_dominant_by_id(`$post_or_attachment_id = null`)__
+ __update_dominant_by_id(`$post_or_attachment_id = null`)__
- __get_ratio(`$post_or_attachment_id = null`)__
- __is_landscape(`$post_or_attachment_id = null`, `$limit = '3:2'`)__
* __get_folders()__
* __get_folder_by_id(`$folder_id`)__
* __get_folder_by_attachment_id(`$attachment_id`)__
* __is_private_folder(`$folder_id`)__
+ __get_galleries(`$post_id = null`)__
+ __get_gallery_by_attachment_id(`$attachment_id`)__
- __attachment_id_from_class_or_url(`$image`)__
- __media_size_full_key()__


Since `Zu Media` plugin is based on the singleton concept, you can access all of its public methods using the special function `zumedia`, which returns a static `instance` of the plugin class.

```php

// if this is the attachment ID then the dominant color for the image will be returned
$image_dominant = zumedia()->get_dominant_by_id(67);
// if this is a post ID then the dominant color for featured image of the post will be returned
$post_dominant = zumedia()->get_dominant_by_id(283);
// if the ID is not specified, then the dominant color for the featured image of the current post will be returned
$current_post_dominant = zumedia()->get_dominant_by_id();
// re-calculate the dominant color for the featured image of the current post
zumedia()->update_dominant_by_id();

// if this is the attachment ID then the ratio for the image will be returned
$image_ratio = zumedia()->get_ratio(67);
// if this is a post ID then the ratio for featured image of the post will be returned
$post_ratio = zumedia()->get_dominant_by_id(283);

// if the image with the attachment ID has a 'landscaped' ratio
$is_landscape = zumedia()->is_landscape(67);
// if the featured image of the post with the ID has a 'landscaped' ratio
// the ratio '16:9 'will be used to distinguish between 'landscape' and 'portrait' images
$is_landscape = zumedia()->is_landscape(283, '16:9');

// get all folders data
$folders = zumedia()->get_folders();
// get folder data for the ID
$folder = zumedia()->get_folder_by_id(4);
// check if folder with the ID is 'private'
$is_private = zumedia()->is_private_folder(4);

// get all galleries data
$galleries = zumedia()->get_galleries();
// get all galleries for a post with the ID
$galleries = zumedia()->get_galleries(283);
// get gallery for the attachment ID (empty array will be returned if no gallery found)
$gallery = zumedia()->get_gallery_by_attachment_id(67);

// get the attachment ID from image tag (first will be checked class and then image url)
// when tag has 'wp-image-*' class
// $tag = '<img src="http://mysite.com/wp-content/uploads/2020/07/next-1024x606.jpg" alt="" class="wp-image-779"/>';
// when tag does not have 'wp-image-*' class then url will be used to find out the attachment ID
// $tag = '<img src="http://mysite.com/wp-content/uploads/2020/05/testimage-400x240.jpg"/>';
$attachment_id = zumedia()->attachment_id_from_class_or_url($tag);

```

<!--
## Support

Need help? This is a developer's portal for __Zu Media__ and should not be used for general support and queries. Please visit the [support forum on WordPress.org](https://wordpress.org/support/plugin/zu-media) for assistance.
-->

## Screenshots

## [![Plugin Settings Page](https://user-images.githubusercontent.com/399395/111200815-6b94de00-85c2-11eb-8b79-236beace105e.jpg)](https://github.com/picasso/zu-media/)

## [![Media Folders Settings Section](https://user-images.githubusercontent.com/399395/111200828-72235580-85c2-11eb-9093-79ba0e124349.jpg)](https://github.com/picasso/zu-media/)

## [![Media Library with Folders](https://user-images.githubusercontent.com/399395/111200861-78b1cd00-85c2-11eb-9593-1b39eb994b4a.jpg)](https://github.com/picasso/zu-media/)

## [![Bulk drag & drop](https://user-images.githubusercontent.com/399395/111200881-7e0f1780-85c2-11eb-810f-d52ac897fc0c.jpg)](https://github.com/picasso/zu-media/)

## [![Drag & drop over folders tree](https://user-images.githubusercontent.com/399395/111200913-85362580-85c2-11eb-9eb6-e8dd23656be2.jpg)](https://github.com/picasso/zu-media/)

## [![Folders Toolbar](https://user-images.githubusercontent.com/399395/111200933-8a937000-85c2-11eb-882b-b613539fe585.jpg)](https://github.com/picasso/zu-media/)
