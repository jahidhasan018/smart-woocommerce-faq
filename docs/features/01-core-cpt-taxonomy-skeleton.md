---
status: done
branch: feature/01-core-cpt-taxonomy-skeleton
---

## 01. Core CPT/taxonomy skeleton

## Goal
Provide the content model foundation: a `wsfq_faq` custom post type plus `wsfq_faq_category` and `wsfq_faq_group` taxonomies, registered and testable from day one. Built initially in Phase 2's walking skeleton; this feature formalizes and verifies it.

## Acceptance criteria
- [x] `wsfq_faq` CPT registered, public + REST-enabled, `faq` rewrite slug.
- [x] `wsfq_faq_category` taxonomy registered, hierarchical, tied to `wsfq_faq`.
- [x] `wsfq_faq_group` taxonomy registered, hierarchical, tied to `wsfq_faq`.
- [x] Registrars hooked on `init` via the plugin bootstrap.
- [x] Activation registers the CPT/taxonomies and flushes rewrite rules.
- [x] Integration tests assert post_type_exists/taxonomy_exists and object-type linkage.
- [x] No meta fields on the CPT yet (assignments come in feature 02).

## Open questions
None — resolved during Phase 2.

## Test plan
- Unit: PluginTest asserts init hooks register the three registrars.
- Integration: ContentTypeTest asserts `post_type_exists`, `taxonomy_exists`, and `get_taxonomy()->object_type` linkage.
- E2E: covered by the general smoke test (no user-facing UI in this feature).

## Decisions made while building this
- CPT + taxonomies use static `register()` methods (idiomatic WP registrar pattern; no multi-strategy, so no interface needed).
- Rewrite slugs: `faq`, `faq-category`, `faq-group`.
- Labels are translation-ready via `__()` with the `smart-woocommerce-faq` text domain.