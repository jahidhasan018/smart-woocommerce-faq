/**
 * Display settings tab.
 *
 * Lets the user toggle the expand-all control and each display position.
 *
 * @package
 */

import { __ } from '@wordpress/i18n';
import {
	Card,
	CardBody,
	CardHeader,
	ToggleControl,
	Button,
} from '@wordpress/components';

const POSITIONS = [
	[ 'product_tab', __( 'Product tab', 'smart-woocommerce-faq' ) ],
	[ 'after_add_to_cart', __( 'After add-to-cart', 'smart-woocommerce-faq' ) ],
	[
		'after_product_meta',
		__( 'After product meta', 'smart-woocommerce-faq' ),
	],
	[
		'after_product_summary',
		__( 'After product summary', 'smart-woocommerce-faq' ),
	],
	[
		'after_single_product',
		__( 'After single product', 'smart-woocommerce-faq' ),
	],
	[ 'shop_archive', __( 'Shop / archive pages', 'smart-woocommerce-faq' ) ],
	[ 'cart', __( 'Cart page', 'smart-woocommerce-faq' ) ],
	[ 'checkout', __( 'Checkout page', 'smart-woocommerce-faq' ) ],
];

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
	 * Set a position toggle.
	 *
	 * @param {string}  position Position slug.
	 * @param {boolean} value    New value.
	 */
	const setPosition = ( position, value ) => {
		onSave( { display: { positions: { [ position ]: value } } } );
	};

	return (
		<>
			<Card>
				<CardHeader>
					{ __( 'Accordion', 'smart-woocommerce-faq' ) }
				</CardHeader>
				<CardBody>
					<ToggleControl
						label={ __(
							'Show expand-all / collapse-all control',
							'smart-woocommerce-faq'
						) }
						checked={ !! display.expand_all }
						onChange={ setExpandAll }
						disabled={ saving }
					/>
				</CardBody>
			</Card>

			<Card>
				<CardHeader>
					{ __( 'Display positions', 'smart-woocommerce-faq' ) }
				</CardHeader>
				<CardBody>
					{ POSITIONS.map( ( [ slug, label ] ) => (
						<ToggleControl
							key={ slug }
							label={ label }
							checked={ !! positions[ slug ] }
							onChange={ ( value ) => setPosition( slug, value ) }
							disabled={ saving }
						/>
					) ) }
				</CardBody>
			</Card>

			<Button
				variant="secondary"
				isBusy={ saving }
				onClick={ () => onSave( {} ) }
			>
				{ __( 'Save settings', 'smart-woocommerce-faq' ) }
			</Button>
		</>
	);
}
