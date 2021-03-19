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

	const [sizes, setSizes] = useState(null);

	const onToggle = useCallback(() => {
		if(sizes === null) {
			ajaxAction('zumedia_all_sizes', sizesData => {
				const headers = get(sizesData, 'headers', []);
				const rows = get(sizesData, 'rows', []);
				const config = get(sizesData, 'config', {});
				if(rows.length) setSizes({ config, headers, rows });
			});
		}

	}, [ajaxAction, sizes]);

	// reset sizes table when 'responsive' or 'full_hd' option is updated
	useEffect(() => {
		setUpdateHook(['responsive', 'full_hd'], () => {
			setSizes(null);
		});
	}, [setSizes, setUpdateHook]);

	return (
			<ZukitPanel id="sizes" initialOpen={ false } onToggle={ onToggle }>
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
