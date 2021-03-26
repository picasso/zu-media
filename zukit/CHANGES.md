#### 1.1.6 / 2021-03-26
* added inline style for submenu separators
* added `fillMissing` prop for `SelectItemControl` component
* tested for compatibility with WP 5.7

#### 1.1.5 / 2021-03-24
* added `compareVersions` function to `utils`
* passed a WP version to the script and basic containers
* improved JS and CSS for WP with version earlier than 5.5
* fixed bug `prefix already exists` in `zukit.sh`
* other small fixes

#### 1.1.4 / 2021-03-21
* added `ModalMessage` and `RawHTML` components for the Settings Page export
* added default size and `bottomHalf` option for `ZukitDivider` component
* added `ends_with_slug` helper for add-ons
* added `Settings Page` media breakpoints
* added media breakpoints for `sidebar` and some controls
* added `extend_actions` method
* added `style` support for `iconcell`
* added `re-init` flow for subtree shell script
* refactoring `refresh_scripts` support
* improved CSS for `interface-interface-skeleton` class
* fixed width calculation for `SelectItemControl`
* fixed bug with nested `depends` for `toggleOption` function
* fixed bug with `actions` data
* fixed bytes abbreviation on `B` instead of `b` in `format_bytes` function
* other small improvements

#### 1.1.3 / 2021-02-10
* changed configuration structure
* added access to nested configuration sections
* refactoring to use `config` instead of `js_params` and `css_params`
* refactoring to use plugin configuration for `zukit_Blocks` class
* added `handle_only` option for enqueue methods
* removed `is_config` method
* fixed bug when nested key is equal parent key in options/config
* fixed bug with recursive merging of config
* fixed `get_filepath` method
* added `called_class` for log methods when called non static
* moved most of the documentation from README to __wiki__
* other small improvements

#### 1.1.2 / 2021-01-08
* fixed bug with `lookbehind` regex (Safari does not support it)
* fixed bug with `shape` type in `LoaderControl` component
* improved CSS to be compatible with WP 5.6

#### 1.1.0 / 2021-01-06
* added `withDebounce` and `withoutValues` params for `AdvTextControl` component
* renaming `ListInput` and `SelectItem` components
* implemented `ModalMessage` component
* implemented alternative `RawHTML` component
* implemented `Loader` and `LoaderControl` components
* added `jquery-helpers` and `data-store`
* added `render` set for `zukit-blocks`

* added support for Custom Blocks addon
* implemented many helpers for Custom Blocks
* implemented `force_frontend_enqueue` method to load plugin/themes scripts with Custom Blocks
* added `Zukit Blocks` injection for Gutenberg pages
* added colors support for `Zukit Blocks`

* improvements to support data stores and REST API extending
* added support for custom stores
* added `api_basics` for `js_blocks_data`
* refactoring `get_zudata` method, added support for `extend_zudata`
* created Core Data store and Custom Hooks for Core Data
* all components changed based on REST API changes

* improved debug logging
* added `Debug` module to __Zukit__ Blocks
* added `log_error` method
* added `context` to log method
* added `_zlg` function for one variable logging
* added error logging for SVG loading
* changed `zukit` path detection

* using `get` method instead of direct config access
* all frontend scripts now use `suffix` param from config
* added `is_config` method, now is possible to cancel prefixing script name with `add_prefix` param
* method `enqueue only` now accept array as `$handle` param
* added method for marking active theme with body class
* changed filter name to `zukit_no_excerpt_blocks`

* removed `isLarge` param from all buttons
* added CSS for `zukit-modal` class
* improved body class snippets
* updated `loaders` and refactored with `group`
* other small improvements

#### 1.0.0 / 2020-12-06
* adapted to changes in WP 5.5
* refactoring use of plugin data
* added `falsely` type for `getPanel`
* implemented support for `depends` key in __Panels__
* improved compatibility check
* improved translations loading
* added basic debug log methods
* bug fixing

#### 0.9.9 / 2020-12-01
* refactoring compatibility check (for PHP and WordPress)
* improved `export-ignore` generation for archive
* updated `README` file

#### 0.9.8 / 2020-11-29
* bug fixing and improvements

#### 0.9.7 / 2020-11-28
* added translations
* fixed `Text Domain` for some translation calls
* implemented loading of translations for both `.MO` and `.JSON` forms
* created scripts for `JSON` translations
* added `testComponentWithUpdate` method
* implemented custom appearance for `Settings Page`
* added `loading` state to Debug Actions
* improved `ajax_error` method
* improved `README` file

#### 0.9.6 / 2020-11-24
* added `editMode` for `ListInput` component
* created new `AdvTextControl` component
* all debug methods moved to a separate trait
* added *debug options* to `Debug Panel` in sidebar
* added `array_prefix`, `to_bool`, `to_float`, `cast` and `shortcode_atts_with_cast` to __snippets__
* modified `insert_svg_from_file` and `array_prefix_keys` methods in __snippets__
* added `del_option` method
* implemented `del_option` via REST API
* added `path_autocreated` switch for `set_option` method
* improved __options__ hooks for cases when `key` contains `path`
* improved `log_error` method
* added `async` and `defer` options for script params
* css improvements and cleaning

#### 0.9.5 / 2020-11-09
* created a new component `ListInput`
* added `loaders` to __snippets__
* refactoring `js_data` methods
* renaming `renderPlugin` to `renderPage`
* implemented work with __options__ and __panels__ via useReducer to decrease re-renders
* refactoring `skeleton` and `sidebar` to work with new hooks
* fixed bug with `ZukitPanel` component created via inline function (*constantly unmounting*)
* fixed bugs in menu reordering
* improved output of `log_error` method
* improved css
* several sections are added to README

#### 0.9.4 / 2020-10-30
* fixed bug in `is_zukit_slug` method
* added `router` support for REST API methods
* added `router` support in `fetch.js`
* improved error processing in *fetch* functions
* changed the methods for creating additional info and actions for the sidebar
* css improvements

#### 0.9.3 / 2020-10-28
* method `enqueue_only` now enqueue both script and style when called without params
* implemented logic when `null` is replaced with default value in `js_params` and `css_params`
* added __bash script__ to install the framework
* store all Zukit slugs in *static* property to check with `is_zukit_slug` method
* store `zukit_Singleton` class location in a static property to use in `get_zukit_filepath`
* added `get_filepath_and_src` method
* moved enqueue of Zukit files in `zukit_enqueue` method
* small improvements

#### 0.9.2 / 2020-10-26
* refactored scripts methods to to separate `register` and `enqueue` phases
* added `enqueue_only` and `register_only` methods
* fixed bug in `zu_printf`
* added check to load Zukit scripts only once

#### 0.9.1 / 2020-10-23
* implemented `zu_sprintf` - special version of the `sprintf` function
* fixed bug with `wp_enqueue_scripts` action name
* added `merge_js_data` method
* added README in *mixed* language

#### 0.9.0 / 2020-10-22
* renaming __snippets__ index file
* small improvements

#### 0.8.0 / 2020-10-06
* initial commit (split from Zu Media)
