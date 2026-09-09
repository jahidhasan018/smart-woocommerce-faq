/**
 * Smart FAQ Gutenberg block — editor script (Prebuilt mode).
 *
 * Registers the wsfq/faq block and renders a FAQ-picker in the editor.
 *
 * @package
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { FormTokenField, Placeholder, Spinner } from '@wordpress/components';

import './style.scss';

/**
 * FAQ picker edit component.
 *
 * @param {Object}   root0               Component props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Attribute setter.
 * @return {import('react').JSX.Element} Editor UI.
 */
function FaqBlockEdit( { attributes, setAttributes } ) {
	const { faqIds } = attributes;

	const { faqs, isLoading } = useSelect( ( select ) => {
		const { getEntityRecords, isResolving } = select( 'core' );
		return {
			faqs: getEntityRecords( 'postType', 'wsfq_faq', { per_page: 50 } ),
			isLoading: isResolving( 'core', 'getEntityRecords', [
				'postType',
				'wsfq_faq',
				{ per_page: 50 },
			] ),
		};
	}, [] );

	if ( isLoading || ! faqs ) {
		return (
			<Placeholder
				icon="editor-help"
				label={ __( 'Smart FAQ', 'smart-woocommerce-faq' ) }
			>
				<Spinner />
			</Placeholder>
		);
	}

	const suggestions = faqs.map( ( faq ) => faq.title?.rendered || faq.slug );

	const handleChange = ( tokens ) => {
		const selected = faqs.filter( ( faq ) => {
			const label = faq.title?.rendered || faq.slug;
			return tokens.includes( label );
		} );
		setAttributes( { faqIds: selected.map( ( faq ) => faq.id ) } );
	};

	const currentTokens = faqs
		.filter( ( faq ) => faqIds.includes( faq.id ) )
		.map( ( faq ) => faq.title?.rendered || faq.slug );

	return (
		<Placeholder
			icon="editor-help"
			label={ __( 'Smart FAQ', 'smart-woocommerce-faq' ) }
		>
			<p className="wsfq-block-label">
				{ __(
					'Select FAQs from your library',
					'smart-woocommerce-faq'
				) }
			</p>
			<FormTokenField
				value={ currentTokens }
				suggestions={ suggestions }
				onChange={ handleChange }
				label={ __( 'FAQs to display', 'smart-woocommerce-faq' ) }
			/>
		</Placeholder>
	);
}

registerBlockType( 'wsfq/faq', {
	edit: FaqBlockEdit,
	save: () => null,
} );
