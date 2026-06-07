<?php
/**
 * Content engine — seed/extend the queue with on-brand posts.
 *
 *   php seed_content.php           # add any missing posts + generate their images
 *   php seed_content.php --images  # (re)generate images only
 *
 * Idempotent: posts already present in queue.json (by id) are skipped, so this
 * is safe to re-run and is what the weekly "content brain" routine calls after
 * adding new entries to $POSTS below.
 *
 * Captions/hashtags mirror INSTAGRAM_SETUP.md (the launch kit).
 */

require __DIR__ . '/lib/queue.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/assets-generator/generate.php';

if (!file_exists(__DIR__ . '/config.php')) {
    fwrite(STDERR, "Missing config.php — fill in credentials (including db_*) first.\n");
    exit(1);
}
$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');

// Rotating hashtag blocks (Set A/B/C from the kit).
$TAGS = [
'A' => '#artificialintelligence #aiautomation #aitools #chatgpt #claudeai #geminiai #automation #machinelearning #futureofwork #digitaltransformation #aiforbusiness #techindia #startupindia #madeinindia #vibecodeweb',
'B' => '#webdevelopment #saas #saasdevelopment #chatbot #aichatbot #posystem #softwaredevelopment #customsoftware #appdevelopment #apiintegration #nocode #buildinpublic #indiandevelopers #techstartup #vibecodeweb',
'C' => '#smallbusinessindia #indianbusiness #businessgrowth #digitalindia #smbindia #entrepreneurindia #startupindia #localbusiness #businessautomation #growyourbusiness #affordabletech #indianstartups #makeinindia #businesstools #vibecodeweb',
];

/**
 * Each post: id, type, image headline+accent, caption, tag set.
 * (Carousels/reels can list multiple media; here we lead with single-image posts
 *  which are the reliably-automated core. Reels are added with supplied clips.)
 */
$POSTS = [
  ['id'=>'p01','accent'=>'neon','head'=>'Meet VibeCode.','sub'=>'AI · Web · SaaS — Built in India',
   'cap'=>"Meet VibeCode. 🚀\nWe build AI automations, websites, and SaaS for Indian businesses — proudly built in India, made to compete globally. 🇮🇳\nFollow along for AI tips, builds, and behind-the-scenes. 👇\n💬 Free consult on WhatsApp: +91 9477443425",'tags'=>'A'],

  ['id'=>'p02','accent'=>'primary','head'=>'9 ways we grow your business with AI','sub'=>'Swipe to see all 9 →',
   'cap'=>"9 ways we help your business win with AI 👇\n1 Retail & POS  2 AI automations  3 Document analyzer  4 SaaS dev  5 Content generators  6 AI strategy  7 Claude API integrations  8 AI chatbots  9 Claude Code consulting\nWhich one would move the needle for you? Comment the number. 👇",'tags'=>'B'],

  ['id'=>'p03','accent'=>'saffron','head'=>'Still doing this by hand? Let AI do it.','sub'=>'Save up to 80% of busywork',
   'cap'=>"Still doing this by hand? Let AI do it. ⚡\nRepetitive tasks — invoices, follow-ups, data entry — can run 24/7 without you.\nWant one built for your business? DM \"AUTO\". 💬",'tags'=>'A'],

  ['id'=>'p04','accent'=>'saffron','head'=>'A POS that understands your shop.','sub'=>'Inventory · Billing · Reports',
   'cap'=>"A POS that actually understands your shop. 🛒\nSmart, reliable point-of-sale + retail management — built to scale with you.\n💬 Ask us how: link in bio / WhatsApp +91 9477443425",'tags'=>'C'],

  ['id'=>'p05','accent'=>'primary','head'=>'Your business, answering customers at 2 AM.','sub'=>'White-label AI chatbots',
   'cap'=>"Your business, answering customers at 2 AM. 🤖\nWhite-label AI chatbots for clinics, real-estate, coaching & more — 24/7 replies, no custom dev cost.\nDM \"BOT\" to see a demo. 💬",'tags'=>'B'],

  ['id'=>'p06','accent'=>'neon','head'=>'No enterprise price tag. Promise.','sub'=>'Starter ₹5k · Pro AI ₹10k · Custom',
   'cap'=>"No enterprise price tag. Promise. 💸\n🟢 Starter Web — ₹5,000+\n🔵 Pro AI Business — ₹10,000+ (site + AI chatbot + POS + free domain)\n🟣 Custom App — let's talk\nWorld-class AI + web, priced for Indian businesses. 🇮🇳 👇 link in bio",'tags'=>'C'],

  ['id'=>'p07','accent'=>'saffron','head'=>'Proudly built in India. Made to win globally.','sub'=>'AI-first development',
   'cap'=>"Proudly built in India. Made to win globally. 🇮🇳\nWe use AI-first development (ChatGPT · Claude · Gemini) to ship faster and cheaper than traditional agencies — so Indian businesses get world-class tech without the wait. 🚀",'tags'=>'A'],

  ['id'=>'p08','accent'=>'neon','head'=>'Drowning in documents? AI reads them for you.','sub'=>'Extract · Summarize · Organize',
   'cap'=>"Drowning in documents? AI reads them for you. 📄\nOur Document Analyzer extracts insights, summarizes, and organizes messy files in seconds.\nDM \"DOCS\" to learn more. 💬",'tags'=>'B'],

  ['id'=>'p09','accent'=>'neon','head'=>"Ready to scale with AI? Let's talk — free.",'sub'=>'WhatsApp +91 9477443425',
   'cap'=>"Ready to scale with AI? Let's talk — free. 🤝\nWhether it's a website, an automation, or a full AI build, your first consult is on us.\n💬 WhatsApp: +91 9477443425  📧 samya.indya@gmail.com  🔗 vibecodeweb.in",'tags'=>'C'],

  ['id'=>'p10','accent'=>'primary','head'=>'AI tip: automate your follow-ups first.','sub'=>'The highest-ROI automation',
   'cap'=>"AI tip of the week 💡\nThe #1 automation for most small businesses? Follow-ups.\nLeads go cold fast — an AI that replies instantly and nudges politely can lift conversions without extra staff.\nWant it set up? DM \"AUTO\". 💬",'tags'=>'A'],

  ['id'=>'p11','accent'=>'saffron','head'=>'We build it with AI — so you pay less.','sub'=>'Our unfair advantage',
   'cap'=>"Why are we faster AND cheaper? 🤔\nBecause we build with AI tooling (Claude Code, ChatGPT, Gemini) — less manual grind, more shipped features.\nLeaner stack, lower cost, same quality. That saving goes to you. 🇮🇳",'tags'=>'B'],

  ['id'=>'p12','accent'=>'neon','head'=>'From idea to landing page in days, not months.','sub'=>'Custom web that converts',
   'cap'=>"From idea to live landing page in days, not months. ⚡\nMobile-responsive, SEO-ready, and built to convert visitors into customers.\nStarter from ₹5,000. 👇 link in bio",'tags'=>'C'],

  ['id'=>'p13','accent'=>'primary','head'=>'Connect Claude to the tools you already use.','sub'=>'CRM · ERP · Support desk',
   'cap'=>"Already have a CRM, ERP, or support desk? 🔌\nWe plug Claude's AI into your existing systems — turning legacy tools into intelligent, AI-powered workflows. No rip-and-replace.\nDM \"API\" to explore. 💬",'tags'=>'B'],

  ['id'=>'p14','accent'=>'saffron','head'=>'Your competitors are adopting AI. Are you?','sub'=>'AI strategy & consulting',
   'cap'=>"Your competitors are adopting AI. Are you? 🏁\nWe help you pick the right AI moves for YOUR business — no hype, just ROI.\nBook a free strategy call. 💬 +91 9477443425",'tags'=>'A'],
];

$onlyImages = in_array('--images', $argv ?? [], true);
$queue = new Queue(vcw_db($config));
$existing = array_column($queue->all(), null, 'id');
$items = $queue->all();

// Schedule: one per day at 10:30 IST starting tomorrow.
$slot = strtotime('tomorrow 10:30');
$added = 0; $imgs = 0;

foreach ($POSTS as $i => $spec) {
    $file = $spec['id'] . '.png';
    $outPath = __DIR__ . '/../ig-assets/' . $file;

    // (Re)generate image.
    if ($onlyImages || !is_file($outPath)) {
        vcw_generate_image([
            'headline' => $spec['head'],
            'sub'      => $spec['sub'] ?? null,
            'accent'   => $spec['accent'],
            'out'      => $outPath,
        ]);
        $imgs++;
    }

    if ($onlyImages) continue;
    if (isset($existing[$spec['id']])) continue; // already queued

    $items[] = [
        'id'           => $spec['id'],
        'type'         => 'image',
        'caption'      => $spec['cap'],
        'hashtags'     => $TAGS[$spec['tags']],
        'media'        => [$file],
        'scheduled_at' => date('c', $slot + $added * 86400),
        'status'       => 'pending_review',
        'created_at'   => date('c'),
    ];
    $added++;
}

if (!$onlyImages) $queue->save($items);

echo "Generated {$imgs} image(s); added {$added} new post(s) to the queue.\n";
echo "Review them at: http://localhost/vibecodeweb/ig-automation/review.php\n";
