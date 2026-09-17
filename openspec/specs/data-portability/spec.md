# Data Portability Specification
## Purpose

Let site owners move FAQ data in and out of the plugin through a portable JSON format, and switch from competitor plugins without losing content through automated migration.

## Requirements

### Requirement: JSON export of full FAQ content model

The plugin SHALL export FAQs, categories, groups, and product assignments into a single JSON file.

#### Scenario: Export includes all content-model entities

- **WHEN** an export is triggered
- **THEN** the resulting JSON document contains entries for FAQs, categories, groups, and product assignments that exist at export time

#### Scenario: Product assignments reference products

- **WHEN** an FAQ is assigned to one or more products
- **THEN** the export records those product assignments in a way that survives re-import into another site

### Requirement: Portable re-importable export file

The plugin SHALL produce an export file that can be imported back into the same or another installation of the plugin without data loss.

#### Scenario: Export file imports back cleanly

- **WHEN** a JSON file previously produced by the plugin is imported into an installation
- **THEN** the FAQs, categories, groups, and product assignments captured in the file are recreated and produce the same rendered behavior as on the exporting site

#### Scenario: Round-trip preserves relationships

- **WHEN** a JSON file is exported and then imported
- **THEN** category and group membership and product assignments for each FAQ are preserved as they were at export time

### Requirement: Validation before import applies changes

The plugin SHALL validate the structure of an import file before applying any changes, and SHALL surface failures to the operator rather than silently dropping invalid content.

#### Scenario: Malformed import file is rejected

- **WHEN** an import file is malformed or fails structural validation
- **THEN** no writes occur and the operator is shown a clear error describing the failure

#### Scenario: Partial structural failure is reported

- **WHEN** an import file passes overall validation but contains entries that cannot be applied
- **THEN** the conflicting entries are reported to the operator and valid entries are still applied without silent loss

### Requirement: One-click migration from competitor plugins

The plugin SHALL detect data and convert it from the "Product FAQ for WooCommerce" and "Happy FAQs" plugins when the site owner starts a migration.

#### Scenario: Detected competitor data is converted automatically

- **WHEN** the site owner triggers a migration and data from "Product FAQ for WooCommerce" or "Happy FAQs" is detected
- **THEN** the detected FAQs, categories, groups, and product assignments are converted into native plugin data without manual re-entry

#### Scenario: No competitor data found

- **WHEN** the site owner triggers a migration and no data from either supported competitor plugin is detected
- **THEN** the migration reports that no supported competitor data was found and no changes are made

### Requirement: Background execution for large imports

The plugin SHALL run imports and migrations as background jobs so large catalogs do not time out a single PHP request, reusing Action Scheduler when available.

#### Scenario: Import job completes in the background

- **WHEN** an import or migration is started
- **THEN** the processing runs as a scheduled background job rather than blocking the initiating request

#### Scenario: Action Scheduler reused when available

- **WHEN** Action Scheduler is available on the site
- **THEN** the background jobs are processed through it

