## Purpose

Provide WordPress shortcodes that render FAQ entries on the front end, allowing site owners and developers to embed all FAQs, a filtered set, or product-specific FAQs into posts, pages, and templates without writing PHP. Shortcodes resolve their FAQ set through a shared rendering pipeline that honors override filters, and each callback returns a string so output is never echoed directly.

## ADDED Requirements

### Requirement: Register shortcodes on init

The plugin MUST register every supported shortcode on the WordPress `init` hook so the shortcodes are available throughout the front end and the admin editor.

#### Scenario: Shortcodes are registered and callable

- **WHEN** WordPress fires the `init` hook
- **THEN** the plugin registers the FAQ shortcodes through its central container
- **AND** each registered shortcode is callable in post content

### Requirement: Render all published FAQs

The `[wsfq_faq_all]` shortcode MUST render every published FAQ in the site, in their default order.

#### Scenario: Every published FAQ renders

- **WHEN** a post contains `[wsfq_faq_all]`
- **THEN** every published FAQ is rendered in the output
- **AND** unpublished or trashed FAQs are not rendered

### Requirement: Render FAQs by category

The `[wsfq_faq_category id="x"]` shortcode MUST render the FAQs that belong to the FAQ-category term identified by the given ID.

#### Scenario: Only category FAQs render

- **WHEN** a post contains `[wsfq_faq_category id="5"]`
- **THEN** only FAQs assigned to the FAQ-category term with ID 5 are rendered
- **AND** FAQs not assigned to that term are not rendered

### Requirement: Render FAQs by IDs

The `[wsfq_faq_ids ids="1,2,3"]` shortcode MUST render exactly the FAQs whose IDs are provided, in the order given.

#### Scenario: Only the given IDs render in order

- **WHEN** a post contains `[wsfq_faq_ids ids="1,2,3"]`
- **THEN** the FAQs with IDs 1, 2, and 3 are rendered in that order
- **AND** any ID that does not correspond to a published FAQ is skipped

### Requirement: Render FAQs by product

The `[wsfq_faq_product id="x"]` shortcode MUST render the FAQs assigned to the product identified by the given ID.

#### Scenario: Only the product's FAQs render

- **WHEN** a post contains `[wsfq_faq_product id="42"]`
- **THEN** only FAQs assigned to the product with ID 42 are rendered
- **AND** FAQs assigned to other products are not rendered

### Requirement: Render FAQs for the current product

The `[wsfq_faq_current]` shortcode MUST render the FAQs assigned to the product of the currently viewed page.

#### Scenario: Current product FAQs render

- **WHEN** a visitor views a product page and the product content contains `[wsfq_faq_current]`
- **THEN** the FAQs assigned to that current product are rendered
- **AND** when there is no current product, nothing is rendered

### Requirement: Render FAQs by group

The `[wsfq_faq_group id="x"]` shortcode MUST render the FAQs that belong to the FAQ-group term identified by the given ID.

#### Scenario: Only group FAQs render

- **WHEN** a post contains `[wsfq_faq_group id="9"]`
- **THEN** only FAQs assigned to the FAQ-group term with ID 9 are rendered
- **AND** FAQs not assigned to that term are not rendered

### Requirement: Render through the shared renderer

All shortcodes MUST resolve and render their FAQ set through the shared rendering pipeline, which honors an override filter so a site can replace the default output.

#### Scenario: Output routes through the shared renderer

- **WHEN** any FAQ shortcode resolves its output
- **THEN** it routes through the shared renderer
- **AND** when a site hooks the override filter, the filtered output is returned instead of the default
- **AND** the override filter applies consistently across every shortcode

### Requirement: Filter the resolved FAQ IDs

The plugin MUST expose a filter through which a site can modify the set of FAQ IDs resolved for a shortcode before rendering.

#### Scenario: Filtered IDs are rendered

- **WHEN** a shortcode resolves its FAQ set
- **THEN** a site may hook the filter to add, remove, or reorder the resolved FAQ IDs
- **AND** the rendered output reflects the filtered set

### Requirement: Return strings from callbacks

Every shortcode callback MUST return its rendered output as a string and MUST NOT echo output directly.

#### Scenario: Callback returns a string

- **WHEN** a shortcode callback produces its output
- **THEN** it returns the string to WordPress
- **AND** it does not echo or print the output itself
- **AND** the returned string is what appears in the rendered page

### Requirement: Do not register the search shortcode

The `[wsfq_faq_search]` shortcode MUST NOT be registered by the shortcodes capability, because AJAX search is handled by the search capability instead.

#### Scenario: Search shortcode has no effect

- **WHEN** the plugin registers its shortcodes on `init`
- **THEN** `[wsfq_faq_search]` is not registered
- **AND** using `[wsfq_faq_search]` in content has no shortcode effect