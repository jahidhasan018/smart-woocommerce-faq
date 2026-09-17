# Product Edit Faq Panel Specification
## Purpose

Define the product-edit FAQ panel of the WooCommerce Smart FAQ plugin: the metabox shown in the product editing screen where a merchant manages that product's FAQs, including manual inline editing, media-rich answers, comment toggling, and assignment management.

## Requirements

### Requirement: Product edit FAQ panel

The plugin SHALL render a FAQ panel within the WooCommerce product editing screen so that a merchant can manage the FAQs associated with that product in one place.

#### Scenario: Panel is present on the product edit screen

- **WHEN** a merchant opens the edit screen for a product in the WooCommerce admin
- **THEN** a FAQ panel is visible in that product editing area

#### Scenario: Panel reflects the current product's FAQs

- **WHEN** the product edit screen loads and the product has FAQs
- **THEN** the panel lists those FAQs for the current product

### Requirement: Manual FAQ editing

The plugin SHALL allow a merchant to add and edit FAQ entries manually and inline within the product-edit FAQ panel.

#### Scenario: Manually adding an FAQ entry

- **WHEN** a merchant adds a new FAQ entry in the panel and provides a question and answer
- **THEN** the FAQ entry is saved and appears in the panel for the product

#### Scenario: Editing an existing FAQ entry

- **WHEN** a merchant modifies the question or answer of an existing FAQ entry in the panel
- **THEN** the changes are saved and reflected in the panel

### Requirement: Reordering FAQs

The plugin SHALL allow a merchant to reorder the FAQs of a product by dragging and dropping entries within the panel.

#### Scenario: Reordering via drag and drop

- **WHEN** a merchant drags an FAQ entry to a different position in the panel and drops it
- **THEN** the display order of the FAQs is updated to match the new position

#### Scenario: Order persists after save

- **WHEN** a merchant reorders FAQ entries and then saves the product
- **THEN** the saved order matches the order shown in the panel

### Requirement: Media-rich answers

The plugin SHALL support answers that include media, including images, video, and embedded HTML.

#### Scenario: Answer with an image

- **WHEN** a merchant attaches an image inside an FAQ answer in the panel
- **THEN** the image is stored and rendered as part of the answer

#### Scenario: Answer with video

- **WHEN** a merchant embeds a video inside an FAQ answer in the panel
- **THEN** the video is stored and rendered as part of the answer

#### Scenario: Answer with embedded HTML

- **WHEN** a merchant inserts embedded HTML (for example, an iframe embed) inside an FAQ answer in the panel
- **THEN** the embedded HTML is stored and rendered as part of the answer

### Requirement: Comment toggle on FAQs

The plugin SHALL provide an optional per-FAQ toggle that enables or disables comments on that FAQ.

#### Scenario: Comments enabled for an FAQ

- **WHEN** a merchant enables the comment toggle for an FAQ entry in the panel
- **THEN** comments are permitted for that FAQ entry

#### Scenario: Comments disabled for an FAQ

- **WHEN** a merchant disables the comment toggle for an FAQ entry in the panel
- **THEN** comments are not permitted for that FAQ entry

### Requirement: Assignment management from the panel

The plugin SHALL allow a merchant to manage FAQ assignments, by product, category, tag, variation, or global, directly from the product-edit FAQ panel.

#### Scenario: Assigning an FAQ globally

- **WHEN** a merchant assigns an FAQ globally from the panel
- **THEN** that FAQ applies to all products

#### Scenario: Assigning an FAQ to a category or tag

- **WHEN** a merchant assigns an FAQ to a product category or tag from the panel
- **THEN** that FAQ applies to products in that category or tag

#### Scenario: Assigning an FAQ to a variation

- **WHEN** a merchant assigns an FAQ to a product variation from the panel
- **THEN** that FAQ applies to that variation

#### Scenario: Assignments persist after save

- **WHEN** a merchant sets FAQ assignments in the panel and then saves the product
- **THEN** the assignments are persisted and reflected in the resolution of applicable FAQs for the product

