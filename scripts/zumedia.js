// WordPress dependencies

// Zukit dependencies

const { renderPage, toggleOption, selectOption } = wp.zukit.render;
const { ZukitPanel } = wp.zukit.components;

// Internal dependencies

import { zumedia } from './settings/data.js';
import ZumediaFolders from './settings/folders.js';
import ZumediaSizes from './settings/sizes.js';
import ZumediaRewriteRules from './settings/rewrite.js';

const EditZumedia = ({
		wp,
		title,
		options,
		updateOptions,
		resetOptions,
		setUpdateHook,
		ajaxAction,
}) => {

	const { options: optionsData, galleryType: galleryData, folders, rewrite } = zumedia;
// Zubug.data({ rewrite });

	return (
			<>
				<ZukitPanel title={ title }>
					{ toggleOption(optionsData, options, updateOptions) }
					{ selectOption(galleryData, options, updateOptions) }
				</ZukitPanel>
				<ZumediaFolders
					wp={ wp }
					data={ folders }
					options={ options }
					updateOptions={ updateOptions }
					ajaxAction={ ajaxAction }
					setUpdateHook={ setUpdateHook }
				/>
				<ZumediaRewriteRules
					data={ rewrite }
					options={ options }
					updateOptions={ updateOptions }
					resetOptions={ resetOptions }
					ajaxAction={ ajaxAction }
				/>
				<ZumediaSizes
					ajaxAction={ ajaxAction }
					setUpdateHook={ setUpdateHook }
				/>
			</>
	);
};

renderPage('zumedia', {
	edit: EditZumedia,
	panels: zumedia.panels,
});
