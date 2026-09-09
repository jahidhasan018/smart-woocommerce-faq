# WooCommerce Smart FAQ — Feature List

**Plugin name:** WooCommerce Smart FAQ
**Positioning:** Every feature competitors charge for, free — plus extras they don't offer at all.

**Code prefix convention:** `wsf_` (or `wsf-` for hyphenated slugs/handles) for everything that lives in code — shortcodes, custom post type slug (`wsf_faq`), taxonomy slugs (`wsf_faq_category`, `wsf_faq_group`), hooks/filters (`wsf_before_render`, `wsf_faq_saved`), PHP function/class names (`WSF_FAQ`, `wsf_get_faqs()`), REST API namespace (`wsf/v1`), DB option keys and table names, JS/CSS handles, and enqueued asset filenames.

---

## 1. Content & Data Model
- Central FAQ library (custom post type), reusable across the whole site
- FAQ categories/taxonomy (Shipping, Returns, Warranty, etc.)
- FAQ Groups — named collections with drag-and-drop order + dedicated shortcode
- Assign FAQs to: individual products, product categories, product tags, product variations, or globally (all products)
- Drag-and-drop sort ordering (library, per-product, per-group)
- Duplicate/clone an existing FAQ

## 2. Display Surfaces
- Product Tabs
- After Add to Cart button
- After Product Meta
- After Product Summary
- After Single Product
- Top or bottom of product page
- Shop / Archive pages
- Cart page
- Checkout page
- Standalone FAQ/help page (auto-inject via settings, or manual shortcode/block placement)
- Show/hide-answers-on-load toggle
- Custom tab order/label

## 3. Builder & Editor Integrations
- Gutenberg blocks: Prebuilt FAQs (library) + Custom FAQs (write inline)
- Elementor widget (same Prebuilt/Custom split)
- Divi 4 and Divi 5 modules
- Bricks Builder module *(competitors don't have this — differentiator)*
- WooCommerce Blocks (block-based Cart/Checkout) compatibility *(gap in both competitors)*
- Classic Editor and block editor support

## 4. Shortcodes
- `[wsf_faq_all]` — every FAQ
- `[wsf_faq_category id="x"]` — by FAQ category
- `[wsf_faq_ids ids="1,2,3"]` — specific FAQs
- `[wsf_faq_product id="x"]` — specific WooCommerce product
- `[wsf_faq_current]` — current product, dynamic
- `[wsf_faq_group id="x"]` — a named FAQ Group
- `[wsf_faq_search]` — AJAX search box

## 5. Styling & Layout
- 9+ accordion/layout styles
- Full color controls: heading, question, answer, active state, border
- Typography and spacing controls
- Custom chevron/icon styles
- Rounded card layout option
- RTL support
- Multi-column layout option
- Expand-all / collapse-all control

## 6. Search & Discovery
- Inline on-page filter search (filters what's already rendered)
- AJAX autocomplete search across the entire FAQ library
- AJAX search scoped to a single product's FAQs
- Hash deep-linking to a specific FAQ (e.g. `#wsf-faq-123`)

## 7. AI Features
- Multi-provider AI FAQ generation: OpenAI, Google Gemini, Claude (BYOK for all three)
- Adjustable copywriting tone: Sales/Objection-Buster, Friendly, Concise, Technical
- One-click objection-buster templates: Shipping & Delivery, Returns & Money-Back, Warranty & Durability, Sizing & Compatibility, Care & Maintenance
- Bulk AI generation across an entire product category
- Multi-model AI failover (auto-switch provider if one is down)
- AI auto-draft answers for customer-submitted questions
- **New:** AI-suggested FAQs mined from existing product reviews
- **New:** Retrieval-based FAQ chatbot widget on the product page (uses the same configured AI key)

## 8. Customer Engagement
- Customer Q&A submission form on product page
- Moderation workflow (approve/reject before publishing)
- Email notifications on new question / new answer
- Dynamic product attribute placeholders in answers (e.g. `{product_price}`, `{stock_status}`)

## 9. Analytics
- FAQ engagement dashboard: total views, read-completion rate, most-viewed FAQs
- Objection pattern tracking (which FAQ categories get viewed most before purchase)

## 10. SEO
- Google FAQPage JSON-LD schema, auto-generated on both product pages and standalone FAQ pages
- No dependency on a separate SEO plugin

## 11. Internationalization
- WPML support
- Polylang support
- Full translation-ready strings (`.pot` file)

## 12. Data Portability & Admin
- Import/export (JSON): FAQs, categories, groups, product assignments
- One-click migration importer: detect and convert data from "Product FAQ for WooCommerce" and "Happy FAQs" automatically
- Media support in answers: images, video, embedded HTML
- Comments on FAQs (optional toggle)
- Multisite network-wide FAQ library option

## 13. Developer Features *(new — not offered by either competitor)*
- REST API endpoints for FAQs (read/write), for headless/decoupled WooCommerce (Next.js, etc.)
- Documented action/filter hooks at every render and save point
- Template override support via child theme (`/wsf-templates/` folder convention)
- Composer/PSR-4 autoloading structure for easier extension by other devs

## 14. Compatibility & Maintenance
- Tested against latest WordPress and WooCommerce versions at every release
- Compatible with major caching plugins (output not cached incorrectly when FAQs update)
- Lightweight, no bloat, clean semantic markup

---

## Suggested Build Priority (if useful later)
1. Core CPT + taxonomy + product/category/tag/variation assignment
2. Display engine (hooks + shortcodes + basic accordion styles)
3. Gutenberg block + Elementor widget
4. Google schema output
5. AI generation (single provider first, then multi-provider + failover)
6. Search (inline, then AJAX)
7. Customer Q&A + moderation
8. Analytics dashboard
9. Import/export + migration tool
10. REST API + developer hooks
11. Divi/Bricks modules, WooCommerce Blocks compatibility, multisite, RTL polish