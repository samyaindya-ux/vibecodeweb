<?php
/**
 * VibeCodeWeb Instagram automation — configuration TEMPLATE.
 *
 * Copy this file to `config.php` (which is gitignored) and fill in the real values
 * obtained during the one-time Meta setup (see README.md, Phase 1).
 *
 * NEVER commit config.php — it holds your access token and app secret.
 */

return [
    // ---- Instagram Graph API credentials (from Meta setup) -------------------
    // Long-lived access token (prefer a Business Manager System User token).
    'access_token'        => 'PASTE_LONG_LIVED_TOKEN_HERE',

    // Your Instagram Business Account ID (numeric).
    // Get via: GET /me/accounts -> page -> instagram_business_account.id
    'ig_business_id'      => 'PASTE_IG_BUSINESS_ACCOUNT_ID_HERE',

    // Meta app credentials (used only for token refresh / debugging).
    'app_id'              => 'PASTE_APP_ID_HERE',
    'app_secret'         => 'PASTE_APP_SECRET_HERE',

    // Graph API version pinned for stability.
    'graph_version'       => 'v21.0',

    // ---- Database (posts + login) --------------------------------------------
    // On GlobeHost these use the cPanel-prefixed names (e.g. vibec_igauto).
    'db_host'             => 'localhost',
    'db_name'             => 'vibec_igauto',
    'db_user'             => 'vibec_iguser',
    'db_pass'             => 'CHANGE_ME',

    // ---- Asset hosting -------------------------------------------------------
    // Public base URL where generated post images are reachable (required by the
    // Graph API — it fetches image_url over the public internet).
    'assets_base_url'     => 'https://vibecodeweb.in/ig-assets/',

    // ---- Behaviour -----------------------------------------------------------
    // Safety switch: when true, publisher logs what it WOULD do but never posts.
    'dry_run'             => true,

    // Local timezone for scheduling decisions.
    'timezone'            => 'Asia/Kolkata',

    // Absolute/relative path to the content queue.
    'queue_path'          => __DIR__ . '/queue.json',

    // Where to write logs.
    'log_dir'             => __DIR__ . '/logs',
];
