<?php
/**
 * FAQ category taxonomy registrar.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Taxonomies;

/**
 * Registers the FAQ category taxonomy.
 */
final class FaqCategory {

	public const SLUG = 'wsfq_faq_category';

	/**
	 * Register the taxonomy (hooked on init).
	 */
	public static function register(): void {
		register_taxonomy(
			self::SLUG,
			array( \WSFQ\PostTypes\FaqPostType::SLUG ),
			array(
				'labels'            => array(
					'name'          => __( 'FAQ Categories', 'smart-woocommerce-faq' ),
					'singular_name' => __( 'FAQ Category', 'smart-woocommerce-faq' ),
					'add_new_item'  => __( 'Add New FAQ Category', 'smart-woocommerce-faq' ),
					'edit_item'     => __( 'Edit FAQ Category', 'smart-woocommerce-faq' ),
					'search_items'  => __( 'Search FAQ Categories', 'smart-woocommerce-faq' ),
					'not_found'     => __( 'No FAQ Categories found', 'smart-woocommerce-faq' ),
					'menu_name'     => __( 'FAQ Categories', 'smart-woocommerce-faq' ),
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'faq-category' ),
			)
		);
	}
}
