// External dependencies

import classnames from 'classnames';

// WordPress dependencies

const _ = lodash;
const { __ } = wp.i18n;
const { Path, G, SVG } = wp.components;
const { getCategories, setCategories, registerBlockCollection } = wp.blocks;

// Gets JSON data from PHP
export function externalData(key, defaultValues = null) {
	const { data = {} } =  window[key] || {};
	return _.isEmpty(defaultValues) ? data : _.defaults(data, defaultValues);
}

// Just a more familiar name
export const mergeClasses = classnames;

// Checks if value can be converted to Number
export function isNum(n) {
	return !_.isNaN(parseFloat(n)) && isFinite(n);
}

// convert argument to Boolean value
// works for true, false, 0, 1, "true", "false", "TRUE", "FALSE", "0", "1", undefined
export function toBool(val, stringOrNull = false) {
	let num;
	const value = val != null && (!_.isNaN(num = +val) ? !!num : !!String(val).toLowerCase().replace(!!0,''));
	return stringOrNull ? (value ? String(value) : null) : value;
}

export function toRange(num, min, max, useMinOnErr = true) {
	let value = _.isNaN(+num) ? (useMinOnErr ? min : max) : num;
	return  _.clamp(_.round(value), min, max);
}

// Create key for react components from 'value' or other params
export function getKey(value, more) {
	const source = _.isString(value) || isNum(value) ? String(value) : String(more);
	let hash = 0, i;
	for(i = 0; i < source.length; i++) {
		hash = ((hash << 5) - hash) + source.charCodeAt(i);
		hash |= 0; // Convert to 32bit integer
	}
	return String(hash);
}

export function isWrongId(id) {
	return _.isNil(id) || (isNum(id) && parseInt(id, 10) === 0);
}

// Creates an 'ids' array from an array (which should have property 'id').
// Ensures the ids array contains numbers
export function getIds(items, asString = false) {
	// always ensure the ids array contains numbers.
	// And remove all elements with "empty" value (undefined, null)
	if(!items || items.length === 0) return asString ? '' : [];
	const ids = _.compact(_.map(items, (value) => value && value.id && parseInt(value.id, 10)));
	return asString ? _.join(ids, ',') : ids;
}

export function checkDependency(item, options, isAction = false, withPath = null) {
	const depends = isAction ? item : _.get(item, 'depends');
	if(_.isNil(depends)) return true;
	if(depends === false) return false;

	const cleanKey = _.trimStart(depends, '!');
	const value =_.get(options, withPath ? `${withPath}.${cleanKey}` : cleanKey, false);
	return _.startsWith(depends, '!') ? !value : value;
}

// Convert object to JSON
export function toJSON(obj) {
	if(obj) {

		try {
			obj = JSON.stringify(obj);
		} catch(err) {
			obj = '{}';
		}
	}
	return obj || '{}';
}

// Creates a new value using the template '<text>-<index>' while avoiding values from 'forbiddenValues'
export const uniqueValue = (value, forbiddenValues, fallback = 'name') => {
	const formatted =  String(value).replace(/([^-|\d])(\d+)$/, '$1-$2');
	if(_.includes(forbiddenValues, formatted)) {
		let index = 0;
		const valueBody = String(formatted).replace(/-\d+$/, '').replace(/\d+$/, '') || fallback;
		while(++index > 0) {
			const testValue = `${valueBody}-${index}`;
			if(!_.includes(forbiddenValues, testValue)) return testValue;
		}
	}
	return formatted;
}

// Add error value (if present) to the message, converting any object to a string
export function messageWithError(message, value = null) {

	if(_.isNil(value)) return message;

	value = _.isArray(value) || _.isPlainObject(value) ? toJSON(value) : String(value);
	value = value
		.replace(/([{|}])/g, ' $1 ')
		.replace(/,\s*/g, ',  ')
		.replace(/"([^"]+)":/g, '<b>$1</b>: ');

	return message.replace(/[:|.]\s*$/g, '') + `: <span class="zukit-data">${value}</span>`;
}

// Returns SVG with a reference to an already loaded SVG set
export function svgRef(id, icon = false, moreClasses = '', iconsSize = 24) {

	const size = iconsSize;	// change viewBox values according to Icons Set
	return (
		<svg
			className={ classnames('zu-svg', { icon }, `icon-${id}`, moreClasses) }
			role="img"
			aria-labelledby="title"
			viewBox={ `0 0 ${size} ${size}` }
			preserveAspectRatio="xMidYMin slice"
		>
			<use xlinkHref={ `#${id}` }/>
		</svg>
	);
}

export function hexToRGB(hex, asObject = false) {
	const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	const rgb = result ? {
		r: parseInt(result[1], 16),
		g: parseInt(result[2], 16),
		b: parseInt(result[3], 16)
	} : null;
	return rgb ? (asObject ? rgb : `rgb(${rgb.r},${rgb.g},${rgb.b})`) : null;
}

export function hexToRGBA(hex, alpha, asObject = false) {
	const rgb = hexToRGB(hex, true);
	if(rgb === null) return null;
	const rgba = _.set({ ...rgb }, 'a', alpha);
	return asObject ? rgba : `rgba(${rgba.r},${rgba.g},${rgba.b},${rgba.a})`;
}

// compare version numbers with segments
// Return values:
// 		a number < 0 if a < b
//		a number > 0 if a > b
//		0 if a = b
export function compareVersions(a, b) {
    let i, diff;
    const regExStrip0 = /(\.0+)+$/;
    const segmentsA = String(a).replace(regExStrip0, '').split('.');
    const segmentsB = String(b).replace(regExStrip0, '').split('.');
    const l = Math.min(segmentsA.length, segmentsB.length);

    for(i = 0; i < l; i++) {
        diff = parseInt(segmentsA[i], 10) - parseInt(segmentsB[i], 10);
        if(diff) return diff;
    }
    return segmentsA.length - segmentsB.length;
}

// Some useful data -----------------------------------------------------------]

export const emptyGif = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';

const { colors: zukitColors = {}} = externalData('zukit_jsdata');

// Returns one of predefined in SASS files colors
export function getColor(key) {
	return _.get(zukitColors, key, '#cc1818');
}

// Brand assets ---------------------------------------------------------------]


export const brandAssets = {
	namespace: 'zu',
	slug: 'zu-blocks',
	color: getColor('violet'),
	icon: null,
	title: __('Zu Blocks', 'zukit'),
};

brandAssets.icon = (
<SVG
	width="24"
	height="24"
	viewBox="0 0 24 24"
	xmlns="http://www.w3.org/2000/svg"
>
	<G>
		<Path d="M15.22,6.873 C15.22,6.873 14.383,8.096 13.914,12.049 C13.445,16.006 17.266,15.5 17.266,15.5 Q19.264,15.312 19.264,13.224 C19.264,13.224 19.172,6.516 19.264,6.873 C20.766,9.109 23.242,6.873 23.242,6.873 L23.242,13.993 Q23.242,16.279 21.737,17.422 Q20.231,18.565 17.242,18.565 Q14.42,18.27 12.914,17.127 C12.914,17.127 11.336,16.393 10.367,13.908 C9.107,10.676 11.242,6.873 11.242,6.873 z" fill={ brandAssets.color }/>
		<Path d="M7.448,14.858 C8.266,16.469 11.164,15.236 11.164,15.236 L17.242,18.565 L0.758,18.565 L6.08,10.203 L1.47,10.203 C1.47,10.203 3.141,7.828 1.47,6.873 C0.922,6.844 12.742,6.873 12.742,6.873 C12.742,6.873 6.256,12.508 7.448,14.858 z" fill={ brandAssets.color }/>
	</G>
</SVG>
);

// Register ZU blocks category (or any other if 'categoryData' is presented)
export function registerCategory(categoryData = null) {
	const category = _.isEmpty(categoryData) ? {
		slug: brandAssets.slug,
		title: brandAssets.title,
		icon: brandAssets.icon,
	} : categoryData;

	setCategories([
		category,
		...getCategories().filter(({ slug }) => slug !== category.slug),
	]);
}

export function registerCollection(collectionData = null) {
	const collection = _.isEmpty(collectionData) ? {
		namespace: brandAssets.namespace,
		title: brandAssets.title,
		icon: brandAssets.icon,
	} : collectionData;

	if(typeof registerBlockCollection === 'function') {
		registerBlockCollection(collection.namespace, collection);
		return true;
	}
	return false;
}

// Subset of functions for 'zukit-blocks'
export const blocksSet = {
	registerCategory,
	registerCollection,
	externalData,
	mergeClasses,
	hexToRGB,
	hexToRGBA,
	isNum,
	toBool,
	toRange,
	getKey,
	getIds,
	getColor,
	toJSON,
	uniqueValue,
	svgRef,
	emptyGif,
	brandAssets,
};
