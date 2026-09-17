# Tasks

## 1. Scaffold the migration

- [ ] 1.1 Create this change and verify `openspec status --change adopt-openspec-single-source` lists proposal/specs/design/tasks
- [ ] 1.2 Enrich `openspec/config.yaml` context and verify `openspec context --json` returns it

## 2. Author the durable capability specs

- [ ] 2.1 Write proposal.md listing all 16 new capabilities and verify proposal artifact exists
- [ ] 2.2 Write all 16 delta capability specs under specs/<capability>/spec.md and verify 16 spec files exist
- [ ] 2.3 Validate every spec format (## Purpose, ### Requirement, #### Scenario with WHEN/THEN) so `openspec validate` passes

## 3. Author design + tasks + ROADMAP

- [ ] 3.1 Write design.md (migration mapping + carried ADRs + migration plan) and verify it exists
- [ ] 3.2 Write tasks.md describing the migration steps and verify checkbox format parses
- [ ] 3.3 Create `openspec/ROADMAP.md` with all 12 phases + 27 features and their status, verified against docs/PROGRESS.md
- [ ] 3.4 Create `openspec/DECISIONS.md` carrying all ADRs 000-012 plus the open decision slots

## 4. Validate and sync

- [ ] 4.1 Run `openspec validate` (and `--strict`) and fix any requirement/scenario/format errors until green
- [ ] 4.2 Confirm `openspec status --change adopt-openspec-single-source` reports all artifacts done
- [ ] 4.3 Sync specs into main `openspec/specs/` so the capability contract is durable beyond this change
- [ ] 4.4 Confirm `openspec list --specs` shows the 16 capabilities under `openspec/specs/`

## 5. Update root pointers

- [ ] 5.1 Update README.md and AGENTS.md to reference openspec/ instead of docs/ and Planing/
- [ ] 5.2 Set up single-source opsx skill generation and gitignore per-tool generated copies

## 6. Remove superseded docs

- [ ] 6.1 After user confirmation, delete `Planing/` and `docs/`
- [ ] 6.2 Verify no dangling references to `docs/` or `Planing/` remain in tracked files (grep)
- [ ] 6.3 Final `openspec validate` green and `ROADMAP.md` reflects current status