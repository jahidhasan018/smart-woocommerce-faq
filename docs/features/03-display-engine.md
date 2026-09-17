---
status: done
branch: feature/03-display-engine
---

## 03. Display engine: hooks for all product/shop/cart/checkout positions

## Goal
Render FAQs on WooCommerce surfaces via a strategy-based renderer. Reads enabled positions from the settings service (`wsfq_settings`, managed by the Settings → Display tab) and registers hooks for product tab, after add-to-cart, after product meta, after product summary, after single product, shop archive, cart, and checkout.

## Acceptance criteria
- [x] `RendererInterface` (strategy) + default `AccordionRenderer` emitting semantic, escaped HTML with `wsfq-` classes.
- [x] Renderer hooks: `wsfq_before_render`, `wsfq_after_render`, `wsfq_faq_item_title`, `wsfq_faq_item_content`, `wsfq_renderer`.
- [x] `DisplayEngine` registers positions from the saved settings.
- [x] All 8 positions wired: product_tab, after_add_to_cart, after_product_meta, after_product_summary, after_single_product, shop_archive, cart, checkout.
- [x] Product FAQ tab added via `woocommerce_product_tabs` filter.
- [x] `FaqResolverInterface` extracted (DisplayEngine depends on interface, not concrete).
- [x] Engine wired into the container + registered on `wp`.
- [x] Integration tests: render_for_product output, empty case, product tab (WC-gated).
- [x] PHPStan: WooCommerce symbols resolved via a lightweight `phpstan-wc.php` stub (full WC stub exceeded memory).

## Open questions
None — resolved via design confirmation (renderer + all positions + stored option).

## Test plan
- Unit: AccordionRenderer (3), DisplayEngine (4).
- Integration: DisplayEngineTest (3) — render output + empty; product tab WC-gated.
- E2E: smoke test.

## Decisions made while building this
- Renderer is a strategy interface; AccordionRenderer is the default. Styling + aria toggles deferred to feature 05.
- Positions and the expand-all control read from `SettingsService` (the `wsfq_settings` option), so the Settings → Display tab drives the storefront. The `wsfq_display_positions` filter is still applied on top for developers.
- The five product-page positions are mutually exclusive and only one may be enabled; `SettingsService` enforces this on read and on save. Page-level positions (shop_archive, cart, checkout) are independent.
- Full `php-stubs/woocommerce-stubs` exceeds PHPStan memory (4.5MB/145k lines) — replaced with a minimal `phpstan-wc.php` stub; PHPStan memory raised to 2G.
- `FaqResolverInterface` added for testability (Mockery can't mock final classes).