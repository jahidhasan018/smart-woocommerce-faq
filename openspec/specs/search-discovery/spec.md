# Search Discovery Specification
## Purpose

Defines the search and discovery behavior of the Smart WooCommerce FAQ plugin: inline filtering of rendered FAQs, AJAX-based autocomplete search across the FAQ library and within a single product, a reusable search shortcode, and hash-based deep-linking to individual FAQ items. These behaviors are planned capabilities the plugin commits to delivering for storefront faqs.

## Requirements

### Requirement: Inline on-page FAQ filter

The plugin SHALL provide inline search that filters the already-rendered FAQs on the current page without a page reload, keeping the page interactive while the visible FAQ list narrows to the questions whose text matches the filter input.

#### Scenario: Filtering rendered FAQs without reloading

- **WHEN** a visitor types a query into the inline FAQ filter box on a page that already displays FAQs
- **THEN** the plugin filters the rendered FAQ list on the page in place, showing only the FAQs whose question or answer text matches the query, without reloading the page

#### Scenario: No matches are shown as empty state

- **WHEN** a visitor enters a filter query that matches no FAQ on the page
- **THEN** the plugin shows an empty state indicating there are no matching FAQs instead of leaving the list unchanged

### Requirement: AJAX search across the entire FAQ library

The plugin SHALL provide an AJAX-only autocomplete search that queries across the entire FAQ library and returns matching FAQs to the visitor without reloading the page.

#### Scenario: Autocomplete results across all FAQs

- **WHEN** a visitor types at least the minimum search length into an AJAX search box that covers the whole FAQ library
- **THEN** the plugin sends the query to the server via AJAX and displays matching FAQs as autocomplete results without reloading the page

#### Scenario: Search limited by server permission checks

- **WHEN** an AJAX search request is submitted for the FAQ library
- **THEN** the plugin only returns results the requesting visitor is permitted to see, filtered according to the same rules that govern public FAQ visibility

### Requirement: AJAX search scoped to a single product

The plugin SHALL provide AJAX search that is scoped to the FAQs belonging to a single product, so a search box placed on a product page only returns that product's FAQs.

#### Scenario: Product-scoped search returns only that product

- **WHEN** a visitor uses an AJAX search box placed on a specific product's page
- **THEN** the plugin searches only that product's FAQs and returns only matches from that product's FAQ set

#### Scenario: No FAQs for the product

- **WHEN** a visitor searches on a product page that has no FAQs associated with it
- **THEN** the plugin indicates there are no matching FAQs for that product rather than returning FAQs from other products

### Requirement: Reusable FAQ search shortcode

The plugin SHALL provide a `[wsfq_faq_search]` shortcode that renders a search box anywhere a page author places it, and SHALL accept attributes to set the search scope, including whether it searches the whole library or a single product.

#### Scenario: Shortcode renders a search box

- **WHEN** a page author adds the `[wsfq_faq_search]` shortcode to a page
- **THEN** the plugin renders a functional search box in that location on the storefront

#### Scenario: Product scope via shortcode attribute

- **WHEN** a page author adds the `[wsfq_faq_search]` shortcode with a product scope attribute on a page
- **THEN** the plugin renders a search box whose results are scoped to the listed product's FAQs

### Requirement: Hash deep-linking to a specific FAQ

The plugin SHALL support hash deep-linking so that loading a page whose URL carries a FAQ anchor, such as `#wsfq-faq-123`, focuses and expands the targeted FAQ on that page.

#### Scenario: Loading a page with a FAQ hash focuses that FAQ

- **WHEN** a visitor loads a page whose URL contains a FAQ hash, such as `#wsfq-faq-123`
- **THEN** the plugin focuses on and expands the FAQ identified by that hash after the page loads

#### Scenario: Hash points to a non-existent FAQ

- **WHEN** a visitor loads a page with a FAQ hash that does not match any FAQ on that page
- **THEN** the plugin leaves the page unchanged and does not expand or focus any FAQ

