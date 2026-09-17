## Purpose

Expose a REST API on the `wsfq/v1` namespace that lets headless and decoupled front ends (such as Next.js) read and write FAQ content and settings, alongside the admin UI and existing settings route. Every endpoint enforces real authorization, request-argument validation and sanitization, and same-origin admin requests verify a nonce, so no route trusts raw input.

## ADDED Requirements

### Requirement: Expose FAQ read and write endpoints

The plugin SHALL register REST API endpoints under the `wsfq/v1` namespace that allow reading and writing FAQ entries, so headless or decoupled front ends such as Next.js can manage FAQ content without the admin UI.

#### Scenario: FAQ write request applies the change

- **WHEN** a client sends an authenticated request to an FAQ write route under `wsfq/v1` (such as create, update, or delete)
- **THEN** the requested FAQ change is applied to the site
- **AND** an error response is returned when the request is unauthorized or invalid

### Requirement: Expose public read-only FAQ endpoints

The plugin SHALL register public, read-only REST API endpoints under `wsfq/v1` that expose published FAQ content to unauthenticated visitors and headless front ends.

#### Scenario: Public read-only request returns published FAQs

- **WHEN** an unauthenticated client requests a read-only FAQ endpoint under `wsfq/v1`
- **THEN** the published FAQ data is returned
- **AND** unpublished or trashed FAQs are not included in the response

### Requirement: Restrict admin routes to the required capability

Every admin-facing REST API route SHALL declare a real `permission_callback` that requires the appropriate WordPress capability before any request is processed.

#### Scenario: Admin route requires the capability

- **WHEN** a request targets an admin-facing FAQ or settings route under `wsfq/v1`
- **THEN** the request is allowed only when the caller has the required capability
- **AND** callers without that capability receive a permission-denied error and no data is changed or returned

### Requirement: Rate-limit public routes

Every public, unauthenticated REST API route SHALL be subject to rate limiting so the endpoints cannot be abused by unauthenticated clients.

#### Scenario: Public route is rate-limited

- **WHEN** an unauthenticated client requests a public read-only route under `wsfq/v1`
- **THEN** the request is served within the allowed rate limit
- **AND** once the rate limit is exceeded, further requests are rejected with an error

### Requirement: Validate and sanitize every request argument

Every registered REST API route SHALL declare an args schema that provides both a `validate_callback` and a `sanitize_callback` for each parameter, so request parameters are never trusted raw.

#### Scenario: Invalid parameters are rejected

- **WHEN** a client sends a request containing route parameters under `wsfq/v1`
- **THEN** each parameter is validated against its declared schema
- **AND** each parameter is sanitized before it is used
- **AND** requests with invalid parameters are rejected with a validation error instead of being processed

### Requirement: Verify a nonce for same-origin admin requests

Same-origin admin UI requests to the REST API SHALL include and verify a nonce before processing, so the admin interface cannot be exploited by cross-site requests.

#### Scenario: Admin request without a nonce is rejected

- **WHEN** the admin UI makes a same-origin request to a `wsfq/v1` route
- **THEN** the request must include a valid nonce
- **AND** requests without a valid nonce are rejected

### Requirement: Keep the settings route for plugin settings

The existing `wsfq/v1/settings` route SHALL continue to provide GET and POST of plugin settings and SHALL be gated to users with the `manage_options` capability.

#### Scenario: Settings route serves authorized users

- **WHEN** a user with the `manage_options` capability requests `wsfq/v1/settings`
- **THEN** GET returns the current plugin settings and POST applies the submitted settings
- **AND** users without the `manage_options` capability are denied access to the route