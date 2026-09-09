# Phase 10 — WP.org Submission Readiness


- [ ] `readme.txt` complete: description, installation, FAQ, screenshots, changelog, "Additional Information" disclosing third-party AI API usage.
- [ ] No obfuscated/minified-only code without a build step + source available (your build step already handles this since compiled assets come from committed source).
- [ ] All bundled third-party code GPL-compatible and disclosed.
- [ ] Full `phpcs` (WordPress-Extra) pass with zero errors, warnings justified/documented if suppressed.
- [ ] Uninstall behavior tested: default (data preserved) and opt-in (full cleanup) both verified via integration test.
- [ ] Privacy: since AI features send product titles/descriptions to third-party APIs, add a short privacy notice pointing to each provider's policy, and make sure it's clearly opt-in (no API key configured = feature fully dormant, no outbound calls).

---

