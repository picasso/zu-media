// WordPress dependencies

const { isNil, isEmpty, debounce } = lodash;
const { __ } = wp.i18n;
const { Button, TextControl, Tooltip } = wp.components;
const { useCallback, useState } = wp.element;

// Internal dependencies

import { mergeClasses, uniqueValue } from './../utils.js';
import ConditionalWrap from './conditional-wrap.js';

// Zukit Text Control Component

const labels = {
	show: __('Show Password', 'zukit'),
	hide: __('Hide Password', 'zukit'),
	clear: __('Clear', 'zukit'),
};

const isKind = (kind, value) => {

	if(value === '' || isNil(kind)) return true;
	if(kind === 'number') return /^[0-9]+$/g.test(value);
	if(kind === 'email') return /^[a-zA-Z0-9._@-]+$/g.test(value);
	if(kind === 'url') return /^[.a-zA-Z0-9-]+$/g.test(value);
	if(kind === 'tel' || kind === 'phone') return /^[0-9()+-\s]+$/g.test(value);
	// otherwise any string that we use as a Regular Expression
	const re = new RegExp(kind);
	return re.test(value);
}

const AdvTextControl = ({
		className,
		isPassword,
		showTooltip = true,
		withoutClear,
		label,
		value,
		help,
		type,
		strict,

		withDebounce,
		debounceDelay = 1000,
		withoutValues = null,
		fallbackValue = 'name',

		onChange,
}) => {

	const [visible, setVisible] = useState(false);
	const controlType = isPassword ? (visible ? 'text' : 'password') : (type || 'text');
	const controlIcon = isPassword ? (visible ? 'hidden' : 'visibility') : 'no-alt';
	const controlTooltip = isPassword ? (visible ? labels.hide : labels.show) : labels.clear;

	const onClick = useCallback(() => isPassword ? setVisible(!visible) : onChange(''), [isPassword, visible, onChange]);

	// Debounce ---------------------------------------------------------------]

	// using temporaryName while debouncing name changes
	const [ temporaryValue, setTemporaryValue ] = useState(value);

	// using debouncing to reduce the number of calls to the onChange handler
	// eslint-disable-next-line react-hooks/exhaustive-deps
	const onUpdateValue = useCallback(
		debounce(value => {
			onChange(value);
		}, debounceDelay)
	, [onChange, debounceDelay]);

	const onChangeValue = useCallback(value => {
		setTemporaryValue(value);
		onUpdateValue(value);
	}, [onUpdateValue]);

	// Validate ---------------------------------------------------------------]

	const withButton = isPassword || !withoutClear;

	const onValidatedChange = useCallback(val => {
		const value = isEmpty(withoutValues) ? val : uniqueValue(val, withoutValues, fallbackValue);
		if(isKind(strict, value)) {
			if(withDebounce) onChangeValue(value);
			else onChange(value);
		}
	}, [strict, withDebounce, onChange, onChangeValue, withoutValues, fallbackValue]);

	return (
		<div className={ mergeClasses(
			'components-base-control', 'zukit-text-control', className,
			{
				'__with-label': label && withButton,
				// '__with-help': help && withButton,
				'__with-label-help': label && help && withButton,
				'__with-button': withButton,
			}
		) }>
			<TextControl
				type={ controlType }
				label={ label }
				help={ help }
				value={ (withDebounce ? temporaryValue : value) || '' }
				onChange={ onValidatedChange }
			/>
			{ withButton &&
				<ConditionalWrap
					wrap={ Tooltip }
					condition={ showTooltip }
					text={ controlTooltip }
					position="top center"
				>
					<Button
						className={ mergeClasses('__exclude',
						{
							'__with-label': label && withButton,
							// '__with-help': help && withButton,
							'__with-label-help': label && help && withButton,
						}
					) }
						icon={ controlIcon }
						onClick={ onClick }
					/>
				</ConditionalWrap>
			}
		</div>
	);
}

export default AdvTextControl;
