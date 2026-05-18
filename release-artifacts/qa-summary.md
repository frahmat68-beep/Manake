# 📋 Manake Production QA Summary

This document consolidates the official QA verification parameters, environment metrics, and build status checks for the final production deployment of the **Manake Web Application**.

---

## 🔗 1. Release & Deployment Details

- **Final Commit Hash:** `d077a3614ae42cdfd408eecda92f93ad26102505`
- **Vercel Deployment URL:** [Vercel Deployment Dashboard](https://vercel.com/fikris-projects-ade2d7c3/manake/FGQHKCQWFvbaRks8pjZqMv2X5ojP)
- **Production Alias URL:** [https://manake.id](https://manake.id) (Vercel production preview: [https://manake-47x38phvs-fikris-projects-ade2d7c3.vercel.app](https://manake-47x38phvs-fikris-projects-ade2d7c3.vercel.app))

---

## 🔎 2. Audit Parameters & Page List

The following production pages were audited across desktop and mobile layout profiles, verifying responsive breakpoints down to `360px` viewport widths:

1. **Homepage (`/`):** Tested visual layout, Google font rendering, modern glassmorphic header, hero banner, category cards grid, testimonials, and footer.
2. **Catalog Page (`/catalog`):** Tested filters, category switches, responsive layouts, search functions, and equipment status cards.
3. **Product Detail Page (`/catalog/{slug}`):** Tested image slide carousel, details accordions, dates picker, terms checkbox, and direct additions to cart.
4. **Availability Board (`/availability/board`):** Tested calendar loading, equipment availability grid, status coloring, and real-time slots locked states.

---

## 🕹️ 3. Verified End-to-End Manual QA Flows

The following operational checkout flow was successfully simulated, isolated, and verified:

1. **User Profile Verification:** Resolved double profile record issue for the target user `tester_manake_2026@example.com`, ensuring `profileIsComplete()` and `hasVerifiedPhone()` correctly return `true` with no data contradictions.
2. **Checkout Integration:** Ensured Midtrans Snap initialization runs correctly in the frontend client sandbox, and loads secure SNAP transaction token scripts.
3. **Payment Simulation:** Direct state transition simulation to `paid` updates order statuses correctly in the AWS Supabase Postgres DB cluster.
4. **Slot Locking:** Verified that availability board dates block and lock immediately upon successful payment of slot orders.

---

## 📦 4. Production Build Summary (`npm run build`)

```text
vite v7.3.1 building client environment for production...
✓ 55 modules transformed.
public/build/manifest.json                0.53 kB │ gzip:  0.20 kB
public/build/assets/theme-xrSB6bhG.css   69.70 kB │ gzip: 11.32 kB
public/build/assets/app-BMEnzwM6.css    123.84 kB │ gzip: 18.14 kB
public/build/assets/app-CBbTb_k3.js      83.04 kB │ gzip: 30.86 kB
✓ built in 2.63s
[vercel-sync-dist] Synced public/build -> dist
[vercel-sync-storage] Synced storage/app/public -> public/storage
```

---

## 🧪 5. Backend Test Suite Summary (`vendor/bin/phpunit`)

```text
PHPUnit 11.5.50 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.2
Configuration: /Users/kiki/Documents/Web Develop/Website Manake/phpunit.xml

...............................................................  63 / 136 ( 46%)
.............................................................. 126 / 136 ( 92%)
..........                                                      136 / 136 (100%)

Time: 00:32.094, Memory: 86.50 MB

OK (136 tests, 520 assertions)
```

---

## ⚠️ 6. Remaining Visual Issues

- **None.** Scoping legacy dashboard class assets `.spotlight-shell`, `.noise-overlay`, `.premium-card`, `.btn-primary`, and `.btn-secondary` to `body:not(.landing-shell)` successfully prevented CSS leakage into public layout interfaces. The homepage, catalog, detail, and board load beautifully with clean, harmonized, 100% TA-ready visual components.
