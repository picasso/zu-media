// WordPress dependencies

// Zukit dependencies

const { renderPage, toggleOption, selectOption } = wp.zukit.render;
const { ZukitPanel } = wp.zukit.components;

// Internal dependencies

import { zumedia } from './settings/data.js';
import ZumediaFolders from './settings/folders.js';
import ZumediaSizes from './settings/sizes.js';

const EditZumedia = ({
		title,
		options,
		updateOptions,
		setUpdateHook,
		ajaxAction,
}) => {

	const { options: optionsData, galleryType, folders } = zumedia;

	return (
			<>
				<ZukitPanel title={ title }>
					{ toggleOption(optionsData, options, updateOptions) }
					{ selectOption(options[galleryType.id], galleryType, updateOptions) }
				</ZukitPanel>
				<ZumediaFolders
					data={ folders }
					options={ options }
					updateOptions={ updateOptions }
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
