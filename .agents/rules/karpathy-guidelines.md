---
name: karpathy-guidelines
description: Behavioral guidelines to prevent LLM mistakes, enforcing simplicity, surgical edits, explicit thinking, and goal-driven verification.
---

# Karpathy Coding Guidelines

Behavioral guardrails derived from Andrej Karpathy's observations on LLM coding pitfalls:

## 1. Think Before Coding
- Never guess or pick silent assumptions.
- Name confusion openly and ask when requirements conflict or are ambiguous.
- Present trade-offs clearly.

## 2. Simplicity First
- Minimum necessary code to solve the user's problem.
- No premature abstractions, speculative extensibility, or unnecessary layers.
- Prefer 50 clean lines over 200 bloated lines.

## 3. Surgical Changes
- Touch ONLY what is directly required to fulfill the user's prompt.
- Do not reformat, refactor, or delete adjacent working code or unrelated comments.
- Clean up only variables/imports orphaned by your own changes.

## 4. Goal-Driven Execution
- Convert instructions into concrete, verifiable success criteria.
- Test and verify with deterministic feedback before finishing.
