// WordPress dependencies

const { map, get, isEmpty, isNil, isPlainObject } = lodash;

const { RawHTML } = wp.element;
const { Spinner, Tooltip, ExternalLink } = wp.components;
const { BlockIcon } = wp.blockEditor;

// Internal dependencies

import { mergeClasses, simpleMarkdown, getExternalData, hexToRGBA } from './../utils.js';

const getRowStyles = (index, colors) => {

	const borderOpacity = 0.3;
	const headBorderOpacity = 0.6;
	const oddOpacity = 0.4;
	const isEven = index % 2 == 0;
	const { backdrop, header, title } = colors;

	return index === 'table' ? {
		borderBottomColor: hexToRGBA(title, borderOpacity),
	} : (index === 'head' ? {
		backgroundColor: header,
		borderColor: hexToRGBA(title, headBorderOpacity),
	} : {
		color: title,
		backgroundColor: isEven ? backdrop : hexToRGBA(header, oddOpacity),
		borderBottomColor: hexToRGBA(title, borderOpacity),
	});
};

// Zukit Table Component

const ZukitTable = ({
		className,
		fixed,
		config,
		head,
		body,
		loading,
}) => {

	// style={ style }"white-space: pre-wrap;"
	const {
		align: cellAlign = [],
		style: cellStyle = [],
		className: cellClass = [],
	} = config || {};

	const colors = getExternalData('info.colors', {});

	const withContent = (content, params) => {
		if(isPlainObject(content)) {
			const { dashicon, svg, tooltip } = content;
			const icon = (
				<BlockIcon
					icon={ svg ? <RawHTML>{ svg }</RawHTML> : dashicon }
					showColors
				/>
			);
			return !tooltip ? icon : (
				<Tooltip
					text={ tooltip }
				>
					<div>{ icon }</div>
				</Tooltip>
			);
		} else {
			const { markdown = false, link } = params || {};
			if(markdown) return simpleMarkdown(content, { br: true, json: true });
			if(get(link, 'href')) {
				const { title, href } = link;
				return <ExternalLink href={ href }>{ title }</ExternalLink>
			}
			return content;
		}
	};

	const withStyle = (index, style) => {
		const commonStyle = get(cellStyle, index);
		if(isNil(style) && !commonStyle) return null;
		return {
			...(commonStyle || {}),
			...(style || {}),
		};
	}

	const withClass = (index, align, params) => {
		const commonClass = get(cellClass, index);
		const commonAlign = align || get(cellAlign, index) || 'left';
		return {
			[commonClass || '']: commonClass,
			[`has-text-align-${commonAlign}`]: commonAlign,
			'__zu_markdown': get(params, 'markdown'),
			'__zu_link': get(params, 'link.href'),
			[get(params, 'className')]: get(params, 'className'),
		};
	}

	const hasHead = !isEmpty(head);
	const hasRows = !isEmpty(body);

	return (
		<div
			className={ mergeClasses('zukit-table', className, {
				'has-fixed-layout': fixed,
				'is-loading': loading,
			}) }
			style={ getRowStyles('table', colors) }
		>
			{ hasHead &&
				<div className="head" style={ getRowStyles('head', colors) }>
					{ map(head, ({ content, align, style }, cellIndex) =>
						<div
							className={ mergeClasses('cell', 'head', withClass(cellIndex, align)) }
							key={ cellIndex }
							aria-label="Header label"
							style={ withStyle(cellIndex, style) }
						>
							{ content }
						</div>
					) }
				</div>
			}
			<div className="body">
				{ hasRows && map(body, (cells, rowIndex) =>
					<div className="row" key={ rowIndex } style={ getRowStyles(rowIndex, colors) }>
						{ map(cells, ({ content, align, style, params }, cellIndex) =>
							<div
								className={ mergeClasses('cell', withClass(cellIndex, align, params)) }
								key={ cellIndex }
								aria-label=""
								style={ withStyle(cellIndex, style) }
							>
								{ withContent(content, params) }
							</div>
						) }
					</div>
				) }
				{ loading && <Spinner/> }
			</div>
		</div>
	);
}

export default ZukitTable;
