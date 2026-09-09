<?php
/**
 * FAQ group taxonomy registrar.
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\Taxonomies;

/**
 * Registers the FAQ group taxonomy.
 */
final class FaqGroup {

	public const SLUG = 'wsfq_faq_group';

	/**
	 * Register the taxonomy (hooked on init).
	 */
	public static function register(): void {
		register_taxonomy(
			self::SLUG,
			array( \WSFQ\PostTypes\FaqPostType::SLUG ),
			array(
				'labels'            => array(
					'name'          => __( 'FAQ Groups', 'smart-woocommerce-faq' ),
					'singular_name' => __( 'FAQ Group', 'smart-woocommerce-faq' ),
					'add_new_item'  => __( 'Add New FAQ Group', 'smart-woocommerce-faq' ),
					'edit_item'     => __( 'Edit FAQ Group', 'smart-woocommerce-faq' ),
					'search_items'  => __( 'Search FAQ Groups', 'smart-woocommerce-faq' ),
					'not_found'     => __( 'No FAQ Groups found', 'smart-woocommerce-faq' ),
					'menu_name'     => __( 'FAQ Groups', 'smart-woocommerce-faq' ),
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'faq-group' ),
			)
		);
	}
}
