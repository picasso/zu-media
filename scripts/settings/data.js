// WordPress dependencies

const { __ } = wp.i18n;

// Internal dependencies

const options = {
	folders: {
		label: 	__('Organize files in Media Library Folders?', 'zu-media'),
		help:	__('Allows to create different folders and organize content with a simple drag and drop.', 'zu-media'),
	},
	responsive: {
		label: 	__('Include support for Responsive Images?', 'zu-media'),
		help:	__('Activates filters and functions which support responsiveness. Adds needed custom image sizes.', 'zu-media'),
	},
	full_hd: {
		label: 	__('Add Full HD Image Size?', 'zu-media'),
		help:	__('Creates custom "full_hd" image size for responsiveness. Otherwise standard "full" image size will be used.', 'zu-media'),
		depends: 'responsive',
	},
	dominant: {
		label: 	__('Add Dominant Colors for images in Media Library', 'zu-media'),
		help:	__('You should updated all existing images after activation via "Update Dominants" action.', 'zu-media'),
	},
	media_ratio: {
		// 2em -> margins above and under the divider
		divider: 2,
		label: 	__('Add Media Ratio field to all media files?', 'zu-media'),
		help:	__('The media ratio will be calculated based on current sizes. Can be used in image selections.', 'zu-media'),
	},
	add_category: {
		label: 	__('Add Category for Media Library?', 'zu-media'),
		help:	__('The existing categories (used for posts and pages) will be used for images as well.', 'zu-media'),
	},
	add_tags: {
		label: 	__('Add Tags for Media Library?', 'zu-media'),
		help:	__('The existing tags (used for posts and pages) will be used for images as well.', 'zu-media'),
	},
	add_location: {
		label: 	__('Add Location for Media Library?', 'zu-media'),
		help:	__('Creates a new category which will be used only for Media Libaray.', 'zu-media'),
	},
	admin_colors: {
		divider: 2,
		label: 	__('Add new Admin Color Schemes', 'zu-media'),
		help: __('You can change it in [Your Profile](/wp-admin/profile.php)', 'zu-media'),
	},
	disable_cache: {
		label: 	__('Disable Cache?', 'zu-media'),
		help: __('Disabling caching will result in memory savings, but very small (not recommended).', 'zu-media'),
	},
	// не реализовано еще
	svg: {
		label: 	__('Include SVG support?', 'zu-media'),
		help:	__('It includes SVG upload and using SVG in "Add Media" dialog boxes.', 'zu-media'),
		depends: false,
	},
};

const galleryType = {
	id: 'gallery_type',
	label: 	__('Gallery Type', 'zu-media'),
	help:	__('Choose which page slug will be considered as a gallery.', 'zu-media'),
	options: [
		{ value: 'pages', label: __('All Pages', 'zu-media') },
		{ value: 'portfolio', label: __('Pages with the parent of "portfolio" slug', 'zu-media') },
		{ value: 'gallery', label: __('Pages with the parent of "gallery" slug', 'zu-media') },
		{ value: 'albums', label: __('Pages with the parent of "albums" slug', 'zu-media') },
		{ value: 'images', label: __('Pages with the parent of "images" slug', 'zu-media') },
		{ value: 'photos', label: __('Pages with the parent of "photos" slug', 'zu-media') },
		{ value: 'posts', label: __('Posts with "gallery" format', 'zu-media') },
	],
	defaultValue: 'portfolio',
	divider: 2,
}

const folderIcons = [
	{ value: 'dashicons-images-alt', label: 'images-alt'} ,
	{ value: 'dashicons-images-alt2', label: 'images-alt2'} ,
	{ value: 'dashicons-format-gallery', label: 'format-gallery'} ,
	{ value: 'dashicons-portfolio', label: 'portfolio'} ,
	{ value: 'dashicons-admin-media', label: 'admin-media'} ,
];
const backIcons = [
	{ value: 'dashicons-editor-break', label: 'editor-break'} ,
	{ value: 'dashicons-arrow-left-alt', label: 'arrow-left-alt'} ,
	{ value: 'dashicons-undo', label: 'undo'} ,
	{ value: 'dashicons-arrow-left-alt2', label: 'arrow-left-alt2'} ,
	{ value: 'dashicons-exit', label: 'exit'} ,
];
const svgIcons = [
	{ value: 'simple', label: 'Simple Folder'} ,
	{ value: 'mac', label: 'Mac Folder'} ,
	{ value: 'zu', label: 'Zu Folder'} ,
	{ value: 'pack', label: 'Packed Folder'} ,
	{ value: 'open', label: 'Opened Folder'} ,
];

const folderColors = [
	{ name: 'Red', slug: 'red', color: '#e53a3d' },
	{ name: 'Yellow', slug: 'yellow', color: '#f6d33d' },
	{ name: 'Gold', slug: 'gold', color: '#c59940' },
	{ name: 'Magenta', slug: 'magenta', color: '#ce52b4' },
	{ name: 'Green', slug: 'green', color: '#47b4a0' },
	{ name: 'WordPress', slug: 'wp', color: '#66accf' },
	{ name: 'Blue', slug: 'blue', color: '#3c80cc' },
];

const folderTreeData = {
	id: 0, name: __('Media Library', 'zu-media'), children: [
		{ id: 1, name: __('Sunsets', 'zu-media'), color: 'magenta', children: [
			{ id: 4, name: __('Iceland', 'zu-media'), color: 'red', children: [] },
			{ id: 5, name: __('Italy', 'zu-media'), color: 'yellow', children: [] },
			{ id: 6, name: __('New Zealand', 'zu-media'), color: 'green', locked: true, children: [] },
		], opened: true, expanded: true },
		{ id: 2, name: __('Landscapes', 'zu-media'), locked: true, children: [] },
		{ id: 3, name: __('Portraits', 'zu-media'), color: 'blue', children: [
			{ id: 7, name: __('Studio', 'zu-media'), locked: true, children: [] },
			{ id: 8, name: __('Outdoors', 'zu-media'), children: [] },
		]},
	],
};

const folders = {
	non_empty: {
		label: 	__('Delete a non-empty folders?', 'zu-media'),
		help:	__('It allows to delete non-empty folders, all files in the deleted folder will be moved to the root one.', 'zu-media'),
	},
	hide_root: {
		label: 	__('Hide tree root?', 'zu-media'),
		help:	__('The tree root "Media Library" will be hidden and not available for drag & drop.', 'zu-media'),
	},
	root_icon: {
		label: 	__('Special icon for the root?', 'zu-media'),
		help:	__('The icon for the tree root "Media Library" will be different from all other folders in the tree.', 'zu-media'),
		depends: '!hide_root',
	},
	colored_tree: {
		label: 	__('Use colored folders in tree?', 'zu-media'),
		help:	__('Folder icons will be displayed in the tree according to the assigned color.', 'zu-media'),
	},
	boxed: {
		label: 	__('Represent folders as boxed icons', 'zu-media'),
		help: 	__('Current folders will be displayed as boxes with icons.', 'zu-media'),
	},
	icons: {
		folder: folderIcons,
		back: backIcons,
		svg: svgIcons,
	},
	colors: folderColors,
	tree: {
		folders: folderTreeData,
		id: 1,
	}
};

const panels = {
	folders: {
		value: true,
		label: 	__('Media Folders', 'zu-media'),
		// Это позволит исключить эту панель когда значение option is false
		depends: 'folders',
	},
	sizes: {
		value: true,
		label: 	__('Media Sizes', 'zu-media'),
	},
};

export const zumedia = {
	options,
	galleryType,
	folders,
	panels,
}
