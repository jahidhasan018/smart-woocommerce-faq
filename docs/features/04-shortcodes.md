---
status: done
branch: feature/04-shortcodes
---

## 04. Shortcodes

## Goal
Register the `wsfq_faq_*` shortcodes (per feature-list §4) and render them through the display engine's renderer. Search shortcode deferred to feature 13/14.

## Acceptance criteria
- [x] `[wsfq_faq_all]` — every published FAQ.
- [x] `[wsfq_faq_category id="x"]` — FAQs in a FAQ-category term.
- [x] `[wsfq_faq_ids ids="1,2,3"]` — specific FAQ IDs.
- [x] `[wsfq_faq_product id="x"]` — FAQs assigned to a specific product (via resolver).
- [x] `[wsfq_faq_current]` — FAQs for the current product.
- [x] `[wsfq_faq_group id="x"]` — FAQs in a FAQ-group term.
- [x] `[wsfq_faq_search]` intentionally NOT included (deferred to feature 13/14).
- [x] All shortcodes render through RendererInterface (respects `wsfq_renderer`).
- [x] `wsfq_shortcode_faq_ids` filter on resolved IDs.
- [x] Registered on `init` via the container.
- [x] Integration tests for all, ids, category, product shortcodes.

## Open questions
None — resolved via design confirmation (wsfq_faq_* names, 6 shortcodes, search deferred).

## Test plan
- Unit: ShortcodeRegistry (6) — registration + each handler + empty cases.
- Integration: ShortcodeTest (4) — real WP rendering for all/ids/category/product.
- E2E: smoke test.

## Decisions made while building this
- Shortcode names use the full `wsfq_faq_*` convention (feature-list §4), not the plan table's abbreviated `wsfq_*`.
- FAQ ID queries use `get_posts()` (handles both object and `fields => ids` int returns) rather than raw `WP_Query` in the class, for testability.
- `wsfq_faq_search` deferred — AJAX search belongs with features 13/14.
- Shortcode callbacks return strings (never echo) — WordPress convention.