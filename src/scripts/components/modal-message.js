// WordPress dependencies

const { map, reduce, castArray } = lodash;
const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { Button, Icon, Modal } = wp.components;

// Internal dependencies

import { mergeClasses } from './../utils.js';
import { warning as warningIcon, error as errorIcon, info as infoIcon } from './../icons.js';
import RawHTML from './raw.js';

// Modal Message Component

export function simpleMarkdown(string, links) {

	const linkReplace = '<a href="$2" target="_blank" rel="external noreferrer noopener">'+
						'$1<span class="components-external-link__icon dashicon dashicons dashicons-external"/></a>';
	// replace links
	let md = reduce(castArray(links || []), (msg, link, index) => msg.replace(`$link${index + 1}`, link), string);
	// replace <strong>
	md = md.replace(/\*\*([^*]+)\*\*/gm, '<strong>$1</strong>');
	// replace <em>
	md = md.replace(/([^*])\*([^*]+)\*/gm, '$1<em>$2</em>');
	// replace <a>
	md = md.replace(/\[([^\]]+)\]\(([^)]+)\)/gm, linkReplace);

	return md.split('\n').map((line, key) => <RawHTML key={ key }>{ line }</RawHTML>);
}

const ModalMessage = ({
		className,
		icon,
		message,
		links,
		withoutCloseButton,
		isOpen,
		onClose,
		children,
}) => {

	const modalIcon = icon === 'warning' ? warningIcon : (icon === 'error' ? errorIcon : infoIcon);

	return isOpen && (
		<Modal
			className={ mergeClasses('zukit-modal', className) }
			title={ __('Warning', 'zu-contact') }
			closeLabel={ __('Close') }
			onRequestClose={ onClose }
		>
			<div className="__content-wrapper">
				<Icon className="__icon" icon={ modalIcon }/>
				<div>
					{ simpleMarkdown(message, links) }
				</div>
			</div>
			<div className="__button-wrapper">
				{ map(castArray(children || []), (button, key) => <Fragment key={ key }>{ button }</Fragment>) }
				{ !withoutCloseButton &&
					<Button isPrimary onClick={ onClose }>
						{ __('Close') }
					</Button>
				}
			</div>
		</Modal>
	);
}

export default ModalMessage;
