// WordPress dependencies

const { get } = lodash;
const { BaseControl, Button, ToggleControl } = wp.components;
const { useCallback } = wp.element;

// Zukit dependencies

const { ZukitDivider, ZukitPanel, AdvTextControl } = wp.zukit.components;
const { simpleMarkdown } = wp.zukit.utils;

const foldersKey = 'zumedia_folders_options';

const ZumediaRewriteRules = ({
		data,
		options,
		updateOptions,
		resetOptions,
		ajaxAction,
}) => {

	const folderOps = get(options, foldersKey, {});
	const resetRules = useCallback(() => {
		resetOptions([
			`${foldersKey}.add_rewrite`,
			`${foldersKey}.rewrite`,
			'tag_rewrite',
			'category_rewrite'
		], () => ajaxAction('zumedia_flush_rewrite'));
	}, [resetOptions, ajaxAction]);

	return (
			<ZukitPanel id="rewrite" options={ options } initialOpen={ false }>
				<div className="__note">
					{ simpleMarkdown(data.note, { br: true }) }
				</div>
				{ options.folders  &&
					<>
						<ToggleControl
							label={ data.add_folders_rewrite }
							help={ simpleMarkdown(data.add_folders_rewrite_help, { br: true }) }
							checked={ !!folderOps.add_rewrite }
							onChange={ () => updateOptions({ [`${foldersKey}.add_rewrite`]: !folderOps.add_rewrite }) }
						/>
						{ folderOps.add_rewrite  &&
							// use <BaseControl> here to separate the label from the text control and allow 'flex' to align the boxes
							<BaseControl label={ data.folders_rewrite } id="folders-rewrite-text-control">
								<div className="__flex __rules">
									<AdvTextControl
										value={ folderOps.rewrite || '' }
										onChange={ value => updateOptions({ [`${foldersKey}.rewrite`]: value }) }
									/>
									<div className="__tag">
										<span>^<i>{ folderOps.rewrite  }</i>/([0-9]+)/?</span>
									</div>
									<div className="__rule">
										<span>index.php?post_type=<i>attachment</i>&<i>{ folderOps.rewrite }_id</i>=$matches[1]</span>
									</div>
								</div>
							</BaseControl>
						}
					</>
				}
				{ (options.folders && (options.add_tags || options.add_category)) &&
					<ZukitDivider bottomHalf size={ 2 }/>
				}
				{ options.add_tags  &&
					<BaseControl label={ data.tag_rewrite } id="tag-rewrite-text-control">
						<div className="__flex __rules">
							<AdvTextControl
								id="tag-rewrite-text-control"
								value={ options.tag_rewrite || '' }
								onChange={ value => updateOptions({ tag_rewrite: value }) }
							/>
							<div className="__tag">
								<span>^<i>{ options.tag_rewrite }</i>/([^/]*)/?</span>
							</div>
							<div className="__rule">
								<span>index.php?post_type=<i>attachment</i>&<i>tag</i>=$matches[1]</span>
							</div>
						</div>
					</BaseControl>
				}
				{ options.add_category  &&
					<BaseControl label={ data.category_rewrite } id="category-rewrite-text-control">
						<div className="__flex __rules">
							<AdvTextControl
								id="category-rewrite-text-control"
								value={ options.category_rewrite || '' }
								onChange={ value => updateOptions({ category_rewrite: value }) }
							/>
							<div className="__tag">
								<span>^<i>{ options.category_rewrite }</i>/([^/]*)/?</span>
							</div>
							<div className="__rule">
								<span>index.php?post_type=<i>attachment</i>&<i>category</i>=$matches[1]</span>
							</div>
						</div>
					</BaseControl>
				}
				<ZukitDivider bottomHalf size={ 2 }/>
				<div className="__flex __right">
					<Button
						isSecondary
						className="__plugin_actions __auto magenta"
						label={ data.resetRules }
						icon="image-rotate"
						onClick={ resetRules }
					>
						{ data.resetRules }
					</Button>
				</div>
			</ZukitPanel>
	);
};

export default ZumediaRewriteRules;
