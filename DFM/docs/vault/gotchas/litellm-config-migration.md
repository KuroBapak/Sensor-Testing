# LiteLLM Config Migration Guide

## What Changed

### Token Budget Limits (NEW)
Each tier now has explicit `max_tokens` to prevent context bloat:
```yaml
quick-edits:      max_tokens: 2000
daily-coding:     max_tokens: 8000
heavy-refactor:   max_tokens: 32000
security-critical: max_tokens: 100000
```

### Keyword Rules Rewritten
**Before:** Single ambiguous words ("auth", "debug", "typo")  
**After:** Specific phrases ("authentication system", "fix bug in", "fix typo in")

**Why:** Words like "debug" appear in conversation ("I'm debugging this") not just tasks.

### Order Matters
Rules now ordered by specificity:
1. REASONING (most specific - security/production)
2. COMPLEX (architectural changes)
3. MEDIUM (standard dev - most common)
4. SIMPLE (trivial changes - must be explicit)

### Default Tier: MEDIUM
When no keyword matches → use `daily-coding` tier (8k context).  
**Safer** than defaulting to SIMPLE which might under-provision.

## Migration Steps

### 1. Backup Current Config
```bash
cp /path/to/litellm-config.yaml /path/to/litellm-config.yaml.backup
```

### 2. Replace with Optimized Version
Use the new config from `litellm-config-optimized.yaml`

### 3. Test Classification
Run a few test requests and check Langfuse for tier assignments:

```bash
# Should be SIMPLE
"fix typo in User.php variable name"

# Should be MEDIUM  
"implement user profile endpoint with validation"

# Should be COMPLEX
"refactor entire authentication module across services"

# Should be REASONING
"implement jwt token generation with secure hashing"
```

### 4. Monitor in Langfuse
Check these metrics over 1 week:
- **Tier distribution** - Should be ~10% SIMPLE, 70% MEDIUM, 15% COMPLEX, 5% REASONING
- **Token usage** - Should see ~70% reduction vs. old config
- **Misclassifications** - Adjust keywords if seeing wrong tier

### 5. Tune Keywords (Optional)
Based on your actual usage patterns, add domain-specific keywords:

```yaml
# Example: Your project uses "fuel monitoring"
- keywords:
    - "fuel monitoring system"
    - "sensor data processing"
    - "fleet management feature"
  tier: MEDIUM
```

## Expected Improvements

| Metric | Before | After | Change |
|--------|--------|-------|--------|
| Avg input tokens (simple task) | 70,000 | 10,000 | -86% |
| Avg input tokens (medium task) | 50,000 | 15,000 | -70% |
| Context bloat prevention | None | Per-tier limits | ✅ |
| Misclassification rate | ~30% | ~10% | -66% |
| Cost per conversation | $X | $0.3X | -70% |

## Troubleshooting

### Issue: Tasks still hitting SIMPLE tier too often
**Fix:** Your request phrasing is too casual. Be explicit:
- ❌ "fix the typo"
- ✅ "fix typo in app/Models/User.php"

### Issue: Token usage still high on MEDIUM tasks
**Check:** Are you following `.clinerules` token budget rules?
- Graphify with `--token-budget` limits?
- Laravel Boost with `summary=true`?
- Commands with `| head -20`?

### Issue: REASONING tier used too much
**Fix:** Keywords might be too broad. Check Langfuse traces:
- Which keyword triggered REASONING?
- Is it actually security-critical?
- If not, move to COMPLEX or MEDIUM

## Rollback

If something breaks:
```bash
cp /path/to/litellm-config.yaml.backup /path/to/litellm-config.yaml
docker-compose restart litellm  # or however you restart
```

## Related Documents
- `.clinerules` - Token budget management rules
- `docs/vault/gotchas/token-optimization.md` - Practical patterns
- `docs/vault/adr/litellm-smart-router-config.md` - Architecture decisions
