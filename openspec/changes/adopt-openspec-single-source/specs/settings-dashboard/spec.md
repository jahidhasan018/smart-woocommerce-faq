## Purpose

The settings dashboard capability provides a Gutenberg-native administration page at a dedicated URL that exposes a tabbed settings interface with a React shell, while persisting and validating all FAQ display settings through the WordPress Settings API and a shared REST route so the admin UI, CLI, and automation agree on one source of truth.

## ADDED Requirements

### Requirement: Administrative Settings Page

The system SHALL provide an administrative settings page at a dedicated admin URL built as a Gutenberg-native React shell, accessible to administrators from the plugin menu.

#### Scenario: Settings page opens at a dedicated URL

- **WHEN** an administrator opens the plugin settings menu item
- **THEN** the browser navigates to a dedicated admin URL that renders the Gutenberg-native settings page

#### Scenario: React shell renders the admin interface

- **WHEN** the settings page loads
- **THEN** a React application shell is rendered and becomes the interactive settings interface

#### Scenario: Page requires administrator capability

- **WHEN** a user without administrator rights attempts to access the settings page
- **THEN** access is denied and the settings page is not rendered

### Requirement: Tabbed Settings Navigation

The settings page SHALL present its options in tabs labeled General, AI Providers, Display, Design, and Advanced, where only the Display tab offers live settings and the remaining tabs render placeholder shells.

#### Scenario: All five tabs are visible

- **WHEN** the settings page renders
- **THEN** General, AI Providers, Display, Design, and Advanced tabs are shown for navigation

#### Scenario: Non-display tabs show placeholders

- **WHEN** an administrator selects the General, AI Providers, Design, or Advanced tab
- **THEN** the tab renders a placeholder shell with no active settings controls

#### Scenario: Display tab shows live settings

- **WHEN** an administrator selects the Display tab
- **THEN** live settings controls are rendered and persistable

### Requirement: Display Location Setting

The Display tab SHALL allow an administrator to select a single product-page location where FAQs appear, and the options SHALL be mutually exclusive so only one may be active at a time, including an option not to show FAQs on product pages.

#### Scenario: Exactly one product-page location selected

- **WHEN** an administrator selects a product-page location
- **THEN** that location is marked active and all other product-page locations are deselected

#### Scenario: Product-page FAQs can be disabled

- **WHEN** an administrator selects the option not to show FAQs on product pages
- **THEN** no product-page location is active and FAQs do not render on product pages

#### Scenario: Product-page choice persists

- **WHEN** the selected product-page location is saved
- **THEN** the saved setting reflects that single location on subsequent loads

### Requirement: Independent Page-Level Toggles

The Display tab SHALL provide independent toggles for the shop archive, cart, and checkout surfaces, each of which SHALL be enabled or disabled without affecting the others, alongside a toggle for expanding all FAQ items by default.

#### Scenario: Shop archive toggles independently

- **WHEN** an administrator enables the shop archive toggle
- **THEN** shop archive FAQs render regardless of the cart or checkout toggle state

#### Scenario: Cart and checkout toggle independently

- **WHEN** an administrator enables the cart or checkout toggle
- **THEN** that surface renders FAQs without changing the state of the other surface toggles

#### Scenario: Expand-all default is controllable

- **WHEN** an administrator toggles the expand-all default
- **THEN** the persisted setting reflects whether FAQ items open expanded by default

### Requirement: Settings Persistence

The system SHALL persist settings through the WordPress Settings API and SHALL expose them through a REST route so the admin UI, CLI, and automation share one validated, sanitized path.

#### Scenario: Saving persists through the Settings API

- **WHEN** an administrator saves the Display settings
- **THEN** the values are persisted through the WordPress Settings API and are available on a subsequent load

#### Scenario: REST route exposes the same settings

- **WHEN** the admin UI, CLI, or automation reads or writes settings through the REST route
- **THEN** all parties read and write the same validated and sanitized values

#### Scenario: Values are sanitized on registration

- **WHEN** settings are registered for persistence
- **THEN** they are processed through a sanitize callback before storage

### Requirement: Settings Service with Defaults and Merge

The system SHALL load and save settings through a single settings service that applies sensible defaults and merges incoming values onto existing settings on save.

#### Scenario: Defaults apply when nothing is saved

- **WHEN** settings are loaded before any value has been persisted
- **THEN** the service returns sensible default values for every setting

#### Scenario: Save merges onto existing settings

- **WHEN** a save request provides a subset of settings
- **THEN** the provided values are merged onto the existing settings and unsupplied settings remain unchanged

#### Scenario: Loaded values match saved values

- **WHEN** settings are loaded after a save
- **THEN** the loaded values match the values that were last persisted

### Requirement: Save State Feedback

The admin UI SHALL reflect the current save and load state as saving, saved, or error with an option to retry, and SHALL auto-save whenever a setting changes.

#### Scenario: Saving state is shown during persistence

- **WHEN** a setting change triggers auto-save and the save is in progress
- **THEN** the UI indicates the saving state and disables retrying until the save completes

#### Scenario: Saved state is shown on success

- **WHEN** an auto-save completes successfully
- **THEN** the UI indicates the saved state

#### Scenario: Error state offers retry

- **WHEN** an auto-save fails
- **THEN** the UI indicates an error and provides a retry control that resubmits the save

#### Scenario: Changes auto-save

- **WHEN** an administrator changes any Display setting
- **THEN** the change is saved automatically without requiring an explicit save action