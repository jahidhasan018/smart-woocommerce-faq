---
status: done
branch: feature/07-faqpage-jsonld-schema
---

## 07. Google FAQPage JSON-LD schema

## Goal
Emit Google-compatible FAQPage JSON-LD structured data on product pages (resolved FAQs) and standalone FAQ pages, with no dependency on a separate SEO plugin.

## Acceptance criteria
- [x] `SchemaGenerator` builds the FAQPage graph (`@context`, `@type`, `mainEntity` of Question/Answer) from FAQ posts.
- [x] `SchemaGenerator::render()` wraps the schema in a `<script type="application/ld+json">` tag via `wp_json_encode`.
- [x] `SchemaOutput` hooks `wp_head`: product pages (resolved FAQs) + `is_singular(wsfq_faq)` pages.
- [x] `wsfq_schema_output` filter to toggle output; `wsfq_schema_data`/`wsfq_schema_faq_ids` filters documented.
- [x] Answers stripped of tags via `wp_strip_all_tags`.
- [x] Integration tests: schema builds from a real post; product page emits schema (WC-gated).
- [x] Live-verified: product page curl contains `application/ld+json` + `FAQPage`.

## Open questions
None.

## Test plan
- Unit: SchemaGenerator (4) — build, empty, render; SchemaOutput (2) — product emits, non-product empty.
- Integration: SchemaTest (2) — real post build, product page output (WC-gated).
- e2e: smoke (schema is head-only, not visible).

## Decisions made while building this
- Schema emitted server-side in `wp_head`, no JS/SEO-plugin dependency.
- `is_product()` resolved via the `phpstan-wc.php` stub for PHPStan.
- `wsfq_schema_output` filter allows disabling schema per-context.