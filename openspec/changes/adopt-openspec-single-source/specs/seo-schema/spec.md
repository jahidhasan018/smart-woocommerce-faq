## Purpose

Provide Google-compatible FAQPage JSON-LD structured data so resolved FAQs on product pages and standalone FAQ pages are eligible for search engine rich results without requiring a separate SEO plugin.

## ADDED Requirements

### Requirement: FAQPage schema on product pages

The plugin SHALL emit Google-compatible FAQPage JSON-LD structured data on product pages, but only for FAQs that are resolved.

#### Scenario: Resolved FAQ on a product page produces schema

- **WHEN** a WooCommerce product page is rendered and the product has at least one FAQ that is resolved
- **THEN** the page head contains a JSON-LD script tag carrying a FAQPage schema whose mainEntity contains one Question and Answer entry per resolved FAQ

#### Scenario: No resolved FAQ means no schema on product page

- **WHEN** a product page is rendered and the product has no resolved FAQs
- **THEN** no FAQPage schema is emitted for that page

### Requirement: FAQPage schema on standalone FAQ pages

The plugin SHALL emit Google-compatible FAQPage JSON-LD structured data on standalone FAQ pages.

#### Scenario: Standalone FAQ page produces schema

- **WHEN** a standalone FAQ page is rendered
- **THEN** the page head contains a JSON-LD script tag carrying a FAQPage schema whose mainEntity contains one Question and Answer entry per FAQ shown on the page

### Requirement: Independence from a separate SEO plugin

The plugin SHALL output its FAQ schema with no dependency on a separate SEO plugin being installed or active.

#### Scenario: Schema appears without an SEO plugin

- **WHEN** a product or standalone FAQ page is rendered and no separate SEO plugin is installed or active
- **THEN** the FAQPage schema is still emitted

### Requirement: Schema structure

The plugin SHALL emit the FAQPage type with a mainEntity property consisting of Question entries, each carrying an Answer entry derived from the FAQ post content.

#### Scenario: Question and answer entries are derived from posts

- **WHEN** a FAQPage schema is emitted
- **THEN** each mainEntity Question has a name matching the FAQ post title and an acceptedAnswer whose text is derived from the FAQ post answer

### Requirement: JSON-LD script tag in page head

The plugin SHALL output the FAQ schema in the page head as a JSON-LD script tag.

#### Scenario: Schema is output as a JSON-LD script tag

- **WHEN** a FAQPage schema is emitted
- **THEN** the output is a script tag of type application/ld+json placed within the page head

### Requirement: Per-context schema toggle

The plugin SHALL provide a filter that can turn schema output off per-context.

#### Scenario: Filter disables schema in a given context

- **WHEN** a supported context (product page or standalone FAQ page) is about to emit schema and the filter returns false for that context
- **THEN** no FAQPage schema is emitted for that context

#### Scenario: Filter enabled by default

- **WHEN** no filter has been applied for a given context
- **THEN** the schema is emitted for that context

### Requirement: HTML stripping from answers

The plugin SHALL strip HTML tags from answer text before emitting it in the schema.

#### Scenario: Answer text has tags removed

- **WHEN** an FAQ answer containing HTML tags is emitted inside the schema
- **THEN** the answer text contains no HTML tags