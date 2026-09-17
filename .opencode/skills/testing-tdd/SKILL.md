---
name: testing-tdd
description: The red-green-refactor loop expected here, which test type covers what, and mocking conventions (Brain\Monkey for WP functions in unit tests).
---

# Testing & TDD

## The loop (non-negotiable)
1. Write a failing test first.
2. Write the minimum code to pass.
3. Refactor for SOLID compliance.
4. Run `composer cs && composer stan && composer test` before marking done.

No PHP class ships without a corresponding test file. An agent writing implementation before a test is violating the plan.

## Which test type covers what
- **Unit** (`tests/Unit`, `composer test:unit`): pure logic with no WP bootstrap — AI prompt builder, cache key generation, etc. Fast (milliseconds).
- **Integration** (`tests/Integration`, `composer test:integration`): anything touching `$wpdb`, post types, or hooks firing end-to-end. Runs against live `wp-env`.
- **E2E** (`tests/e2e`, `npm run test:e2e`): Playwright via `@wordpress/e2e-test-utils-playwright` — admin settings flows, the product-edit FAQ panel, and frontend accordion rendering/interaction.

## Mocking conventions
Use **Brain\Monkey** to mock WP core functions in unit tests. In integration tests, use the real WP test suite inside `wp-env`.

## Fixtures / seed data
`bin/seed-demo-data.sh` seeds demo products + demo FAQs via WP-CLI so tests and agents get working data instantly.