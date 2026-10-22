---
paths:
  - "resources/js/**"
  - "resources/css/**"
  - "resources/views/**"
  - "vite.config.*"
  - "tailwind.config.*"
  - "postcss.config.*"
  - "tsconfig.json"
  - "package.json"
---
# Frontend rules

- Identify the actual stack and versions from source and package manifests before editing. Do not assume React, Vue, TypeScript, Tailwind, or Inertia features without evidence.
- Search for an existing component, hook, type, style, or page pattern before creating a new one.
- Avoid dumping `node_modules`, generated `public/build`, source maps, or full package lockfiles into context. Inspect only relevant manifest entries and source ranges.
- Run the smallest relevant lint, typecheck, or build script actually defined by the project. Report the real exit code.
