# 0. Guiding Principles (apply to every phase below)


1. **TDD is non-negotiable.** For every unit of behavior: write a failing test → write the minimum code to pass → refactor. No PHP class ships without a corresponding test file. An agent that writes implementation code before a test is violating the plan.
2. **SOLID + OOP throughout.** Every class has one reason to change. Depend on interfaces (`AiProviderInterface`, `CacheInterface`, `RendererInterface`), not concrete classes. No god classes, no static-everything utility dumps.
3. **Ask, don't assume.** If a requirement, data shape, UX behavior, or naming decision is ambiguous, the agent stops and asks you — it does not guess and it does not silently pick "a reasonable default" for anything architectural (data model, hook names, public API shape, security-sensitive logic). Cosmetic micro-decisions (a variable name, whitespace) don't need to block on this.
4. **No slop code.** Every function has a docblock. Every external input is validated/sanitized. Every output is escaped. No commented-out code left in commits. No "TODO: fix later" merged into `develop`.
5. **Small, reviewable units.** One feature = one branch = one PR = one docs/features file = passing CI before merge.

---

