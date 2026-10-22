---
paths:
  - "app/**"
  - "routes/**"
  - "database/**"
  - "config/**"
  - "resources/views/**"
  - "resources/js/**"
  - "resources/css/**"
  - "tests/**"
  - "composer.json"
  - "composer.lock"
  - "artisan"
  - "phpunit.xml"
  - "vite.config.*"
  - "package.json"
---
# Laravel implementation rules

- First inspect the relevant route, middleware, controller/action, model, migration/schema, frontend page, and test. Only inspect layers that exist and affect the task.
- Prefer Laravel Boost documentation search/MCP for framework and package APIs when available; otherwise inspect installed versions and local code before using online/current assumptions.
- Query schema narrowly through Boost or targeted migrations. Never dump the entire database or `.env`.
- Use the project's existing Composer scripts, test framework, formatter, and package manager. Check `composer.json` / `package.json` scripts rather than assuming command names.
- Do not create a service/repository/controller just to fit a generic architecture. Follow existing project conventions and use the smallest appropriate change.
- For frontend work, trace the actual route/page/props and preserve the project's Inertia/React/Vue conventions instead of assuming one.
- Run the narrowest relevant test/check, then inspect `git diff --check`. Report any test not run and why.
