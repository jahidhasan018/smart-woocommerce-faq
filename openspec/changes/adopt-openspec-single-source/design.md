# Design

## Context

Consolidating the build roadmap and feature status onto OpenSpec. Previously the project kept four overlapping systems: `Planing/plan.md`, `docs/phases/*.md` (verbatim per-phase copies), `docs/features/*.md`, plus `docs/PROGRESS.md` and `docs/DECISIONS.md`. See proposal.md Why.

Migration constraint: delete `Planing/` and `docs/` only after every piece of durable content has a home inside `openspec/`. `ARCHITECTURE.md`, `README.md`, `AGENTS.md`, `CONTRIBUTING.md` stay as pointer docs that link into `openspec/`.

## Goals / Non-Goals

**Goals:**
- One validated source of truth for the product behavior contract (`openspec/specs/`).
- One source for live roadmap status (`openspec/ROADMAP.md`) tied to change open/archived state.
- One source for architecture decisions (`openspec/DECISIONS.md`).
- Roadmap phases and feature-acceptance work tracked as OpenSpec change tasks so progress is machine-checkable.

**Non-Goals:**
- Not a migration of product source code (`src/`, `tests/`, `assets/`, `blocks/`) — none of it changes here.
- Not importing the WordPress agent-skills content into OpenSpec (those stay as on-demand skills).
- Not migrating `ARCHITECTURE.md` body into a spec: it stays a standalone reference doc.

## Decisions

- **Durable product requirements live as OpenSpec capability specs.** Each `docs/features/*` and feature-list section maps to one capability spec (16 capabilities). Rationale: OpenSpec specs are the behavior contract, and archived change spec-deltas sync into `openspec/specs/`. Alternative considered: keeping `docs/features/*` and mirroring in OpenSpec — rejected because that recreates the duplicate-source problem.
- **Roadmap status lives in `openspec/ROADMAP.md`, kept in sync with change open/archived state.** Rationale: OpenSpec has no native "progress table" artifact, so a small human-readable status table is the pragmatic home, and the migration proposal's `tasks.md` mirrors the outstanding roadmap as real tracked work. Alternative: only relying on change checkboxes — less glanceable for a portfolio/roadmap view.
- **Architecture decisions move verbatim to `openspec/DECISIONS.md` as ADRs.** No content loss; the ADRs are project-wide and long-lived. Alternative: folding into change design docs — rejected because ADRs outlive any single change.
- **Deleted source docs are not copied everywhere.** Only the durable content moves. Repetitive per-phase copies in `docs/phases/` are consolidated into the proposal's phase groups rather than duplicated 14x.
- **Existing done features map to specs as "SHALL" target contracts**, with their current status recorded in `ROADMAP.md` — not by inventing separate archived changes for already-done work. Avoids fabricating historical changes.

## Risks / Trade-offs

- [Stale roadmap table] -> It is authoritative only while kept in sync with change open/archived state; the config `operations` guidance reminds the agent to update it at apply/archive time.
- [Loss of per-phase verbatim copies] -> Mitigated: phases are captured as task groups in this proposal's `tasks.md`, which is the working picture that was being copy-pasted.
- [Empty feature files 09-27 dropped detail] -> Those files were already empty stubs; their real content (in `feature-list.md` and `plan.md`) was captured into the capability specs, so nothing substantive is lost.

## Migration Plan

1. Proposal + config context created (this change).
2. 16 capability delta specs authored under `openspec/changes/adopt-openspec-single-source/specs/`.
3. `openspec/ROADMAP.md` created (phases + feature status from docs/PROGRESS.md).
4. `openspec/DECISIONS.md` created (ADRs carried from docs/DECISIONS.md).
5. `tasks.md` for this change mirrors the outstanding roadmap.
6. Validate the change (`openspec validate`); on success, sync specs into `openspec/specs/` (archive path).
7. Update root pointers (`README.md`, `AGENTS.md`) to reference `openspec/`.
8. Delete `Planing/` and `docs/` after user confirmation.

Rollback: all content is preserved in git history; the prior `docs/` and `Planing/` are recoverable from the pre-migration commit.

## Open Questions

- None that change specs. (Feature-level ambiguity — e.g. exact AI tone list — is captured as target contract and can be refined per-feature without changing this migration.)