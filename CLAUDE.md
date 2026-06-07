# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development

This is a **pure static site** — no build step, no package manager, no compilation. Open any HTML file directly in a browser, or spin up a local server:

```bash
python -m http.server 8080
# then open http://localhost:8080
```

### Image Processing Scripts

The Python and PowerShell scripts in the repo root are one-shot image utilities. They have **hardcoded Windows XAMPP paths** (`i:/xampp/htdocs/vibecodeweb/`) that must be updated before running on any other machine.

| Script | Purpose |
|---|---|
| `crop.py` / `crop.ps1` | Slices `images/5logo.jpg` into a 3×2 grid → `images/logo_0.jpg` through `logo_5.jpg` |
| `make_transparent.py` / `transparent.ps1` | Creates `images/new_site_logo_transparent.png` from `images/new_site_logo.png` by promoting the brightest channel to alpha |
| `temp_crop.ps1` | Scratch variant of the crop script — not canonical |

Requirements: `pip install Pillow` for the Python scripts; `System.Drawing` assembly (Windows) for the PowerShell scripts.

## Architecture

### Two independent HTML pages

The repo contains two self-contained pages that share no code:

- **`index.html`** (1321 lines) — the primary production page. Elaborate dark-theme design using Outfit + Playfair Display fonts and FontAwesome icons. Has a JS-powered dark/light mode toggle (`darkMode: 'class'`).
- **`index2.html`** (469 lines) — a newer, cleaner redesign using Material Design 3 color tokens and Google Material Symbols icons. Hero images are served from Google's CDN (`lh3.googleusercontent.com`) rather than `images/`. Hardcodes `<html class="dark">` with no toggle.

Neither page imports the other or shares assets.

### `index.html` section map

Use these IDs and line numbers to navigate the 1321-line file:

| Line | ID / landmark | Content |
|------|--------------|---------|
| 162 | navbar | Top nav with mobile hamburger |
| 205 | — | Mobile dropdown menu |
| 235 | `#hero` | Hero with mandala background, rotating logo |
| 301 | — | AI expertise marquee banner |
| 384 | `#ai-benefits` | AI benefit section (image + text) |
| 438 | `#ai-advantages` | AI advantages section (text + dashboard image) |
| 507 | `#vision` | Goal & moat / vision section |
| 568 | `#about` | Mission section with founder portrait |
| 628 | `#services` | Six service cards |
| 731 | `#pricing` | Three pricing plans (Starter / Pro AI / Custom) |
| 826 | `#contact` | Contact info + inline contact form |
| 919 | `#footer` | Footer with email + WhatsApp links |
| 1064 | `<script>` | All page JS (nav, dark mode, carousel, scroll-reveal, modal) |

The contact section (`#contact`) contains both direct contact info (WhatsApp `wa.me/919477443425`, email `samya.indya@gmail.com`) and a form modal — no backend; form submission opens WhatsApp.

### Tailwind CSS via CDN — no local build

Both pages load Tailwind from `https://cdn.tailwindcss.com`. There is no `package.json`, no Tailwind CLI, and no PostCSS pipeline. Tailwind configuration (custom colors, font families, spacing tokens) lives in an inline `<script>` block near the top of each HTML file.

The two pages use **completely different color token sets**:
- `index.html` — tokens like `brand-dark`, `brand-primary`, `brand-accent`, `brand-neon`, `brand-saffron`, `brand-indiaGreen`
- `index2.html` — Material Design 3-style semantic tokens (`surface`, `on-surface`, `primary-container`, `electric-purple`, `neon-green`, etc.)

When editing colors or adding new Tailwind utilities, update the `tailwind.config` block inside the specific file being changed — changes to one file's config do not affect the other.

### JavaScript

All JS is inline at the bottom of each HTML file inside `<script>` tags. There are no external `.js` files. `index.html` handles: mobile nav toggle, dark/light mode switch, logo carousel animation, scroll-reveal effects, and a contact form modal. `index2.html` has no meaningful JS beyond the Tailwind CDN config.

### Images

`images/` is fully tracked in git (`.gitignore` only excludes OS noise). The client logo carousel in `index.html` uses `logo_0.jpg`–`logo_5.jpg`, which are produced by the crop scripts from `5logo.jpg`. The AI illustration PNGs (`ai_abstract.png`, `ai_brain.png`, etc.) are standalone assets used directly in `index.html` via `<img>` tags.

## Branch Naming

Feature branches follow the pattern `claude/kebab-case-description-randomSuffix` (e.g. `claude/new-page-vibecodeweb-837hT`). `main` is the production branch.

---

## Instagram Automation (`ig-automation/`)

A review-gated publishing pipeline for **@vibecodeweb.in** using the official Instagram Graph API v21.0.

### Architecture
```
ig-automation/
├─ config.php             ← GITIGNORED — secrets live here only
├─ config.sample.php      ← committed template
├─ queue.json             ← content queue (draft → pending_review → approved → published)
├─ publisher.php          ← cron: publishes approved+due items
├─ insights.php           ← cron: pulls weekly metrics
├─ seed_content.php       ← seeds queue + regenerates post images
├─ review.php             ← local approval dashboard
├─ lib/graph.php          ← Graph API wrapper
├─ lib/queue.php          ← queue helpers
└─ assets-generator/
   └─ generate.php        ← PHP GD 1080×1080 branded image generator
ig-assets/                ← generated PNGs (committed, must be public on GlobeHost)
```

### Meta credentials (never commit — config.php only)
- **IG account**: @vibecodeweb.in · IG Business Account ID: `17841423972351765`
- **Facebook Page**: Vibecodeweb · Page ID: `1177970148735385`
- **Meta app**: Samya Content Tools · App ID: `1537383894700364`
- **System user**: vibecodeweb-publisher (ID: `61589583827535`) · never-expiring token
- **Business portfolio**: Vibecodeweb.in (ID: `1006474768411908`)

### GlobeHost production server
| Detail | Value |
|---|---|
| Server path | `/home1/vibec/public_html/ig-automation/` |
| PHP binary | `/usr/local/bin/php` |
| Review dashboard | `https://vibecodeweb.in/ig-automation/review.php` (Basic Auth: user `rehansh`) |
| config.php on server | `dry_run = false` — server always publishes live |
| `.htpasswd` | gitignored — on server only, never commit |

### Cron jobs (GlobeHost cPanel — active)
| Job | Schedule | Command |
|---|---|---|
| Publisher | Hourly (`0 * * * *`) | `/usr/local/bin/php /home1/vibec/public_html/ig-automation/publisher.php >> .../logs/cron.log 2>&1` |
| Insights | Sunday 3:30 UTC (`30 3 * * 0`) | `/usr/local/bin/php /home1/vibec/public_html/ig-automation/insights.php >> .../logs/cron.log 2>&1` |

### Workflow (how to post)
1. Open `https://vibecodeweb.in/ig-automation/review.php` → login `rehansh`
2. Approve a post → status flips to `approved`
3. Hourly cron publishes it automatically — no further action needed

### Local commands (XAMPP — dry_run=true, safe)
```bash
# Verify API connection
I:\xampp\php\php.exe ig-automation/insights.php

# Dry-run publisher (no live posts)
I:\xampp\php\php.exe ig-automation/publisher.php

# Regenerate all post images after brand/handle changes
I:\xampp\php\php.exe ig-automation/seed_content.php --images
```

### Rules
- `config.php` and `.htpasswd` are gitignored — **never commit either**
- Local = `dry_run = true`; GlobeHost = `dry_run = false`
- No follow/unfollow/mass-DM automation — Instagram ToS violation
- After regenerating images locally → push to git → re-upload `ig-assets/` to GlobeHost

### Status (2026-06-07) — FULLY LIVE ✅
- ✅ Meta app + system user + never-expiring token
- ✅ Instagram @vibecodeweb.in connected to Facebook Page
- ✅ 14 posts queued, all images at `@vibecodeweb.in`
- ✅ ig-assets live at `https://vibecodeweb.in/ig-assets/`
- ✅ Publisher + Insights crons active on GlobeHost
- ✅ API verified from GlobeHost: `@vibecodeweb.in · 1 follower`
- ✅ Review dashboard password-protected (user: `rehansh`)
