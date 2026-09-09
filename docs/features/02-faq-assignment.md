---
status: done
branch: feature/02-faq-assignment
---

## 02. FAQ assignment (product / category / tag / variation / global)

## Goal
Provide the assignment data model (postmeta-backed) and a resolver that determines which FAQs apply to a given product or variation — honoring global, direct-product, product-category, product-tag, and variation assignments. Clone wiring into the product-edit UI is deferred to feature 09 per plan.

## Acceptance criteria
- [x] Assignments stored as postmeta on the FAQ post (5 keys: global, product_ids, category_ids, tag_ids, variation_ids).
- [x] `FaqAssignments` value object with normalization + immutable `with_*` copies.
- [x] `FaqAssignmentInterface` (strategy interface) + `PostMetaFaqAssignment` implementation.
- [x] `FaqResolver` resolves applicable FAQ IDs for a product/variation.
- [x] Resolution honors global → direct product → variation → category → tag.
- [x] `wsfq_resolve_faqs()` global helper + container wiring.
- [x] Hooks: `wsfq_faq_assignments_loaded`, `wsfq_faq_assignments_saved`, `wsfq_faq_assignments_deleted`, `wsfq_resolve_faq_ids`.
- [x] Container passes the container into singleton factories (bug found + fixed + regression-tested).
- [x] Integration tests: postmeta round-trip, delete, resolver global+direct against real WP.

## Open questions
None — all resolved via design confirmation (postmeta storage, storage+resolver scope, clone deferred).

## Test plan
- Unit: FaqAssignments (4), PostMetaFaqAssignment (3), FaqResolver (7), Container regression (1).
- Integration: AssignmentTest (3) — real postmeta + resolver.
- E2E: covered by smoke test (no user-facing UI in this feature).

## Decisions made while building this
- Postmeta on FAQ post (plan Phase 7 default) — not product postmeta, not a custom table.
- snake_case naming enforced by WPCS `WordPress.NamingConventions` (renamed from camelCase).
- `$wpdb` not used (all postmeta via WP APIs).
- Container::singleton now forwards the container to factories (bug fix).