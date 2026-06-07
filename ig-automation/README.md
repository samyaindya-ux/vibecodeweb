# VibeCodeWeb — Instagram Automation

A ToS-safe, review-gated auto-posting system for **@samya_vibecodeweb**.
It generates on-brand posts, lets you approve them, and publishes via the official
**Instagram Graph API** on a schedule. No follow/unfollow bots, no bought followers — those
get accounts banned.

## How it flows

```
seed_content.php  ──►  queue.json (pending_review)  ──►  review.php (you Approve)
                                                              │
                                          approved + due ─────▼
                                                         publisher.php (cron) ──► Instagram
insights.php (cron) ──► weekly metrics report ──► informs next week's content
```

## Files
| File | Role |
|---|---|
| `config.sample.php` | Template — copy to `config.php` (gitignored) and fill in. |
| `config.php` | Your secrets (token, IG id). **Never committed.** |
| `lib/graph.php` | Instagram Graph API wrapper. |
| `lib/queue.php` | Safe read/write of `queue.json`. |
| `seed_content.php` | Generates branded images + enqueues posts (idempotent). |
| `assets-generator/generate.php` | PHP-GD 1080×1080 branded image maker. |
| `review.php` | **Approval dashboard** → http://localhost/vibecodeweb/ig-automation/review.php |
| `publisher.php` | Cron: publishes approved + due posts (`--dry-run` / `--live`). |
| `insights.php` | Cron: pulls metrics into `logs/insights-*.json`. |
| `queue.json` | The content queue. |
| `../ig-assets/` | Generated images, **publicly hosted** at vibecodeweb.in/ig-assets/. |

PHP CLI on this machine: `I:\xampp\php\php.exe`.

---

## ONE-TIME SETUP (Phase 1) — Meta app + token

The Graph API needs a Business account, a Facebook Page, a Meta app, and a token.
Do these once (Claude can drive the browser and guide you; you handle login/2FA):

1. **Instagram → Professional → Business** account (done in the IG mobile app).
2. **Create / pick a Facebook Page** and **link your Instagram account to it**
   (Page → Settings → Linked accounts → Instagram).
3. Go to **developers.facebook.com → My Apps → Create App → type "Business"**.
4. In the app, **Add Product → Instagram Graph API** (and *Facebook Login* if prompted).
5. Open **Graph API Explorer**, select your app, and generate a **User token** with scopes:
   `instagram_basic`, `instagram_content_publish`, `pages_show_list`,
   `pages_read_engagement`, `business_management`.
6. **Exchange for a long-lived token** (≈60 days), or — better — create a
   **System User** in Business Settings and generate a **non-expiring System User token**.
7. Get your **IG Business Account ID**:
   `GET /me/accounts` → find your Page → `instagram_business_account { id }`.
8. `cp config.sample.php config.php` and paste in: `access_token`, `ig_business_id`,
   `app_id`, `app_secret`.
9. **Verify:** `I:\xampp\php\php.exe insights.php` should print `@samya_vibecodeweb · followers: …`.

> Token refresh: long-lived user tokens expire ~60 days. Re-run the exchange, or use a
> System User token to avoid this. (A refresh helper can be added later.)

---

## DAILY / WEEKLY USE

- **Generate more content:** add entries to `$POSTS` in `seed_content.php`, then
  `I:\xampp\php\php.exe seed_content.php`. (The weekly routine does this for you.)
- **Approve:** open `review.php`, review image + caption, set time, click **Approve**.
- **Publish:** the cron runs `publisher.php` hourly and posts approved + due items.
  Flip `dry_run` to `false` in `config.php` (or run `publisher.php --live`) when ready to go live.

## Scheduling (Phase 5)
- **Preferred:** GlobeHost cron — `php /path/publisher.php` hourly; `php /path/insights.php` weekly.
  (Always-on, and the images are served from the same host.)
- **Fallback (local):** a Windows Scheduled Task running `I:\xampp\php\php.exe publisher.php`
  hourly, mirroring the existing `autopull-all.bat` task. Only fires while the PC is on.

## Safety
- `config.php`, `logs/`, and token files are gitignored.
- Nothing publishes until **you** approve it in `review.php`.
- We never automate following, liking, commenting, or DMing strangers.
