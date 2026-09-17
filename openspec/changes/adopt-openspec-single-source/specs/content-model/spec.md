## Purpose

Define the content model and assignment behavior of the WooCommerce Smart FAQ plugin: how FAQ entries are stored, categorized, grouped, and matched to products and variations.

## ADDED Requirements

### Requirement: FAQ custom post type

The plugin SHALL register a `wsfq_faq` custom post type that is public, REST-enabled, and uses the rewrite slug `faq`.

#### Scenario: FAQ post type is registered and queryable

- **WHEN** the plugin is loaded during the WordPress `init` hook
- **THEN** a `wsfq_faq` post type is registered as public and REST-enabled with rewrite slug `faq`

### Requirement: FAQ category and group taxonomies

The plugin SHALL register a hierarchical `wsfq_faq_category` taxonomy and a hierarchical `wsfq_faq_group` taxonomy, both tied to the `wsfq_faq` post type. Registrars SHALL be hooked on the `init` action via the plugin bootstrap.

#### Scenario: Taxonomies are tied to the FAQ post type

- **WHEN** the plugin bootstrap runs during the `init` action
- **THEN** the `wsfq_faq_category` and `wsfq_faq_group` taxonomies are registered as hierarchical and associated with the `wsfq_faq` post type

#### Scenario: Categories and groups are assignable to FAQ entries

- **WHEN** an editor assigns a category and a group to a `wsfq_faq` entry
- **THEN** that entry is queryable by both taxonomy terms

### Requirement: Activation registers content types and flushes rewrites

Plugin activation SHALL register the custom post type and taxonomies and flush rewrite rules.

#### Scenario: Activation sets up content types and rewrite rules

- **WHEN** the plugin is activated
- **THEN** the custom post type and taxonomies are registered and rewrite rules are flushed so the `faq` slug resolves

### Requirement: Assignment storage on FAQ post

Assignments SHALL be stored as postmeta on the FAQ post using five keys: `global`, `product_ids`, `category_ids`, `tag_ids`, and `variation_ids`.

#### Scenario: Assignment round-trips through postmeta

- **WHEN** values are saved to the five assignment keys on a `wsfq_faq` post
- **THEN** reading those keys returns the same values stored

#### Scenario: Assignment is deleted with the post meta

- **WHEN** the assignment postmeta for a `wsfq_faq` post is deleted
- **THEN** the assignment keys no longer return stored values

### Requirement: Assignment resolution against products and variations

A resolver SHALL determine which FAQs apply to a given product or variation, honoring global, direct product, variation, category, and tag assignments.

#### Scenario: Global assignment applies to a product

- **WHEN** an FAQ is globally assigned and a resolver runs for any product
- **THEN** that FAQ is included in the resolved applicable FAQ ids

#### Scenario: Direct product assignment applies to a product

- **WHEN** an FAQ is directly assigned to a product and a resolver runs for that product
- **THEN** that FAQ is included in the resolved applicable FAQ ids

#### Scenario: Variation assignment applies to a product variation

- **WHEN** an FAQ is assigned to a product variation and a resolver runs for that variation
- **THEN** that FAQ is included in the resolved applicable FAQ ids

#### Scenario: Category and tag assignments apply to matching products

- **WHEN** an FAQ is assigned to a product category or tag that the target product belongs to and a resolver runs for that product
- **THEN** that FAQ is included in the resolved applicable FAQ ids

### Requirement: Global resolution helper

The plugin SHALL provide a global helper that resolves the applicable FAQ ids for a given product or variation.

#### Scenario: Helper returns applicable FAQ ids

- **WHEN** the global helper is invoked for a product or variation
- **THEN** it returns the set of applicable FAQ ids resolved for that product or variation

### Requirement: Hooks around assignment and resolution

The plugin SHALL fire hooks when assignments are loaded, saved, or deleted and when FAQ ids are resolved.

#### Scenario: Hook fires when an assignment is saved or deleted

- **WHEN** an assignment is saved or deleted on a `wsfq_faq` post
- **THEN** a corresponding hook fires carrying the affected assignment context

#### Scenario: Hook fires when FAQ ids are resolved

- **WHEN** a resolver computes applicable FAQ ids for a product or variation
- **THEN** a hook fires carrying the resolved FAQ ids so they can be observed or modified