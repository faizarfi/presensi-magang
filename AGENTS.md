# Antigravity & Agent Guidelines (Inspired by Andrej Karpathy)
# Source: https://github.com/multica-ai/andrej-karpathy-skills

Behavioral guidelines to reduce common LLM coding mistakes and ensure concise, reliable, and surgical development.

**Tradeoff:** These guidelines bias toward caution and precision over unverified speed.

---

## 1. Think Before Coding
**Don't assume. Don't hide confusion. Surface tradeoffs.**

Before implementing:
- State assumptions explicitly. If uncertain, ask rather than guess.
- If multiple interpretations exist, present them — don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear or conflicting, stop. Name what's confusing and ask.

## 2. Simplicity First
**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was explicitly asked.
- No abstractions or wrappers for single-use code.
- No speculative "flexibility" or configurability that wasn't requested.
- No complex error handling for impossible scenarios.
- If 200 lines could cleanly be 50, rewrite and simplify it.
- **The test:** "Would a senior engineer say this is overcomplicated?" If yes, simplify.

## 3. Surgical Changes
**Touch only what you must. Clean up only your own mess.**

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match the existing style and conventions of the codebase.
- If you notice unrelated dead code or bugs, mention them — don't delete or mutate them unilaterally.

When changes create orphans:
- Remove imports, variables, or functions that YOUR changes made unused.
- Do not touch pre-existing legacy code unless explicitly requested.
- **The test:** Every changed line should trace directly to the user's request.

## 4. Goal-Driven Execution
**Define success criteria. Loop until verified.**

Transform tasks into verifiable goals:
- Transform "make it work" into clear, testable success criteria.
- Multi-step tasks must state a concise plan:
  ```
  1. [Step] -> verify: [check]
  2. [Step] -> verify: [check]
  ```
- Verify and test directly before concluding the task.
