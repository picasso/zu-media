// WordPress dependencies

const { get } = lodash;
const { useCallback, useState, useEffect } = wp.element;

// Zukit dependencies

const { ZukitTable, ZukitPanel } = wp.zukit.components;

const ZumediaSizes = ({
		// options,
		ajaxAction,
		setUpdateHook,
}) => {

	const [isOpen, setIsOpen] = useState(false);
	const [sizes, setSizes] = useState(null);

	const onToggle = useCallback(() => {
		setIsOpen(prev => !prev);
	}, []);

	// reset sizes table when 'responsive' or 'full_hd' option is updated
	useEffect(() => {
		setUpdateHook(['responsive', 'full_hd'], () => {
			setSizes(null);
		});
		if(isOpen && sizes === null) {
			// request data for table of sizes
			ajaxAction('zumedia_all_sizes', sizesData => {
				const headers = get(sizesData, 'headers', []);
				const rows = get(sizesData, 'rows', []);
				const config = get(sizesData, 'config', {});
				if(rows.length) setSizes({ config, headers, rows });
			});
		}
	}, [isOpen, sizes, setUpdateHook, ajaxAction]);

	return (
		<ZukitPanel id="sizes" initialOpen={ isOpen } onToggle={ onToggle }>
			<ZukitTable
				fixed={ true }
				config={ sizes && sizes.config }
				head={ sizes && sizes.headers }
				body={ sizes && sizes.rows }
				loading={ sizes === null }
			/>
		</ZukitPanel>
	);
};

export default ZumediaSizes;
