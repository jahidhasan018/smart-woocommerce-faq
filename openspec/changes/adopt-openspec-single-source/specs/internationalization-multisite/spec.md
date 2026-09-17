# internationalization-multisite

## Purpose

This capability ensures the WooCommerce Smart FAQ plugin is fully translatable and works correctly in multilingual and network (multisite) environments, covering WPML, Polylang, translation-ready strings, and network-level settings with an optional shared FAQ library.

## ADDED Requirements

### Requirement: WPML support

The plugin SHALL integrate with the WPML multilingual plugin so that FAQ content and plugin strings render in the site's active languages. FAQ titles and answers SHALL be translatable and presented in the visitor's current language, and translations SHALL follow the WordPress locale that WPML selects.

#### Scenario: Translated FAQ rendered in selected language

- **WHEN** the site runs WPML with multiple languages configured and a FAQ item has a published translation
- **THEN** the FAQ is rendered in the language active for the current visitor

#### Scenario: Missing translation falls back

- **WHEN** a FAQ item has no translation in the visitor's selected language
- **THEN** the plugin displays the fallback language version without error

### Requirement: Polylang support

The plugin SHALL integrate with the Polylang multilingual plugin so that FAQ content and plugin strings work across configured languages. FAQ titles and answers SHALL be translatable and presented according to the active language, following the locale Polylang selects for the request.

#### Scenario: Polylang translated FAQ rendered

- **WHEN** the site runs Polylang with multiple languages and a FAQ item has a translation in the active language
- **THEN** the FAQ is rendered in that active language

#### Scenario: Polylang missing translation fallback

- **WHEN** a FAQ item lacks a translation for the active language
- **THEN** the plugin presents the default language content without error

### Requirement: Translation-ready strings with a .pot file

The plugin SHALL ship a translation template (.pot) file covering all user-facing strings. Every string visible to site visitors or administrators SHALL be wrapped for translation and included in the template so the plugin can be translated into any locale.

#### Scenario: Shipping translation template

- **WHEN** the plugin package is inspected
- **THEN** a .pot file is present containing every user-facing string of the plugin

#### Scenario: Localizing into another language

- **WHEN** a translator creates a locale file from the shipped .pot template
- **THEN** the translated strings appear in the plugin for that locale with no untranslated escapes

### Requirement: Network-level plugin settings

The plugin SHALL make its settings configurable at the network level on WordPress multisite. The network administrator SHALL be able to set plugin options that apply across all sites in the network, overriding individual site defaults.

#### Scenario: Configuring settings for the network

- **WHEN** a network administrator edits the plugin's settings in the network admin
- **THEN** those settings apply to all sites in the network unless a site overrides them

#### Scenario: Site-level override

- **WHEN** a site within the network has its own plugin setting configured
- **THEN** that site uses its local value instead of the network default

### Requirement: Network-wide shared FAQ library option

The plugin SHALL offer a network option that shares one FAQ library across all sites in the network. When enabled, sites SHALL read FAQs from the shared library instead of maintaining independent libraries.

#### Scenario: Shared library available across sites

- **WHEN** the network-wide shared FAQ library option is enabled
- **THEN** the same FAQ items are available on every site in the network

#### Scenario: Shared library option disabled

- **WHEN** the shared library option is disabled
- **THEN** each site maintains and displays its own independent FAQ library