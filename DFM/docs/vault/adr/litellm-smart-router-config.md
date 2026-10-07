# ADR: LiteLLM Smart Router Configuration

## Status
Proposed (2026-10-07)

## Context
Using LiteLLM smart router to automatically select model tier based on task complexity:
- Flow: cline → litellm (smart-router) → langfuse → model
- Goal: Cost reduction by routing simple tasks to cheaper models
- Problem: Excessive token usage (70k input → 100 token output) and misclassification

## Decision

### Tier Structure
```yaml
SIMPLE:    quick-edits      # Isolated, trivial changes
MEDIUM:    daily-coding     # Standard development (DEFAULT)
COMPLEX:   heavy-refactor   # Multi-file, architectural
REASONING: security-critical # Auth, security, production
```

### Token Budget Limits (Recommended)
```yaml
max_tokens_threshold:
  SIMPLE: 2000      # Force concise context
  MEDIUM: 8000      # Normal working set
  COMPLEX: 32000    # Large refactors
  REASONING: 100000 # No limit for critical
```

### Keyword Specificity Rules
1. **Be explicit**: "fix typo in X.php" not just "typo"
2. **Use phrases**: "refactor entire" not "refactor"
3. **Domain terms**: "authentication system" not "auth"
4. **Avoid ambiguity**: Words that appear in conversation vs. task descriptions

### Default Tier Strategy
- Default to MEDIUM (daily-coding) when no keyword match
- SIMPLE tier is opt-in (explicit keywords needed)
- Prevents under-provisioning for ambiguous requests

## Consequences

### Positive
- Clearer tier boundaries reduce misclassification
- Token limits prevent context bloat at routing level
- Explicit defaults prevent "too cheap" model selection

### Negative
- May over-provision for truly simple tasks initially
- Requires discipline in phrasing user requests
- Heuristic classifier still limited vs. semantic understanding

## Alternatives Considered

### 1. Semantic Router (Rejected)
- Uses embeddings to classify requests
- Pro: Better context understanding
- Con: Adds token cost for classification itself
- Con: Requires additional API calls

### 2. Manual Model Selection (Rejected)
- User specifies model per request
- Pro: Perfect accuracy
- Con: Cognitive overhead for user
- Con: Defeats purpose of "smart" routing

### 3. Cost-based Auto-fallback (Future)
- Start with SIMPLE, upgrade to MEDIUM if context grows
- Pro: Adaptive to actual complexity
- Con: Requires mid-request model switching (not supported yet)

## Implementation Notes

See improved configuration in project documentation:
- Location: `docs/vault/gotchas/token-optimization.md`
- Testing: Monitor Langfuse traces for tier distribution
- Tuning: Adjust keywords based on real usage patterns after 1 week

## Missing Optimizations (Not Yet Implemented)

### Prompt Caching (HIGH IMPACT - Recommended Next Step)
**Status:** Not configured in current setup  
**Potential savings:** 90% cost reduction on cached content, 80% latency reduction

Anthropic's Prompt Caching allows reusing static prompt prefixes:
- **Automatic caching:** Single `cache_control` at top level
- **Explicit breakpoints:** Up to 4 cache points (tools, system, context, messages)
- **TTL:** 5 minutes (ephemeral) or 1 hour (persistent)
- **Compatible with:** Tool use, multi-turn conversations, RAG

**How to enable in LiteLLM:**
```python
# LiteLLM automatically passes cache_control to Anthropic models
# Just need to structure prompts with cache boundaries
```

**Recommended implementation:**
1. Cache tool definitions (rarely change)
2. Cache system instructions (static)
3. Cache RAG/docs context (updated per session)
4. Auto-cache conversation history (growing messages)

**Expected impact on your setup:**
- 70k tokens with caching → ~15k cache_read + 5k new = **20k total cost equivalent**
- First request: Full cost + cache write
- Subsequent requests: 90% cheaper (cache hits)

### Context Compaction
**Status:** Mentioned in system prompt but not actively implemented  
**Cline feature:** "Your context window will be automatically compacted"

From research: Cline's compaction is **automatic** when approaching limits:
- Summarizes old conversation turns
- Preserves recent context
- No configuration needed (built-in)

**Already working** - no action needed.

## References
- LiteLLM Routing Docs: https://docs.litellm.ai/docs/routing
- Anthropic Prompt Caching: https://docs.anthropic.com/en/docs/build-with-claude/prompt-caching
- Token optimization: `docs/vault/gotchas/token-optimization.md`
- Cline rules: `.clinerules`
