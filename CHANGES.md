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

### 1.8.5 ###
* added `params` attribute to defaults for JS
* added `no_false` param to pass in JS attributes which should be deleted when set to false
* added `cached`, `m_disabled` and `m_firstonly` attributes to `gallery` shortcode
* added `$_caption` var to all repeaters
* if image `alt` contains `nocopy` then no copyright will be inserted in `figure`
* if image `alt` is equal to `copy:X` then X will be inserted in `figure` as copyright
* added button to reset cache in `Media Actions` meta box
* css modifications
