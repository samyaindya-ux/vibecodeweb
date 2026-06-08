<?php
// VibeCodeWeb AI Tools — key lives in tools_config.php (gitignored)
if (file_exists(__DIR__ . '/tools_config.php')) {
    require_once __DIR__ . '/tools_config.php';
}
if (!defined('ANTHROPIC_KEY')) define('ANTHROPIC_KEY', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'ig') {
        $topic  = strip_tags($_POST['topic']  ?? '');
        $niche  = strip_tags($_POST['niche']  ?? '');
        $tone   = strip_tags($_POST['tone']   ?? 'casual');
        $type   = strip_tags($_POST['type']   ?? 'post');
        $prompt = "You are an expert Instagram content strategist. Generate content for:\nProduct/Topic: $topic\nBusiness Niche: $niche\nTone: $tone\nContent Type: $type\n\nRespond ONLY with valid JSON (no markdown, no backticks):\n{\"caption\":\"engaging caption with emojis\",\"hashtags\":\"#tag1 #tag2 ... (15 tags)\",\"cta\":\"strong call to action\",\"best_time\":\"best time to post and why\",\"hook\":\"attention-grabbing first line\"}";

    } elseif ($action === 'email') {
        $etype     = strip_tags($_POST['etype']     ?? 'cold_outreach');
        $sender    = strip_tags($_POST['sender']    ?? '');
        $recipient = strip_tags($_POST['recipient'] ?? '');
        $company   = strip_tags($_POST['company']   ?? '');
        $goal      = strip_tags($_POST['goal']      ?? '');
        $tone      = strip_tags($_POST['tone']      ?? 'professional');
        $prompt = "You are an expert email copywriter. Write a $etype email.\nFrom: $sender (VibeCodeWeb)\nTo: $recipient, $company\nGoal: $goal\nTone: $tone\n\nRespond ONLY with valid JSON (no markdown, no backticks):\n{\"subject\":\"compelling subject line\",\"body\":\"full email body with line breaks as \\\\n\",\"ps\":\"optional P.S. line\"}";
    } else {
        echo json_encode(['error' => 'Invalid action']); exit;
    }

    $payload = json_encode([
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 1024,
        'messages'   => [['role' => 'user', 'content' => $prompt]]
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . ANTHROPIC_KEY,
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $res     = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($res) {
        $json = json_decode($res, true);
        $text = $json['content'][0]['text'] ?? '{}';
        preg_match('/\{.*\}/s', $text, $m);
        $out = json_decode($m[0] ?? '{}', true);
        echo json_encode(['ok' => true, 'data' => $out]);
    } else {
        echo json_encode(['error' => 'API call failed: ' . $curlErr]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>AI Power Tools Demo — VibeCodeWeb.in</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script>
tailwind.config = {
  darkMode:'class',
  theme:{ extend:{
    fontFamily:{ sans:['Outfit','sans-serif'], serif:['Playfair Display','serif'] },
    colors:{
      brand:{ dark:'#0f172a', darker:'#020617', card:'#1e293b',
              primary:'#3b82f6', accent:'#8b5cf6', neon:'#10b981',
              saffron:'#f97316', indiaGreen:'#15803d' }
    }
  }}
}
</script>
<style>
  body{ background:#020617; color:#f8fafc; overflow-x:hidden; }
  ::-webkit-scrollbar{ width:6px } ::-webkit-scrollbar-track{ background:#0f172a }
  ::-webkit-scrollbar-thumb{ background:#3b82f6; border-radius:3px }
  .glass{ background:rgba(30,41,59,.5); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,.08); }
  .glass-dark{ background:rgba(2,6,23,.7); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,.06); }
  .glass-nav{ background:rgba(15,23,42,.7); backdrop-filter:blur(16px); border-bottom:1px solid rgba(255,255,255,.05); }
  .text-grad{ background:linear-gradient(135deg,#3b82f6,#8b5cf6,#10b981); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .text-india{ background:linear-gradient(90deg,#f97316,#fff,#10b981); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .glow-btn{ transition:all .3s; }
  .glow-btn:hover{ box-shadow:0 0 25px rgba(59,130,246,.4); transform:translateY(-2px); }
  .glow-neon:hover{ box-shadow:0 0 25px rgba(16,185,129,.4); transform:translateY(-2px); }
  .tool-tab{ transition:all .3s; border-bottom:2px solid transparent; }
  .tool-tab.active{ border-bottom-color:#3b82f6; color:#fff; }
  .tool-panel{ display:none; } .tool-panel.active{ display:block; }
  .input-field{ background:rgba(15,23,42,.8); border:1px solid rgba(255,255,255,.1); color:#fff; transition:border-color .3s; }
  .input-field:focus{ outline:none; border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.15); }
  .input-field option{ background:#1e293b; }
  @keyframes shimmer{ 0%{ background-position:-200% 0 } 100%{ background-position:200% 0 } }
  .shimmer{ background:linear-gradient(90deg,rgba(255,255,255,0) 0%,rgba(255,255,255,.05) 50%,rgba(255,255,255,0) 100%); background-size:200% 100%; animation:shimmer 1.5s infinite; }
  @keyframes fadeUp{ from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
  .fade-up{ animation:fadeUp .5s ease forwards; }
  @keyframes spin{ to{transform:rotate(360deg)} }
  .spin{ animation:spin 1s linear infinite; }
  .phone-frame{ background:linear-gradient(145deg,#1e293b,#0f172a); border:2px solid rgba(255,255,255,.15); border-radius:2.5rem; padding:1rem; box-shadow:0 25px 60px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.1); }
  .phone-screen{ background:#000; border-radius:2rem; overflow:hidden; }
  .ig-header{ background:#000; padding:.75rem 1rem; display:flex; align-items:center; gap:.75rem; border-bottom:1px solid #262626; }
  .ig-avatar{ width:2rem; height:2rem; border-radius:50%; background:linear-gradient(45deg,#f97316,#8b5cf6,#3b82f6); }
  .email-frame{ background:#1e293b; border:1px solid rgba(255,255,255,.1); border-radius:1rem; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,.4); }
  .email-header{ background:#0f172a; padding:.75rem 1.25rem; border-bottom:1px solid rgba(255,255,255,.08); }
  .typewriter::after{ content:'|'; animation:blink .7s infinite; }
  @keyframes blink{ 0%,100%{opacity:1} 50%{opacity:0} }
  .result-card{ animation:fadeUp .4s ease; }
  .copy-btn{ transition:all .2s; }
  .copy-btn:hover{ transform:scale(1.05); }
  .copy-btn.copied{ background:rgba(16,185,129,.2)!important; border-color:#10b981!important; color:#10b981!important; }
  .orb{ position:absolute; border-radius:50%; mix-blend-mode:screen; filter:blur(120px); pointer-events:none; animation:float 6s ease-in-out infinite; }
  @keyframes float{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-20px)} }
  .tag-pill{ display:inline-block; background:rgba(59,130,246,.15); border:1px solid rgba(59,130,246,.3); color:#93c5fd; border-radius:9999px; padding:.2rem .65rem; font-size:.8rem; margin:.15rem; transition:all .2s; cursor:default; }
  .tag-pill:hover{ background:rgba(59,130,246,.3); }
</style>
</head>
<body class="antialiased font-sans">

<!-- Navbar -->
<nav class="fixed w-full z-50 glass-nav">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-20">
      <a href="/" class="text-xl font-bold flex items-center gap-2">
        VibeCode<span class="text-india">Web.in</span>
      </a>
      <div class="flex items-center gap-4">
        <a href="/#services" class="hidden md:block text-gray-400 hover:text-white text-sm transition-colors">Services</a>
        <a href="/#pricing"  class="hidden md:block text-gray-400 hover:text-white text-sm transition-colors">Pricing</a>
        <a href="/#contact"  class="px-5 py-2 rounded-full text-sm font-semibold text-white bg-gradient-to-r from-brand-saffron to-brand-primary hover:opacity-90 transition-opacity">
          Contact <i class="fa-solid fa-paper-plane ml-1 text-xs"></i>
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="relative pt-36 pb-16 overflow-hidden text-center">
  <div class="orb w-96 h-96 bg-brand-saffron opacity-10 top-10 left-10 animate-[float_8s_ease-in-out_infinite]"></div>
  <div class="orb w-80 h-80 bg-brand-primary opacity-10 top-20 right-10 animate-[float_6s_ease-in-out_2s_infinite]"></div>
  <div class="max-w-4xl mx-auto px-4 relative z-10">
    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand-neon/30 bg-brand-neon/10 text-brand-neon text-xs font-semibold mb-6">
      <i class="fa-solid fa-flask animate-pulse"></i> Live AI Demo — Powered by Claude
    </div>
    <h1 class="text-5xl md:text-7xl font-serif font-bold mb-5 leading-tight">
      AI Power <span class="text-grad">Tools Demo</span>
    </h1>
    <p class="text-lg text-gray-400 max-w-2xl mx-auto mb-3">
      Experience the future of content creation. These tools are a small glimpse of what we build for our clients — fully integrated, white-labelled, and custom-trained.
    </p>
    <p class="text-sm text-brand-saffron/80 font-medium">
      <i class="fa-solid fa-bolt"></i> Results generated live using Claude AI
    </p>
  </div>
</section>

<!-- Tabs -->
<div class="max-w-6xl mx-auto px-4 mb-8">
  <div class="flex gap-1 glass rounded-2xl p-1.5 w-fit mx-auto">
    <button id="tab-ig" onclick="switchTab('ig')"
      class="tool-tab active flex items-center gap-2 px-7 py-3 rounded-xl text-sm font-semibold text-gray-300 hover:text-white transition-all">
      <i class="fa-brands fa-instagram text-pink-400"></i> IG Content Engine
    </button>
    <button id="tab-email" onclick="switchTab('email')"
      class="tool-tab flex items-center gap-2 px-7 py-3 rounded-xl text-sm font-semibold text-gray-300 hover:text-white transition-all">
      <i class="fa-solid fa-envelope text-brand-primary"></i> Email Composer
    </button>
  </div>
</div>

<!-- ============================================================ -->
<!-- IG TOOL -->
<!-- ============================================================ -->
<section id="panel-ig" class="tool-panel active max-w-6xl mx-auto px-4 pb-24">
  <div class="grid lg:grid-cols-2 gap-8 items-start">

    <!-- Form -->
    <div class="glass rounded-3xl p-8">
      <div class="flex items-center gap-3 mb-7">
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-pink-500 to-brand-saffron flex items-center justify-center">
          <i class="fa-brands fa-instagram text-white text-xl"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-white">Instagram Content Engine</h2>
          <p class="text-xs text-gray-400">Caption · Hashtags · Strategy · CTA</p>
        </div>
      </div>

      <form id="ig-form" class="space-y-5" onsubmit="generateIG(event)">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Product / Topic <span class="text-brand-saffron">*</span></label>
          <input type="text" id="ig-topic" required placeholder="e.g. Handmade silver jewellery, Yoga retreat, AI automation service..."
            class="input-field w-full rounded-xl px-4 py-3 text-sm placeholder-gray-500"/>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Business Niche</label>
          <input type="text" id="ig-niche" placeholder="e.g. Sustainable fashion, B2B SaaS, Health & Wellness..."
            class="input-field w-full rounded-xl px-4 py-3 text-sm placeholder-gray-500"/>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Tone</label>
            <select id="ig-tone" class="input-field w-full rounded-xl px-4 py-3 text-sm">
              <option value="casual & friendly">Casual & Friendly</option>
              <option value="professional & authoritative">Professional</option>
              <option value="inspirational & motivational">Inspirational</option>
              <option value="trendy & Gen-Z">Trendy / Gen-Z</option>
              <option value="luxurious & premium">Luxury / Premium</option>
              <option value="playful & humorous">Playful & Fun</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Content Type</label>
            <select id="ig-type" class="input-field w-full rounded-xl px-4 py-3 text-sm">
              <option value="product showcase post">Product Showcase</option>
              <option value="educational carousel">Educational Carousel</option>
              <option value="behind-the-scenes reel">Behind The Scenes</option>
              <option value="testimonial/social proof post">Testimonial</option>
              <option value="promotional offer post">Promo / Offer</option>
              <option value="engagement question story">Engagement Story</option>
            </select>
          </div>
        </div>
        <button type="submit" id="ig-btn"
          class="glow-neon w-full py-3.5 rounded-xl bg-gradient-to-r from-pink-600 to-brand-saffron text-white font-bold text-sm flex items-center justify-center gap-2 transition-all">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Content
        </button>
      </form>
    </div>

    <!-- Preview -->
    <div id="ig-result-wrap" class="space-y-5">
      <!-- Placeholder state -->
      <div id="ig-placeholder" class="glass rounded-3xl p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-pink-500/20 to-brand-saffron/20 flex items-center justify-center mx-auto mb-4 border border-white/10">
          <i class="fa-brands fa-instagram text-3xl text-pink-400"></i>
        </div>
        <p class="text-gray-400 text-sm">Fill in the form and click <span class="text-white font-semibold">Generate Content</span> to see a live AI-crafted Instagram post appear here — complete with caption, hashtags, and strategy.</p>
        <div class="mt-6 grid grid-cols-3 gap-3 text-xs text-gray-500">
          <div class="glass-dark rounded-xl p-3"><i class="fa-solid fa-pen-nib text-pink-400 mb-1 block"></i>Smart Caption</div>
          <div class="glass-dark rounded-xl p-3"><i class="fa-solid fa-hashtag text-brand-primary mb-1 block"></i>15 Hashtags</div>
          <div class="glass-dark rounded-xl p-3"><i class="fa-solid fa-clock text-brand-neon mb-1 block"></i>Best Time</div>
        </div>
      </div>

      <!-- Result (hidden until generated) -->
      <div id="ig-result" class="hidden result-card space-y-4">
        <!-- Phone mockup -->
        <div class="phone-frame max-w-xs mx-auto">
          <div class="phone-screen">
            <div class="ig-header">
              <div class="ig-avatar"></div>
              <div>
                <p class="text-white text-xs font-semibold leading-tight">vibecodeweb.in</p>
                <p class="text-gray-500 text-[10px]">Sponsored</p>
              </div>
              <i class="fa-solid fa-ellipsis text-gray-400 ml-auto text-xs"></i>
            </div>
            <div class="bg-gradient-to-br from-brand-primary/20 via-brand-accent/20 to-brand-saffron/20 aspect-square flex items-center justify-center">
              <div class="text-center p-4">
                <i class="fa-brands fa-instagram text-5xl text-white/30 mb-2"></i>
                <p class="text-white/50 text-xs">Your visual here</p>
              </div>
            </div>
            <div class="bg-black p-3">
              <div class="flex gap-4 text-gray-400 text-lg mb-2">
                <i class="fa-regular fa-heart hover:text-red-500 cursor-pointer transition-colors"></i>
                <i class="fa-regular fa-comment cursor-pointer hover:text-white transition-colors"></i>
                <i class="fa-regular fa-paper-plane cursor-pointer hover:text-white transition-colors"></i>
                <i class="fa-regular fa-bookmark ml-auto cursor-pointer hover:text-white transition-colors"></i>
              </div>
              <p class="text-white text-[11px] font-semibold mb-1">1,204 likes</p>
              <div id="ig-caption-preview" class="text-[11px] text-gray-200 leading-relaxed overflow-y-auto max-h-44 whitespace-pre-wrap"></div>
            </div>
          </div>
        </div>

        <!-- Hook -->
        <div class="glass rounded-2xl p-5">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-brand-saffron uppercase tracking-wider"><i class="fa-solid fa-bolt mr-1"></i>Hook Line</p>
            <button onclick="copyText('ig-hook-text')" class="copy-btn text-xs text-gray-400 hover:text-white border border-white/10 rounded-lg px-2 py-1 hover:border-white/30 transition-all"><i class="fa-regular fa-copy mr-1"></i>Copy</button>
          </div>
          <p id="ig-hook-text" class="text-white font-semibold text-sm italic"></p>
        </div>

        <!-- Caption -->
        <div class="glass rounded-2xl p-5">
          <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-pink-400 uppercase tracking-wider"><i class="fa-solid fa-pen-nib mr-1"></i>Caption</p>
            <button onclick="copyText('ig-caption-text')" class="copy-btn text-xs text-gray-400 hover:text-white border border-white/10 rounded-lg px-2 py-1 hover:border-white/30 transition-all"><i class="fa-regular fa-copy mr-1"></i>Copy</button>
          </div>
          <p id="ig-caption-text" class="text-gray-200 text-sm leading-relaxed whitespace-pre-wrap"></p>
        </div>

        <!-- Hashtags -->
        <div class="glass rounded-2xl p-5">
          <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-brand-primary uppercase tracking-wider"><i class="fa-solid fa-hashtag mr-1"></i>Hashtags</p>
            <button onclick="copyText('ig-hashtags-raw')" class="copy-btn text-xs text-gray-400 hover:text-white border border-white/10 rounded-lg px-2 py-1 hover:border-white/30 transition-all"><i class="fa-regular fa-copy mr-1"></i>Copy All</button>
          </div>
          <div id="ig-hashtags-pills"></div>
          <span id="ig-hashtags-raw" class="hidden"></span>
        </div>

        <!-- CTA + Time -->
        <div class="grid grid-cols-2 gap-4">
          <div class="glass rounded-2xl p-4">
            <p class="text-xs font-semibold text-brand-neon uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-pointer mr-1"></i>CTA</p>
            <p id="ig-cta" class="text-gray-200 text-sm"></p>
          </div>
          <div class="glass rounded-2xl p-4">
            <p class="text-xs font-semibold text-brand-accent uppercase tracking-wider mb-2"><i class="fa-regular fa-clock mr-1"></i>Best Time</p>
            <p id="ig-time" class="text-gray-200 text-sm"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================ -->
<!-- EMAIL TOOL -->
<!-- ============================================================ -->
<section id="panel-email" class="tool-panel max-w-6xl mx-auto px-4 pb-24">
  <div class="grid lg:grid-cols-2 gap-8 items-start">

    <!-- Form -->
    <div class="glass rounded-3xl p-8">
      <div class="flex items-center gap-3 mb-7">
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-primary to-brand-accent flex items-center justify-center">
          <i class="fa-solid fa-envelope-open-text text-white text-lg"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-white">AI Email Composer</h2>
          <p class="text-xs text-gray-400">Subject · Body · P.S. — ready to send</p>
        </div>
      </div>

      <form id="email-form" class="space-y-5" onsubmit="generateEmail(event)">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Email Type <span class="text-brand-saffron">*</span></label>
          <select id="email-type" class="input-field w-full rounded-xl px-4 py-3 text-sm">
            <option value="cold outreach">Cold Outreach</option>
            <option value="follow-up">Follow-Up</option>
            <option value="sales proposal">Sales Proposal</option>
            <option value="partnership inquiry">Partnership Inquiry</option>
            <option value="thank you">Thank You</option>
            <option value="re-engagement">Re-Engagement</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Your Name <span class="text-brand-saffron">*</span></label>
            <input type="text" id="email-sender" required placeholder="e.g. Samya"
              class="input-field w-full rounded-xl px-4 py-3 text-sm placeholder-gray-500"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Recipient Name <span class="text-brand-saffron">*</span></label>
            <input type="text" id="email-recipient" required placeholder="e.g. Rahul"
              class="input-field w-full rounded-xl px-4 py-3 text-sm placeholder-gray-500"/>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Recipient's Company</label>
          <input type="text" id="email-company" placeholder="e.g. TechCorp India, Local Boutique..."
            class="input-field w-full rounded-xl px-4 py-3 text-sm placeholder-gray-500"/>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Goal / Context <span class="text-brand-saffron">*</span></label>
          <textarea id="email-goal" required rows="3" placeholder="e.g. Pitch our AI chatbot service to reduce their support costs. They're a mid-sized e-commerce brand..."
            class="input-field w-full rounded-xl px-4 py-3 text-sm placeholder-gray-500 resize-none"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Tone</label>
          <select id="email-tone" class="input-field w-full rounded-xl px-4 py-3 text-sm">
            <option value="professional and concise">Professional & Concise</option>
            <option value="warm and conversational">Warm & Conversational</option>
            <option value="bold and direct">Bold & Direct</option>
            <option value="formal and respectful">Formal & Respectful</option>
            <option value="friendly and enthusiastic">Friendly & Enthusiastic</option>
          </select>
        </div>
        <button type="submit" id="email-btn"
          class="glow-btn w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-primary to-brand-accent text-white font-bold text-sm flex items-center justify-center gap-2 transition-all">
          <i class="fa-solid fa-paper-plane"></i> Compose Email
        </button>
      </form>
    </div>

    <!-- Preview -->
    <div id="email-result-wrap">
      <!-- Placeholder -->
      <div id="email-placeholder" class="glass rounded-3xl p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-brand-primary/10 flex items-center justify-center mx-auto mb-4 border border-brand-primary/20">
          <i class="fa-solid fa-envelope-open-text text-3xl text-brand-primary"></i>
        </div>
        <p class="text-gray-400 text-sm">Fill in the details and click <span class="text-white font-semibold">Compose Email</span> to generate a high-converting, personalized email — subject line, full body, and P.S. included.</p>
        <div class="mt-6 grid grid-cols-3 gap-3 text-xs text-gray-500">
          <div class="glass-dark rounded-xl p-3"><i class="fa-solid fa-heading text-brand-primary mb-1 block"></i>Subject Line</div>
          <div class="glass-dark rounded-xl p-3"><i class="fa-solid fa-align-left text-brand-accent mb-1 block"></i>Full Body</div>
          <div class="glass-dark rounded-xl p-3"><i class="fa-solid fa-star text-brand-saffron mb-1 block"></i>P.S. Hook</div>
        </div>
      </div>

      <!-- Result -->
      <div id="email-result" class="hidden result-card">
        <div class="email-frame">
          <!-- Email client header -->
          <div class="email-header">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-3 h-3 rounded-full bg-red-500"></div>
              <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
              <div class="w-3 h-3 rounded-full bg-green-500"></div>
              <span class="text-gray-500 text-xs ml-2">New Message</span>
            </div>
            <div class="space-y-2 text-xs">
              <div class="flex items-center gap-3 border-b border-white/5 pb-2">
                <span class="text-gray-500 w-12">From:</span>
                <span class="text-gray-300" id="email-from-display">you@company.com</span>
              </div>
              <div class="flex items-center gap-3 border-b border-white/5 pb-2">
                <span class="text-gray-500 w-12">To:</span>
                <span class="text-gray-300" id="email-to-display">recipient@company.com</span>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-gray-500 w-12">Subject:</span>
                <span class="text-white font-medium" id="email-subject-display"></span>
              </div>
            </div>
          </div>

          <!-- Email body -->
          <div class="p-6 bg-brand-darker/60">
            <p id="email-body-display" class="text-gray-200 text-sm leading-relaxed whitespace-pre-wrap"></p>
            <p id="email-ps-display" class="text-brand-saffron text-sm mt-4 italic"></p>
          </div>
        </div>

        <!-- Copy buttons -->
        <div class="grid grid-cols-2 gap-3 mt-4">
          <button onclick="copyEmailSubject()" id="copy-subject-btn"
            class="copy-btn glass rounded-xl py-3 text-sm font-medium text-gray-300 hover:text-white border border-white/10 hover:border-white/30 transition-all flex items-center justify-center gap-2">
            <i class="fa-regular fa-copy"></i> Copy Subject
          </button>
          <button onclick="copyFullEmail()" id="copy-full-btn"
            class="copy-btn glass rounded-xl py-3 text-sm font-medium text-gray-300 hover:text-white border border-white/10 hover:border-white/30 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-copy"></i> Copy Full Email
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Strip -->
<section class="py-16 relative overflow-hidden border-t border-white/5">
  <div class="absolute inset-0 bg-gradient-to-r from-brand-saffron/5 via-brand-primary/5 to-brand-neon/5"></div>
  <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
    <p class="text-brand-neon text-sm font-bold uppercase tracking-widest mb-3"><i class="fa-solid fa-rocket mr-1"></i> Like What You See?</p>
    <h2 class="text-3xl md:text-5xl font-serif font-bold mb-4">Get These Tools <span class="text-india">Built for You</span></h2>
    <p class="text-gray-400 mb-8">Fully custom-branded, trained on your data, integrated into your website or app. No API keys, no hassle — we handle everything.</p>
    <a href="https://wa.me/919477443425"
      class="inline-flex items-center gap-2 px-8 py-4 rounded-full bg-brand-neon text-brand-darker font-bold hover:bg-emerald-400 transition-all shadow-[0_0_25px_rgba(16,185,129,.4)] hover:shadow-[0_0_35px_rgba(16,185,129,.6)] hover:-translate-y-1">
      <i class="fa-brands fa-whatsapp text-xl"></i> Chat with us on WhatsApp
    </a>
  </div>
</section>

<!-- Footer -->
<footer class="border-t border-white/10 bg-brand-darker py-8">
  <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
    <a href="/" class="font-bold text-lg">VibeCode<span class="text-india">Web.in</span></a>
    <p class="text-gray-500">© <span id="yr"></span> VibeCodeWeb.in — AI Demo Tools</p>
    <a href="https://wa.me/919477443425" class="text-brand-neon hover:text-emerald-400 transition-colors">
      <i class="fa-brands fa-whatsapp mr-1"></i>+91 94774 43425
    </a>
  </div>
</footer>

<!-- Floating WhatsApp -->
<a href="https://wa.me/919477443425" target="_blank"
  class="fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-tr from-brand-neon to-brand-indiaGreen rounded-full flex items-center justify-center text-white text-3xl shadow-[0_0_20px_rgba(16,185,129,.5)] hover:scale-110 transition-all z-50 animate-bounce"
  style="animation-duration:3s">
  <i class="fa-brands fa-whatsapp"></i>
</a>

<script>
document.getElementById('yr').textContent = new Date().getFullYear();

// Tab switching
function switchTab(tab) {
  ['ig','email'].forEach(t => {
    document.getElementById('tab-'+t).classList.toggle('active', t===tab);
    document.getElementById('panel-'+t).classList.toggle('active', t===tab);
  });
}

// IG Generate
async function generateIG(e) {
  e.preventDefault();
  const btn = document.getElementById('ig-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-circle-notch spin mr-2"></i>Generating...';

  const fd = new FormData();
  fd.append('action','ig');
  fd.append('topic', document.getElementById('ig-topic').value);
  fd.append('niche', document.getElementById('ig-niche').value);
  fd.append('tone',  document.getElementById('ig-tone').value);
  fd.append('type',  document.getElementById('ig-type').value);

  try {
    const res  = await fetch('tools.php', {method:'POST', body:fd});
    const json = await res.json();
    if (json.ok && json.data) {
      const d = json.data;
      document.getElementById('ig-placeholder').style.display = 'none';
      const result = document.getElementById('ig-result');
      result.classList.remove('hidden');

      document.getElementById('ig-hook-text').textContent    = d.hook    || '';
      document.getElementById('ig-caption-text').textContent = d.caption || '';

      // Phone preview: hook + caption + hashtags, IG-style
      const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
      const phoneTags = (d.hashtags||'').split(/\s+/).filter(t=>t.startsWith('#'));
      document.getElementById('ig-caption-preview').innerHTML =
        '<span class="font-semibold text-white">vibecodeweb.in</span> ' +
        (d.hook    ? '<span class="italic text-gray-300">' + esc(d.hook) + '</span>\n\n' : '') +
        esc(d.caption||'') +
        (phoneTags.length ? '\n\n<span class="text-[#58b0e0]">' + esc(phoneTags.join(' ')) + '</span>' : '');
      document.getElementById('ig-cta').textContent            = d.cta       || '';
      document.getElementById('ig-time').textContent           = d.best_time || '';

      // Hashtag pills
      const tags = (d.hashtags||'').split(/\s+/).filter(t=>t.startsWith('#'));
      document.getElementById('ig-hashtags-raw').textContent   = tags.join(' ');
      document.getElementById('ig-hashtags-pills').innerHTML   = tags.map(t=>`<span class="tag-pill">${t}</span>`).join('');
    } else {
      showError('ig', json.error || 'Generation failed — check API key in tools.php');
    }
  } catch(err) { showError('ig', 'Request failed: ' + err.message); }

  btn.disabled = false;
  btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Generate Content';
}

// Email Generate
async function generateEmail(e) {
  e.preventDefault();
  const btn = document.getElementById('email-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-circle-notch spin mr-2"></i>Composing...';

  const sender    = document.getElementById('email-sender').value;
  const recipient = document.getElementById('email-recipient').value;
  const company   = document.getElementById('email-company').value;

  const fd = new FormData();
  fd.append('action',    'email');
  fd.append('etype',     document.getElementById('email-type').value);
  fd.append('sender',    sender);
  fd.append('recipient', recipient);
  fd.append('company',   company);
  fd.append('goal',      document.getElementById('email-goal').value);
  fd.append('tone',      document.getElementById('email-tone').value);

  try {
    const res  = await fetch('tools.php', {method:'POST', body:fd});
    const json = await res.json();
    if (json.ok && json.data) {
      const d = json.data;
      document.getElementById('email-placeholder').style.display = 'none';
      document.getElementById('email-result').classList.remove('hidden');
      document.getElementById('email-from-display').textContent    = sender + ' — VibeCodeWeb';
      document.getElementById('email-to-display').textContent      = recipient + (company ? ', ' + company : '');
      document.getElementById('email-subject-display').textContent = d.subject || '';
      document.getElementById('email-body-display').textContent    = d.body    || '';
      document.getElementById('email-ps-display').textContent      = d.ps ? 'P.S. ' + d.ps : '';
    } else {
      showError('email', json.error || 'Generation failed — check API key in tools.php');
    }
  } catch(err) { showError('email', 'Request failed: ' + err.message); }

  btn.disabled = false;
  btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i>Compose Email';
}

function showError(tool, msg) {
  alert('⚠️ ' + msg);
}

function copyText(id) {
  const el = document.getElementById(id);
  navigator.clipboard.writeText(el.textContent).then(() => {
    const btn = el.closest('.glass').querySelector('.copy-btn');
    if (btn) { btn.classList.add('copied'); btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Copied!'; setTimeout(()=>{ btn.classList.remove('copied'); btn.innerHTML = '<i class="fa-regular fa-copy mr-1"></i>Copy'; }, 2000); }
  });
}

function copyEmailSubject() {
  const txt = document.getElementById('email-subject-display').textContent;
  navigator.clipboard.writeText(txt).then(() => {
    const btn = document.getElementById('copy-subject-btn');
    btn.classList.add('copied'); btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Copied!';
    setTimeout(()=>{ btn.classList.remove('copied'); btn.innerHTML = '<i class="fa-regular fa-copy mr-1"></i>Copy Subject'; }, 2000);
  });
}

function copyFullEmail() {
  const subj = document.getElementById('email-subject-display').textContent;
  const body = document.getElementById('email-body-display').textContent;
  const ps   = document.getElementById('email-ps-display').textContent;
  const full = `Subject: ${subj}\n\n${body}${ps ? '\n\n'+ps : ''}`;
  navigator.clipboard.writeText(full).then(() => {
    const btn = document.getElementById('copy-full-btn');
    btn.classList.add('copied'); btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Copied!';
    setTimeout(()=>{ btn.classList.remove('copied'); btn.innerHTML = '<i class="fa-solid fa-copy mr-1"></i>Copy Full Email'; }, 2000);
  });
}
</script>
</body>
</html>
