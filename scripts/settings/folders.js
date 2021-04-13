// WordPress dependencies

const { get, mapKeys, omit, find } = lodash;
const { __ } = wp.i18n;
const { RangeControl, ColorPalette, BaseControl } = wp.components;
const { useCallback } = wp.element;

// Zukit dependencies

const { toggleOption } = wp.zukit.render;
const { mergeClasses, compareVersions } = wp.zukit.utils;
const { SelectItemControl, RawHTML, ZukitDivider, ZukitPanel } = wp.zukit.components;

// Internal dependencies

import FolderIcons from './folders-icons.js';
import ZumediaFoldersPreview from './folders-preview.js';

const FolderSVGs = FolderIcons(lodash).folders;
const optionsKey = 'zumedia_folders_options';

const getColor = (colors, value, slug = false) => {
	const color = find(colors, value ? ['color', value]: ['slug', slug]) || colors[0];
	return value ? color.slug : color.color;
}

const getFolderIcon = (value, selected = null, isSVG = false) => {
	if(isSVG) {
		return (
			<RawHTML
				tag="div"
				className={ `__svg folders-${value === selected ? 'magenta' : 'blue' }` }
			>
				{ FolderSVGs[value] }
			</RawHTML>
		);
	} else {
		return (<div className={ `dashicons ${value}` }></div>);
	}
}

const ZumediaFolders = ({
		wp,
		data,
		options,
		updateOptions,
}) => {

	const folders = get(options, optionsKey, {});
	const updateFolderOptions = useCallback(update => {
		const folderUpdate = mapKeys(update, (_, key) => `${optionsKey}.${key}`);
		updateOptions(folderUpdate);
	}, [updateOptions]);

	const setColor = useCallback(color => {
		updateFolderOptions({ color: getColor(data.colors, color) });
	}, [data.colors, updateFolderOptions]);

	if(options['folders'] === false) return null;

	const cls = ZumediaFoldersPreview.Classes;

	// do not use the last back icon for WP before 5.5
	const obsoleteVer = compareVersions(wp, '5.5') < 0;
	const backIcons = obsoleteVer ? data.icons.back.slice(0, -1) : data.icons.back;

	return (
			<ZukitPanel className="__folders" id="folders" options={ options } initialOpen={ true }>
				<div className="__folders_container">
					<div className="__folders_preview">
						<ZumediaFoldersPreview options={ folders } data={ data }/>
					</div>
					<div className="__folders_options">
						{ toggleOption(omit(data, ['icons', 'colors', 'tree']), options, updateOptions, optionsKey) }
						</div>
					</div>
						<ZukitDivider bottomHalf/>
						<div className="__colors_container">
							<BaseControl
								id="__folders-default-color"
								label={ __('Default Folder Color', 'zu-media') }
								help={ __('Will be used only for folders that have not been assigned an individual color.', 'zu-media') }>
								<ColorPalette
									colors={ data.colors }
									value={ getColor(data.colors, false, folders.color) }
									onChange={ setColor }
									disableCustomColors
									clearable={ false }
								/>
							</BaseControl>
							<div className={ cls.wrapper }>
								<div className={ mergeClasses(cls.browser, '__colors_example') }>
									<ZumediaFoldersPreview.Box
										id={ 1 }
										isBoxed={ folders.boxed }
										color={ folders.color }
										icons={ folders.icons }
										name={ __('Example', 'zu-media') }
										options={ folders }
									/>
								</div>
							</div>
						</div>
						<ZukitDivider bottomHalf/>
						<RangeControl
							label={ __('Tree Animation Speed, ms', 'zu-media') }
							help={ __('Animation duration when opening/closing folders in the tree (the more, the slower).', 'zu-media') }
							value={ folders.anim_speed }
							onChange={ value => updateFolderOptions({ anim_speed: value }) }
							step={ 100 }
							min={ 200 }
							max={ 600 }
						/>
						<ZukitDivider bottomHalf/>
						<SelectItemControl
							fillMissing
							columns={ 5 }
							label={ __('Select Back Icon', 'zu-media') }
							options={ backIcons }
							selectedItem={ folders.icons.back }
							onClick={ value => updateFolderOptions({ 'icons.back': value}) }
							transformValue={ getFolderIcon }
						/>
						{ folders.boxed &&
							<SelectItemControl
								columns={ 5 }
								label={ __('Select Boxed Folder Icon', 'zu-media') }
								options={ data.icons.folder }
								selectedItem={ folders.icons.folder }
								onClick={ value => updateFolderOptions({ 'icons.folder': value }) }
								transformValue={ getFolderIcon }
							/>
						}
						{ !folders.boxed &&
							<div className="__select-svg">
								<SelectItemControl
									columns={ 5 }
									label={ __('Select Folder Icon', 'zu-media') }
									options={ data.icons.svg }
									selectedItem={ folders.icons.svg }
									onClick={ value => updateFolderOptions({ 'icons.svg': value }) }
									transformValue={ value => getFolderIcon(value, folders.icons.svg, true) }
								/>
							</div>
						}
			</ZukitPanel>
	);
};

export default ZumediaFolders;
