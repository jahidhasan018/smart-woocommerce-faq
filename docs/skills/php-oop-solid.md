---
name: php-oop-solid
description: How SOLID applies in this codebase, with the interface-first convention for AI providers, cache backends, and renderers.
---

# PHP / OOP / SOLID in This Codebase

## Rules
- Every class has one reason to change.
- Depend on interfaces (`AiProviderInterface`, `CacheInterface`, `RendererInterface`), never concrete classes. A class that has more than one possible strategy must implement an interface (see AGENTS.md).
- No god classes, no static-everything utility dumps.
- Constructor injection only — never `new SomeConcreteClass()` buried inside another class; resolve through the container in `Core/Container.php`.
- Every function has a docblock. `declare(strict_types=1);` in every `/src` file.

## Reference pattern
When a new concrete class is added, model it on the established interface-first classes in `/src/` (AI providers, cache backends, renderers). New classes that could have multiple strategies must first define the interface, then a test, then the implementation (TDD).

## Adding a new class
1. Define the interface if more than one strategy is possible.
2. Write the failing test.
3. Implement minimum code to pass.
4. Refactor for SOLID compliance.