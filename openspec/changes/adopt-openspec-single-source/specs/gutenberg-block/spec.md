# gutenberg-block

## Purpose

Define the observable behavior of the `wsfq/faq` Gutenberg block in Prebuilt mode, which lets editors insert a server-rendered, accessible FAQ accordion on any post or page using FAQs selected from the plugin's FAQ library.

## ADDED Requirements

### Requirement: Select FAQs in Prebuilt mode

The `wsfq/faq` block SHALL let an editor choose one or more FAQs from the plugin's FAQ library for insertion in Prebuilt mode.

#### Scenario: Editor selects a single FAQ

- **WHEN** an editor inserts the `wsfq/faq` block onto a page and selects a single FAQ from the FAQ library in Prebuilt mode
- **THEN** the selected FAQ's question and answer are rendered as an accessible accordion item on that page

#### Scenario: Editor selects multiple FAQs

- **WHEN** an editor selects multiple FAQs from the FAQ library in Prebuilt mode
- **THEN** each selected FAQ is rendered as its own accessible accordion item in the order chosen by the editor

### Requirement: Server-side dynamic rendering

The block SHALL render its selected FAQs dynamically on the server, consistent with the shortcode output so that changes to the underlying FAQ library propagate automatically.

#### Scenario: Library change propagates automatically

- **WHEN** a FAQ selected by the block is edited in the FAQ library
- **THEN** the block output on the frontend reflects the updated content without any editor re-insertion

#### Scenario: Consistent shortcode output

- **WHEN** the same FAQs are rendered by both the `wsfq/faq` block and the equivalent shortcode
- **THEN** the block renders the FAQs using the same shared renderer and their frontend output is consistent

### Requirement: Accessible accordion rendering

The block SHALL render the selected FAQs using the shared renderer as an accessible accordion.

#### Scenario: Keyboard operable accordion

- **WHEN** a page renders the block and a visitor tabs through the accordion
- **THEN** each FAQ item can be opened and closed using the keyboard and the expanded state is exposed to assistive technology

### Requirement: Sanitize selected FAQ IDs

The block SHALL sanitize the selected FAQ IDs before rendering.

#### Scenario: Invalid ID is not rendered

- **WHEN** a stored block contains a non-numeric or otherwise invalid FAQ ID
- **THEN** that ID is discarded and no corresponding accordion item is rendered

### Requirement: Insertable on posts and pages

The block SHALL be insertable on any post or page, including product pages.

#### Scenario: Inserted on a standard page

- **WHEN** an editor adds the block to a standard WordPress page
- **THEN** the block is available in the editor and renders normally on the frontend

#### Scenario: Inserted on a product page

- **WHEN** an editor adds the block to a product page and selects FAQs configured for that product
- **THEN** the selected FAQs render as an accessible accordion on the product page

### Requirement: Registered on init without duplication

The block SHALL be registered on the WordPress `init` hook and SHALL be guarded against double-registration.

#### Scenario: Single registration during init

- **WHEN** WordPress triggers the `init` hook during a request
- **THEN** the block is registered exactly once and no duplicate-registration error is raised