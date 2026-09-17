/**
 * Display settings tab.
 *
 * Controls where the accordion appears. The product-page locations are
 * mutually exclusive, so they use a single-choice control; the page-level
 * locations render on their own pages and stay independent toggles.
 *
 * @package
 */

import { __, sprintf } from '@wordpress/i18n';
import { SelectControl, ToggleControl } from '@wordpress/components';

/**
 * Product-page positions.
 *
 * The accordion renders in exactly one place on a product page, so this is a
 * single-choice list. Order matches the settings service precedence.
 */
const PRODUCT_POSITIONS = [
	{
		slug: 'after_product_summary',
		label: __( 'Below the short description', 'smart-woocommerce-faq' ),
	},
	{
		slug: 'after_add_to_cart',
		label: __( 'Below the add-to-cart button', 'smart-woocommerce-faq' ),
	},
	{
		slug: 'after_product_meta',
		label: __( 'Below the SKU and categories', 'smart-woocommerce-faq' ),
	},
	{
		slug: 'product_tab',
		label: __( 'In a product tab', 'smart-woocommerce-faq' ),
	},
	{
		slug: 'after_single_product',
		label: __( 'At the end of the page', 'smart-woocommerce-faq' ),
	},
];

/**
 * Page-level positions. Each renders on its own page, so they are independent.
 */
const PAGE_POSITIONS = [
	{
		slug: 'shop_archive',
		label: __( 'Shop and category pages', 'smart-woocommerce-faq' ),
	},
	{ slug: 'cart', label: __( 'Cart page', 'smart-woocommerce-faq' ) },
	{ slug: 'checkout', label: __( 'Checkout page', 'smart-woocommerce-faq' ) },
];

/**
 * A titled group of settings.
 *
 * @param {Object}  root0             Props.
 * @param {string}  root0.title       Section heading.
 * @param {string}  root0.description Optional supporting line.
 * @param {number}  root0.enabled     Number of enabled positions.
 * @param {number}  root0.total       Total positions in the group.
 * @param {Element} root0.children    Section content.
 * @return {import('react').JSX.Element} Section element.
 */
function Section( { title, description, enabled, total, children } ) {
	return (
		<section className="wsfq-section">
			<header className="wsfq-section__header">
				<div className="wsfq-section__heading">
					<h2 className="wsfq-section__title">{ title }</h2>
					{ total > 1 && (
						<span className="wsfq-section__count">
							{ sprintf(
								/* translators: 1: number of enabled positions, 2: total number of positions. */
								__(
									'%1$d of %2$d on',
									'smart-woocommerce-faq'
								),
								enabled,
								total
							) }
						</span>
					) }
				</div>
				{ description && (
					<p className="wsfq-section__description">{ description }</p>
				) }
			</header>
			<div className="wsfq-section__body">{ children }</div>
		</section>
	);
}

/**
 * A single toggle row.
 *
 * @param {Object}   root0          Props.
 * @param {string}   root0.label    Row label.
 * @param {boolean}  root0.checked  Whether the toggle is on.
 * @param {boolean}  root0.disabled Whether the toggle is disabled.
 * @param {Function} root0.onChange Change callback.
 * @return {import('react').JSX.Element} Row element.
 */
function ToggleRow( { label, checked, disabled, onChange } ) {
	return (
		<div className="wsfq-setting">
			<ToggleControl
				__nextHasNoMarginBottom
				label={ label }
				checked={ checked }
				disabled={ disabled }
				onChange={ onChange }
			/>
		</div>
	);
}

/**
 * Display settings tab.
 *
 * @param {Object}   root0          Props.
 * @param {Object}   root0.settings Settings object.
 * @param {boolean}  root0.saving   Whether saving.
 * @param {Function} root0.onSave   Save callback.
 * @return {import('react').JSX.Element} Tab UI.
 */
export function DisplayTab( { settings, saving, onSave } ) {
	const display = settings?.display || {};
	const positions = display?.positions || {};

	/**
	 * Set the expand-all toggle.
	 *
	 * @param {boolean} value New value.
	 */
	const setExpandAll = ( value ) => {
		onSave( { display: { expand_all: value } } );
	};

	/**
	 * Set a page-level toggle.
	 *
	 * @param {string}  position Position slug.
	 * @param {boolean} value    New value.
	 */
	const setPosition = ( position, value ) => {
		onSave( { display: { positions: { [ position ]: value } } } );
	};

	/**
	 * Choose the single product-page position. Sends the whole set so exactly
	 * one stays on; the settings service enforces the same rule server-side.
	 *
	 * @param {string} slug Chosen position slug, or '' for none.
	 */
	const setProductPosition = ( slug ) => {
		const next = {};
		PRODUCT_POSITIONS.forEach( ( position ) => {
			next[ position.slug ] = position.slug === slug;
		} );
		onSave( { display: { positions: next } } );
	};

	const selectedProductPosition =
		PRODUCT_POSITIONS.find( ( position ) => positions[ position.slug ] )
			?.slug || '';

	const enabledPagePositions = PAGE_POSITIONS.filter(
		( position ) => positions[ position.slug ]
	).length;

	return (
		<>
			<Section
				title={ __( 'Product page', 'smart-woocommerce-faq' ) }
				description={ __(
					'Choose the one place FAQs appear on a product page.',
					'smart-woocommerce-faq'
				) }
			>
				<div className="wsfq-field">
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Show FAQs', 'smart-woocommerce-faq' ) }
						value={ selectedProductPosition }
						options={ [
							...PRODUCT_POSITIONS.map( ( position ) => ( {
								value: position.slug,
								label: position.label,
							} ) ),
							{
								value: '',
								label: __(
									'Don’t show on product pages',
									'smart-woocommerce-faq'
								),
							},
						] }
						disabled={ saving }
						onChange={ setProductPosition }
					/>
				</div>
			</Section>

			<Section
				title={ __( 'Other pages', 'smart-woocommerce-faq' ) }
				enabled={ enabledPagePositions }
				total={ PAGE_POSITIONS.length }
			>
				{ PAGE_POSITIONS.map( ( position ) => (
					<ToggleRow
						key={ position.slug }
						label={ position.label }
						checked={ !! positions[ position.slug ] }
						disabled={ saving }
						onChange={ ( value ) =>
							setPosition( position.slug, value )
						}
					/>
				) ) }
			</Section>

			<Section title={ __( 'Accordion', 'smart-woocommerce-faq' ) }>
				<ToggleRow
					label={ __(
						'Show an expand and collapse all button',
						'smart-woocommerce-faq'
					) }
					checked={ !! display.expand_all }
					disabled={ saving }
					onChange={ setExpandAll }
				/>
			</Section>
		</>
	);
}
