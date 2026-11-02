---
name: karpathy-guidelines
description: Enforce Andrej Karpathy's 4 core guidelines for AI coding agents - Think Before Coding, Simplicity First, Surgical Changes, and Goal-Driven Execution.
---

# Karpathy Guidelines Skill

Use this skill to guide agent behavior during development, debugging, and refactoring:

### Principles:
1. **Think Before Coding**: State assumptions, present multiple interpretations if ambiguous, push back against bad design, ask when confused.
2. **Simplicity First**: Write the simplest solution that works. Avoid boilerplate, premature generalization, and bloated helpers.
3. **Surgical Changes**: Make narrow diffs. Never mutate unrelated files or lines. Respect existing code idioms.
4. **Goal-Driven Execution**: Formulate a verifiable check for every change. Loop until verified.
