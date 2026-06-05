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

Requirements: `pip install Pillow` for the Python scripts; `System.Drawing` assembly (Windows) for the PowerShell scripts.

## Architecture

### Two independent HTML pages

The repo contains two self-contained pages that share no code:

- **`index.html`** (~80 KB) — the primary production page. Elaborate dark-theme design using Outfit + Playfair Display fonts and FontAwesome icons. Sections: hero, services, client logo carousel (auto-scrolling), testimonials, blog teasers, contact modal. Includes a JS-powered dark/light mode toggle.
- **`index2.html`** (~30 KB) — a newer, cleaner redesign using Material Design 3 color tokens and Google Material Symbols icons. Hero images are served from Google's CDN (`lh3.googleusercontent.com`) rather than `images/`. Hardcodes `<html class="dark">` with no toggle.

Neither page imports the other or shares assets.

### Tailwind CSS via CDN — no local build

Both pages load Tailwind from `https://cdn.tailwindcss.com`. There is no `package.json`, no Tailwind CLI, and no PostCSS pipeline. Tailwind configuration (custom colors, font families, spacing tokens) lives in an inline `<script>` block near the top of each HTML file.

The two pages use **completely different color token sets**:
- `index.html` — tokens like `brand-dark`, `neon-blue`, `cyber-purple`, with `darkMode: 'class'`
- `index2.html` — Material Design 3-style semantic tokens (`surface`, `on-surface`, `primary-container`, `electric-purple`, `neon-green`, etc.)

When editing colors or adding new Tailwind utilities, update the `tailwind.config` block inside the specific file being changed — changes to one file's config do not affect the other.

### JavaScript

All JS is inline at the bottom of each HTML file inside `<script>` tags. There are no external `.js` files. `index.html` handles: mobile nav toggle, dark/light mode switch, logo carousel animation, scroll-reveal effects, and a contact form modal. `index2.html` has no meaningful JS beyond the Tailwind CDN config.

### Images

`images/` is fully tracked in git (`.gitignore` only excludes OS noise). The client logo carousel in `index.html` uses `logo_0.jpg`–`logo_5.jpg`, which are produced by the crop scripts from `5logo.jpg`. The AI illustration PNGs (`ai_abstract.png`, `ai_brain.png`, etc.) are standalone assets used directly in `index.html` via `<img>` tags.

## Branch Naming

Feature branches follow the pattern `claude/kebab-case-description-randomSuffix` (e.g. `claude/new-page-vibecodeweb-837hT`). `main` is the production branch.
