<?php
// VibeCodeWeb site chatbot — Anthropic proxy. Key lives in tools_config.php (gitignored).
if (file_exists(__DIR__ . '/tools_config.php')) require_once __DIR__ . '/tools_config.php';
if (!defined('ANTHROPIC_KEY')) define('ANTHROPIC_KEY', '');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'error'=>'POST only']); exit; }
if (ANTHROPIC_KEY === '') { echo json_encode(['ok'=>false,'error'=>'Assistant is not configured.']); exit; }

// Basic per-IP throttle so a public endpoint can't drain the key: 20 msgs / 10 min.
$ip   = $_SERVER['REMOTE_ADDR'] ?? '0';
$file = sys_get_temp_dir() . '/vcw_chat_' . md5($ip);
$hits = array_filter((array) @json_decode((string) @file_get_contents($file), true),
                     function ($t) { return $t > time() - 600; });
if (count($hits) >= 20) { echo json_encode(['ok'=>false,'error'=>'Too many messages — please try again in a few minutes.']); exit; }
$hits[] = time();
@file_put_contents($file, json_encode(array_values($hits)));

$in       = json_decode(file_get_contents('php://input'), true) ?: [];
$messages = [];
foreach (array_slice((array) ($in['messages'] ?? []), -12) as $m) {   // last 12 turns
    $role = ($m['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
    $text = trim(mb_substr((string) ($m['content'] ?? ''), 0, 2000));
    if ($text !== '') $messages[] = ['role' => $role, 'content' => $text];
}
if (!$messages) { echo json_encode(['ok'=>false,'error'=>'Empty message']); exit; }

$system = "You are the VibeCodeWeb assistant on vibecodeweb.in. VibeCodeWeb delivers AI automation, custom web development, SaaS products, and AI consulting for businesses across India, with expertise in ChatGPT, Claude, Gemini and intelligent workflow automation. Help visitors understand our services, answer questions about web development, AI automation and consulting, and encourage them to get in touch for a project. Be friendly, concise (1-3 sentences) and professional. If you do not know something, suggest they contact the team.";

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode([
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 400,
        'system'     => $system,
        'messages'   => $messages,
    ]),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . ANTHROPIC_KEY,
        'anthropic-version: 2023-06-01',
    ],
    CURLOPT_TIMEOUT        => 30,
]);
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if (!$res) { echo json_encode(['ok'=>false,'error'=>'API call failed: ' . $err]); exit; }
$json = json_decode($res, true);
$text = $json['content'][0]['text'] ?? '';
echo $text !== ''
    ? json_encode(['ok'=>true, 'reply'=>trim($text)])
    : json_encode(['ok'=>false, 'error'=>$json['error']['message'] ?? 'Empty response from model.']);
