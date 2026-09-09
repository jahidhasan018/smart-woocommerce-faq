# Contributing

This is largely a human-facing summary of `AGENTS.md`. Read `AGENTS.md` first — it is the authoritative source for setup, test commands, and non-negotiable rules.

## Onboarding
1. `composer install`
2. `npm install`
3. `npm run env:start` (boots `wp-env`), then `npm run env:cli -- wp wc install` for WooCommerce sample data.

## How to contribute a feature
The canonical build loop lives in plan.md Phase 3. In short, for each feature:
1. Create `docs/features/NN-name.md` with Goal + Acceptance Criteria.
2. Branch `feature/NN-name` off `develop`.
3. Write failing tests first (TDD — non-negotiable).
4. Implement the minimum code to pass, then refactor for SOLID.
5. Run `composer cs && composer stan && composer test`.
6. Add Playwright e2e coverage if user-facing.
7. Update the feature doc and `docs/PROGRESS.md`.
8. Open a PR → CI green → merge to `develop`.

## Rules that are non-negotiable
- TDD only: failing test before implementation, every time.
- Interfaces for any class with more than one possible strategy.
- Never raw SQL — always `$wpdb->prepare()`.
- Never echo unescaped output — `esc_html`/`esc_attr`/`wp_kses_post`.
- Every REST route needs a real `permission_callback`.
- If a requirement is ambiguous or touches the data model / public API / security, stop and ask — do not guess.

## Commit convention
Use [Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `chore:`, `docs:`, `test:`.

## Git workflow
`develop` is the integration branch; features branch off it. Releases are cut as `release/x.y.z` and deployed to WP.org SVN from GitHub tags only — never hand-edit the SVN repo.