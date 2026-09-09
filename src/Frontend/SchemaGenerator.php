<?php
/**
 * Google FAQPage JSON-LD schema generator.
 *
 * Builds the FAQPage structured-data graph from FAQ posts and renders it as a
 * JSON-LD script tag. Used on product pages (resolved FAQs) and standalone FAQ
 * pages.
 *
 * Hooks:
 * - `wsfq_schema_faq_ids` (filter, int[] $faq_ids, array $context) — choose FAQs.
 * - `wsfq_schema_data` (filter, array|null $schema, array $context) — mutate schema.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Frontend;

/**
 * JSON-LD schema generator.
 */
final class SchemaGenerator {

	/**
	 * Build the FAQPage schema array for a set of FAQ IDs.
	 *
	 * @param int[] $faq_ids FAQ post IDs.
	 * @return array|null
	 */
	public function build( array $faq_ids ): ?array {
		$questions = array();
		foreach ( $faq_ids as $faq_id ) {
			$post = get_post( (int) $faq_id );
			if ( ! $post || 'publish' !== $post->post_status ) {
				continue;
			}

			$questions[] = array(
				'@type'          => 'Question',
				'name'           => (string) $post->post_title,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( (string) $post->post_content ),
				),
			);
		}

		if ( empty( $questions ) ) {
			return null;
		}

		return array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $questions,
		);
	}

	/**
	 * Render the JSON-LD script tag for a set of FAQ IDs.
	 *
	 * @param int[] $faq_ids FAQ post IDs.
	 * @return string
	 */
	public function render( array $faq_ids ): string {
		$schema = $this->build( $faq_ids );
		if ( null === $schema ) {
			return '';
		}

		$json = wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		if ( false === $json ) {
			return '';
		}

		return sprintf( '<script type="application/ld+json">%s</script>' . "\n", $json );
	}
}
