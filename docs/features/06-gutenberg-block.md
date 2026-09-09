---
status: done
branch: feature/06-gutenberg-block
---

## 06. Gutenberg block (Prebuilt)

## Goal
Ship a dynamic Gutenberg block (`wsfq/faq`) in Prebuilt mode: the editor picks FAQ IDs from the library; the server renders them via the display engine's renderer. Custom (inline) mode is deferred per user decision.

## Acceptance criteria
- [x] `blocks/faq/block.json` — apiVersion 3, `wsfq/faq`, `faqIds` attribute, `editorScript` handle, render callback.
- [x] Editor JS (`assets/src/blocks/faq/index.js`) — registers the block, FAQ-picker via `FormTokenField` + `getEntityRecords`, `save() => null`.
- [x] `FaqBlock` PHP class — registers the block with `render_callback`, sanitizes `faqIds`, renders via `RendererInterface`.
- [x] Registered idempotently on `init` (guarded against double-registration).
- [x] Build pipeline: webpack entry `faq-block` → `assets/build/faq-block.js` + manifest.
- [x] Editor script deps include wp-blocks/components/data/i18n.
- [x] Integration tests: block registered + renders FAQs.
- [x] e2e + lint clean.

## Open questions
None — Prebuilt-only confirmed; Custom deferred.

## Test plan
- Unit: FaqBlock (4) — register, render, sanitize, empty.
- Integration: FaqBlockTest (2) — registered, renders FAQ content.
- e2e: existing accordion + smoke.

## Decisions made while building this
- One block, Prebuilt mode only (Custom deferred per user).
- Dynamic block (server-rendered) so frontend output stays consistent with shortcodes and library changes propagate.
- Block metadata lives in `blocks/faq/` (committed, ships in zip); editor JS source in `assets/src/blocks/faq/` compiles to `assets/build/`.
- `render_callback` used (not `render` file) so the container-injected renderer is available.
- `WP_Block` stub added to the unit-test bootstrap so the type-hinted render() is unit-testable.