# Phase 5 — Product-Edit Screen: FAQ Panel + AI Generation


- A React panel (same `@wordpress/components` toolkit) mounted into the WooCommerce Product Data metabox area via `woocommerce_product_data_panels` / `woocommerce_product_data_tabs`.
- Manual mode: add/edit/reorder FAQs inline (drag-and-drop via `@wordpress/components`' built-in sortable primitives or a small dependency-free implementation).
- AI mode: "Generate with AI" button → tone selector → objection-buster template dropdown → calls `wsf/v1/ai/generate` REST endpoint → renders editable preview cards → "Insert" commits selected FAQs to the product.
- All AI calls happen **server-side** (PHP → provider API) via `wp_remote_post`, never client-side — so API keys never touch the browser.
- Every AI-generated FAQ is clearly marked as such until a human edits/approves it (protects data quality and gives you an analytics signal later: AI-accepted vs AI-rejected suggestions).
- Manual-mode answers support media — images, video, and embedded HTML — via the core media picker; AI-generated answers stay plain-text until a human inserts media.

---

