# Phase 11 — Post-Launch Operations


**Skills to load:** `wp-plugin-directory-guidelines`, `wp-wpcli-and-ops`

- Support forum monitoring cadence (WP.org support forum) — decide upfront how often you'll check it; unanswered threads hurt your rating and review-team standing for future plugins.
- `hotfix/*` branch process (Phase 1.2) exercised for the first real production bug — treat it as a fire drill early rather than the first time under pressure.
- Marketing/analytics future-proofing: since you explicitly don't want a premium tier now, still design the extension points (interfaces, hook names, a documented `wsfq_extensions` filter) so a future companion add-on (or even a totally separate premium plugin) could integrate without you rewriting core — you don't have to build it, just don't architecturally block it.
- Revisit `docs/DECISIONS.md` quarterly; anything that's aged into "we'd do this differently now" gets a new decision entry, not a silent rewrite.

---

