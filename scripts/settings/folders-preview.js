// WordPress dependencies

const $ = jQuery;
const { get, map, each, merge, isEmpty, findIndex, noop } = lodash;
const { __ } = wp.i18n;
const { useCallback, useState } = wp.element;

// Zukit dependencies

const { mergeClasses } = wp.zukit.utils; // isNum, // toBool, // toJSON, // uniqueValue,
const { RawHTML } = wp.zukit.components;

// Internal dependencies

import FolderIcons from './folders-icons.js';
const Icons = FolderIcons(lodash);

const cls = (() => {
	const prefix = 'mfs';
	return {
		prefix,
		tree: `${prefix}-tree`,
		node: `${prefix}-node`,
		root: `${prefix}-root`,
		row: `${prefix}-row`,
		root_row: `${prefix}-root-row`,
		expandable: `${prefix}-expandable`,
		holder: `${prefix}-holder`,
		icon: `${prefix}-icon`,
		item: `${prefix}-item`,
		svg: `${prefix}-svg`,

		wrapper: `${prefix}-wrapper`,
		browser: `${prefix}-browser`,
		back: `${prefix}-back`,
		icon_back: `__back icon dashicons`,
		folder: `${prefix}-folder`,
		locked: `${prefix}-locked`,
		preview: {
			container: `${prefix}-folder-preview`,
			svg: `__svg`,
			boxed: `__boxed`,
			icon: `${prefix}-folder-svg`,
			dashicon: 'icon dashicons',
			locked: `__locked dashicons`,
			name: `${prefix}-folder-name`,
		},
	};
})();

function updateTreeNode(tree, update, found, parentId) {

	if(found === undefined) found = { update: true };
	if(parentId === undefined) parentId = 0;

	each(tree, node => {
		if(node.id === update.id) {
			if(found.update) {
				// if update
				found.index = findIndex(tree, { id: update.id });
				found.node = merge({}, node, update);
				tree[found.index] = found.node;
			} else {
				// if simple search
				found.node = node;
				found.parentId = parentId;
			}
			return false;
		} if(!isEmpty(node.children)) {
			updateTreeNode(node.children, update, found, node.id);
			// abort search if already found
			if(found.node !== undefined) return false;
		}
	});
}

function updateTree(tree, id, opened, expanded) {
	let update = { id, opened };
	if(expanded !== undefined) update.expanded = expanded;
	if(id === 0) merge(tree, update)
	else updateTreeNode(tree.children, update);
	return { ...tree, children: [...tree.children] };
}

function findChildren(tree, id) {
	if(id === 0) return tree.children;
	else {
		let found = {};
		updateTreeNode(tree.children, {id}, found);
		return get(found, ['node', 'children'], []);
	}
}

function findParent(tree, id) {
	if(id === 0) return 0;
	else {
		let found = {};
		updateTreeNode(tree.children, {id}, found);
		return get(found, ['parentId'], 0);
	}
}


function expandNode(id, expand, speed, easing, callback) {
	var $node = $(`#${cls.tree}`).find(`li[data-id="${id}"] > .${cls.node}`);
	if($node.length === 0) callback();
	else $node[expand ? 'slideDown': 'slideUp'](speed, easing, callback);
}

const TreeIcon = ({
	id,
}) => {
	return (
		<svg
			className={ `${cls.svg} ${cls.prefix}-${id}` }
			role="img"
			aria-labelledby="title"
			viewBox="0 0 24 24"
			preserveAspectRatio="xMidYMin slice"
		>
			<use href={ `#${cls.prefix}-${id}` }></use>
		</svg>
	);
};

const TreeNode = ({
		id,
		name,
		color,
		expanded,
		opened,
		children,
		onExpand,
		onClick,
		options = {},
}) => {

	const nodeIcon = id === 0 && options.root_icon ? <RawHTML tag='span'>{ Icons.svg('home') }</RawHTML> : (
		<>
			<TreeIcon id="opened"/>
			<TreeIcon id="closed"/>
		</>
	);

	const nodeColor = color || options.color;

	return (
		<li
			className={ mergeClasses(
				`folders-${nodeColor}`, {
					[cls.root]: id === 0,
					expanded: expanded,
					collapsed: !expanded,
			}) }
			data-id={ id }
		>
			<div
				className={ mergeClasses(cls.row, { [cls.root_row]: id === 0 }) }
				style={ id === 0 && options.hide_root ? { display: 'none' } : null }
			>
				{ id === 0 ? null :
					<div className={ cls.expandable } data-id={ id } style={ { opacity: isEmpty(children) ? 0 : 1 } }>
						<div className={ cls.holder } onClick={ () => onExpand(id, opened, expanded) }>
							<TreeIcon id="minus"/>
							<TreeIcon id="plus"/>
						</div>
					</div>
				}
				<div className={ mergeClasses(cls.icon, { opened, closed: !opened }) }>
					<div className={ cls.holder }>{ nodeIcon }</div>
				</div>
				<a className={ cls.item } data-id={ id } onClick={ () => onClick(id) }>{ name }</a>
			</div>
			{ isEmpty(children) ? null :
				<ul className={ cls.node } style={ id === 0 || expanded ? null : { display: 'none' } }>
					{ map(children, node =>
						<TreeNode
							key={ node.id }
							options={ options }
							onExpand={ onExpand }
							onClick={ onClick }
							{ ...node }
						/>
					) }
				</ul>
			}
		</li>
	);
};

const FolderBox = ({
		isBack,
		isBoxed,
		id,
		parentId,
		name,
		color,
		locked,
		icons,
		defaultColor = 'none',
		onClick = noop,
}) => {

	const svg = isBoxed || isBack ? null : <RawHTML tag='span'>{ Icons.folders[icons.svg] }</RawHTML>;

	return (
		<li
			className={ mergeClasses(cls.folder, { [cls.locked]: locked, [cls.back]: isBack }) }
			data-id={ isBack ? parentId : id }
			onClick={ () => onClick(isBack ? parentId : id) }
		>
			<div className={ mergeClasses(cls.preview.container, `folders-${color || defaultColor}`, {
					[cls.preview.boxed]: isBoxed,
					[cls.preview.svg]: !isBoxed,
			}) }>
				<div className={ mergeClasses(isBack ? cls.icon_back : cls.icon, {
						[cls.preview.icon]: !isBack && !isBoxed,
						[cls.preview.dashicon]: !isBack && isBoxed,
						[icons.folder]: !isBack && isBoxed,
						[icons.back]: isBack,
				}) }>{ svg }</div>
				<div className={  mergeClasses(cls.preview.locked, icons.lock) }></div>
				<div className={ cls.preview.name }>
					<div>{ isBack ? __('Back', 'zu-media') : name }</div>
				</div>
			</div>
		</li>
	);
};

const ZumediaFoldersPreview = ({
		options,
		data,
}) => {

	const [tree, setTree] = useState(data.tree.folders);
	const [folders, setFolders] = useState(findChildren(data.tree.folders, data.tree.id));
	const [selectedId, setSelectedId] = useState(data.tree.id);

	const onExpand = useCallback((id, opened, expanded) => {
		expandNode(id, !expanded, options.anim_speed, options.anim_easing, () => {
			const updated = updateTree(tree, id, opened, !expanded);
			setTree(updated);
		});
	}, [tree, options.anim_speed, options.anim_easing]);

	const onClick = useCallback(id => {
		if(id === selectedId) return;
		const updated = updateTree(tree, selectedId, false);
		setTree(updated);
		setSelectedId(id);
		onExpand(id, true, false);
		setFolders(findChildren(updated, id))
	}, [tree, onExpand, selectedId]);

	return (
		<div className={ mergeClasses(cls.wrapper, {
			__colors: options.colored_tree,
			'__boxed-mode': options.boxed,
			'__svg-mode': !options.boxed,
		}) }>
			<RawHTML tag="span">{ Icons.collection() }</RawHTML>
			<ul id={ cls.tree } className={ mergeClasses(cls.node, { __colors: options.colored_tree }) }>
				<TreeNode
					key={ tree.id }
					onExpand={ onExpand }
					onClick={ onClick }
					options={ options }
					{ ...tree }
				/>
			</ul>
			<ul className={ cls.browser }>
				{ selectedId !== 0 &&
					<FolderBox
						isBack
						isBoxed={ options.boxed }
						parentId={ findParent(tree, selectedId) }
						icons={ options.icons }
						onClick={ onClick }
					/>
				}{
					map(folders, folder =>
						<FolderBox
							key={ folder.id }
							isBoxed={ options.boxed }
							icons={ options.icons }
							defaultColor={ options.color }
							onClick={ onClick }
							{ ...folder }
						/>
					)
				}
			</ul>
		</div>
	);
};

ZumediaFoldersPreview.Box = FolderBox;
ZumediaFoldersPreview.Classes = cls;
export default ZumediaFoldersPreview;
