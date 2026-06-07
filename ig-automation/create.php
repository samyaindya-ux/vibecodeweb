<?php
/**
 * Live content creator — add a new post from the browser.
 * Protected by .htaccess (same auth as review.php).
 *
 * https://vibecodeweb.in/ig-automation/create.php
 */

require __DIR__ . '/lib/queue.php';
require __DIR__ . '/assets-generator/generate.php';

$config = file_exists(__DIR__ . '/config.php')
    ? require __DIR__ . '/config.php'
    : ['queue_path' => __DIR__ . '/queue.json', 'timezone' => 'Asia/Kolkata'];

date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');

$TAGS = [
    'A' => '#artificialintelligence #aiautomation #aitools #chatgpt #claudeai #geminiai #automation #machinelearning #futureofwork #digitaltransformation #aiforbusiness #techindia #startupindia #madeinindia #vibecodeweb',
    'B' => '#webdevelopment #saas #saasdevelopment #chatbot #aichatbot #posystem #softwaredevelopment #customsoftware #appdevelopment #apiintegration #nocode #buildinpublic #indiandevelopers #techstartup #vibecodeweb',
    'C' => '#smallbusinessindia #indianbusiness #businessgrowth #digitalindia #smbindia #entrepreneurindia #startupindia #localbusiness #businessautomation #growyourbusiness #affordabletech #indianstartups #makeinindia #businesstools #vibecodeweb',
];

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headline  = trim($_POST['headline']  ?? '');
    $sub       = trim($_POST['sub']       ?? '');
    $caption   = trim($_POST['caption']   ?? '');
    $accent    = in_array($_POST['accent'] ?? '', ['neon','primary','saffron'])
                   ? $_POST['accent'] : 'neon';
    $tagSet    = $_POST['tag_set'] ?? 'A';
    $hashtags  = $tagSet === 'custom'
                   ? trim($_POST['custom_tags'] ?? '')
                   : ($TAGS[$tagSet] ?? $TAGS['A']);
    $schedRaw  = $_POST['scheduled_at'] ?? '';
    $scheduled = $schedRaw ? date('c', strtotime($schedRaw)) : date('c', strtotime('tomorrow 10:30'));

    if (!$headline) {
        $error = 'Headline is required — it appears on the image.';
    } elseif (!$caption) {
        $error = 'Caption is required — it goes in the Instagram post.';
    } else {
        // Auto-generate next ID.
        $queue   = new Queue($config['queue_path']);
        $all     = $queue->all();
        $maxNum  = 0;
        foreach ($all as $item) {
            if (preg_match('/^p(\d+)$/', $item['id'] ?? '', $m)) {
                $maxNum = max($maxNum, (int)$m[1]);
            }
        }
        $newId   = 'p' . str_pad($maxNum + 1, 2, '0', STR_PAD_LEFT);
        $imgFile = $newId . '.png';
        $imgPath = __DIR__ . '/../ig-assets/' . $imgFile;

        // Generate branded image.
        @mkdir(dirname($imgPath), 0775, true);
        try {
            vcw_generate_image([
                'headline' => $headline,
                'sub'      => $sub ?: null,
                'accent'   => $accent,
                'out'      => $imgPath,
            ]);
            $imgOk = true;
        } catch (Throwable $e) {
            $imgOk = false;
            $error = 'Image generation failed: ' . $e->getMessage()
                   . ' — post was NOT saved. Check GD extension is enabled.';
        }

        if ($imgOk) {
            $items   = $all;
            $items[] = [
                'id'           => $newId,
                'type'         => 'image',
                'caption'      => $caption,
                'hashtags'     => $hashtags,
                'media'        => [$imgFile],
                'scheduled_at' => $scheduled,
                'status'       => 'pending_review',
                'created_at'   => date('c'),
            ];
            $queue->save($items);
            header('Location: review.php');
            exit;
        }
    }
}

$defaultSchedule = date('Y-m-d\TH:i', strtotime('tomorrow 10:30'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VibeCodeWeb · New Post</title>
<style>
  :root { --bg:#020617; --card:#1e293b; --saffron:#f97316; --neon:#10b981; --primary:#3b82f6; --muted:#94a3b8; }
  * { box-sizing:border-box; }
  body { margin:0; background:var(--bg); color:#f8fafc; font-family:'Segoe UI',system-ui,sans-serif; }
  header { padding:24px 32px; border-bottom:1px solid rgba(255,255,255,.08);
    background:linear-gradient(90deg,rgba(249,115,22,.12),rgba(59,130,246,.12),rgba(16,185,129,.12));
    display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
  h1 { margin:0; font-size:22px; flex:1; }
  .back { color:var(--muted); text-decoration:none; font-size:13px; border:1px solid rgba(255,255,255,.12);
    border-radius:999px; padding:6px 14px; }
  .back:hover { color:#f8fafc; }
  .wrap { max-width:720px; margin:0 auto; padding:32px; }
  .field { margin-bottom:22px; }
  label { display:block; font-size:12px; color:var(--muted); text-transform:uppercase;
    letter-spacing:.06em; margin-bottom:6px; }
  input[type=text], input[type=datetime-local], textarea, select {
    width:100%; background:#0f172a; color:#f8fafc;
    border:1px solid rgba(255,255,255,.12); border-radius:10px;
    padding:11px 14px; font:inherit; font-size:14px; }
  input:focus, textarea:focus, select:focus { outline:none; border-color:var(--neon); }
  textarea { resize:vertical; }
  .hint { font-size:12px; color:var(--muted); margin-top:5px; }
  .accents { display:flex; gap:10px; flex-wrap:wrap; margin-top:6px; }
  .accents label { text-transform:none; letter-spacing:0; font-size:14px; cursor:pointer;
    border:2px solid rgba(255,255,255,.12); border-radius:12px; padding:10px 18px;
    display:flex; align-items:center; gap:8px; margin:0; transition:.15s; }
  .accents input[type=radio] { display:none; }
  .accents input[type=radio]:checked + label { border-color:currentColor; background:rgba(255,255,255,.05); }
  .dot { width:12px; height:12px; border-radius:50%; display:inline-block; }
  .tags { display:flex; gap:10px; flex-wrap:wrap; margin-top:6px; }
  .tags label { text-transform:none; letter-spacing:0; font-size:13px; cursor:pointer;
    border:2px solid rgba(255,255,255,.12); border-radius:10px; padding:8px 14px; margin:0; }
  .tags input[type=radio] { display:none; }
  .tags input[type=radio]:checked + label { border-color:var(--primary); color:#93c5fd; }
  #custom_tags_wrap { display:none; margin-top:10px; }
  .error { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3);
    color:#fca5a5; border-radius:12px; padding:14px 18px; margin-bottom:24px; }
  .btn { border:none; border-radius:999px; padding:14px 32px; font-weight:700;
    cursor:pointer; font-size:15px; background:var(--neon); color:#022; width:100%; }
  .btn:hover { opacity:.9; }
  .preview-wrap { margin-top:16px; display:none; text-align:center; }
  .preview-wrap img { max-width:300px; border-radius:12px; border:1px solid rgba(255,255,255,.1); }
</style>
</head>
<body>
<header>
  <h1>✏️ New Post</h1>
  <a class="back" href="review.php">← Review dashboard</a>
</header>
<div class="wrap">

<?php if ($error): ?>
  <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" id="form">

  <div class="field">
    <label>Headline <span style="color:#fca5a5">*</span></label>
    <input type="text" name="headline" placeholder="e.g. Your business, running 24/7 on AI."
           value="<?= htmlspecialchars($_POST['headline'] ?? '') ?>" maxlength="120">
    <div class="hint">Big text on the image. Keep it punchy — under 10 words works best.</div>
  </div>

  <div class="field">
    <label>Image subtext <span style="color:var(--muted)">(optional)</span></label>
    <input type="text" name="sub" placeholder="e.g. Automations · Web · SaaS"
           value="<?= htmlspecialchars($_POST['sub'] ?? '') ?>" maxlength="80">
    <div class="hint">Smaller line below the headline on the image.</div>
  </div>

  <div class="field">
    <label>Accent colour</label>
    <div class="accents">
      <?php foreach (['neon'=>['#10b981','Neon green'],'primary'=>['#3b82f6','Blue'],'saffron'=>['#f97316','Saffron']] as $k=>[$col,$lbl]): ?>
        <div>
          <input type="radio" name="accent" id="ac_<?=$k?>" value="<?=$k?>"
            <?= (($_POST['accent'] ?? 'neon') === $k ? 'checked' : '') ?>>
          <label for="ac_<?=$k?>" style="color:<?=$col?>">
            <span class="dot" style="background:<?=$col?>"></span><?=$lbl?>
          </label>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="field">
    <label>Caption <span style="color:#fca5a5">*</span></label>
    <textarea name="caption" rows="6"
      placeholder="Write the Instagram caption here. Use emojis, line breaks, CTAs…"><?= htmlspecialchars($_POST['caption'] ?? '') ?></textarea>
    <div class="hint">This is what appears in the Instagram post below the image.</div>
  </div>

  <div class="field">
    <label>Hashtag set</label>
    <div class="tags">
      <?php $tagLabels = ['A'=>'A — AI / Automation','B'=>'B — Web / SaaS','C'=>'C — Small Biz India','custom'=>'Custom']; ?>
      <?php foreach ($tagLabels as $k=>$lbl): ?>
        <div>
          <input type="radio" name="tag_set" id="tag_<?=$k?>" value="<?=$k?>"
            <?= (($_POST['tag_set'] ?? 'A') === $k ? 'checked' : '') ?>
            onchange="document.getElementById('custom_tags_wrap').style.display=(this.value==='custom'?'block':'none')">
          <label for="tag_<?=$k?>"><?=$lbl?></label>
        </div>
      <?php endforeach; ?>
    </div>
    <div id="custom_tags_wrap">
      <textarea name="custom_tags" rows="3" placeholder="Enter your own hashtags..."><?= htmlspecialchars($_POST['custom_tags'] ?? '') ?></textarea>
    </div>
  </div>

  <div class="field">
    <label>Schedule</label>
    <input type="datetime-local" name="scheduled_at"
           value="<?= htmlspecialchars($_POST['scheduled_at'] ?? $defaultSchedule) ?>">
    <div class="hint">When the post should go live (IST). The hourly cron will pick it up after you approve it.</div>
  </div>

  <button type="submit" class="btn">🖼 Generate image &amp; add to review queue</button>
</form>
</div>

<script>
// Show custom tags if selected on page load (after validation error).
if (document.querySelector('input[name=tag_set]:checked')?.value === 'custom') {
  document.getElementById('custom_tags_wrap').style.display = 'block';
}
</script>
</body>
</html>
