# Manake UI Review Workflow

This document describes the repeatable UI review workflow for Manake.

## What this project is

Manake is a modern media equipment rental platform for cameras, lighting, audio, HT, drones, stabilizers, and production gear.

The UI should feel:
- cinematic
- premium
- modern
- clean
- trustworthy
- practical for event organizers, students, filmmakers, and production crews

## How to run the app

### Production-like local server

```bash
php artisan serve --host=127.0.0.1 --port=3000
```

### Frontend assets

```bash
npm run build
```

If you are actively editing the frontend:

```bash
npm run dev
```

## Playwright UI screenshots

Use the built-in screenshot audit to capture the homepage, catalog, and availability board at common breakpoints:

```bash
npx playwright install chromium
npm run ui:review
```

This creates screenshots in:

```text
test-results/ui-review
```

The current viewport set is:
- desktop: `1440x900`
- laptop: `1280x800`
- tablet: `768x1024`
- mobile: `390x844`

### What to check in screenshots

Before merging, inspect screenshots for:
- first-screen impact
- obvious primary CTA
- spacing consistency
- card alignment
- text overflow
- awkward wrapping
- broken responsive layout
- low contrast
- missing empty/loading states

## Lighthouse

Run Lighthouse locally. The script starts a temporary Laravel server on `http://127.0.0.1:3000` when nothing is already available:

```bash
npm run lighthouse
```

To audit another URL:

```bash
LIGHTHOUSE_URL=https://example.com npm run lighthouse
```

Recommended thresholds:
- accessibility: at least `0.90`
- best practices: at least `0.90`
- SEO: at least `0.80`
- performance: monitor, but do not block too aggressively on first rollout

## PR review workflow

When reviewing a pull request, use this order:
1. open the app locally
2. capture screenshots with `npm run ui:review`
3. run Lighthouse with `npm run lighthouse`
4. inspect mobile first if the change affects layout
5. fix overflow, spacing, contrast, and CTA clarity before shipping

## How to ask Codex to fix UI review findings

Use a request like:

> Review the current homepage at desktop/tablet/mobile, list the top visual issues first, then fix them and repeat until the layout is clean.

Good issue categories:
- text overflow
- mobile layout breakage
- inconsistent spacing
- low contrast
- weak hierarchy
- cramped cards
- hidden CTA

## GitHub PR review notes

If you want Codex to review a PR, ask for:
- screenshot audit findings first
- concrete visual issues
- then the fix plan or direct fix

If the issue is visual, treat mobile layout and overflow as high priority.
