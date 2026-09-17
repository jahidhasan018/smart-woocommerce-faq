<?php
/**
 * FAQ post type registrar.
 *
 * Registers the wsfq_faq custom post type. Content + assignments live as
 * CPT + postmeta (see openspec/DECISIONS.md ADR-001).
 *
 * @package WSFQ
 */

declare(strict_types=1);

namespace WSFQ\PostTypes;

/**
 * Registers the FAQ custom post type.
 */
final class FaqPostType {

	public const SLUG = 'wsfq_faq';

	/**
	 * Register the post type (hooked on init).
	 */
	public static function register(): void {
		register_post_type(
			self::SLUG,
			array(
				'labels'       => array(
					'name'          => __( 'FAQs', 'smart-woocommerce-faq' ),
					'singular_name' => __( 'FAQ', 'smart-woocommerce-faq' ),
					'add_new_item'  => __( 'Add New FAQ', 'smart-woocommerce-faq' ),
					'edit_item'     => __( 'Edit FAQ', 'smart-woocommerce-faq' ),
					'new_item'      => __( 'New FAQ', 'smart-woocommerce-faq' ),
					'view_item'     => __( 'View FAQ', 'smart-woocommerce-faq' ),
					'search_items'  => __( 'Search FAQs', 'smart-woocommerce-faq' ),
					'not_found'     => __( 'No FAQs found', 'smart-woocommerce-faq' ),
					'menu_name'     => __( 'FAQs', 'smart-woocommerce-faq' ),
				),
				'public'       => true,
				'show_in_menu' => true,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'revisions' ),
				'menu_icon'    => 'dashicons-editor-help',
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'faq' ),
			)
		);
	}
}
