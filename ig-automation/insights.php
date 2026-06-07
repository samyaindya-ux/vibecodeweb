<?php
/**
 * Weekly insights pull — account + recent-post metrics → a report file.
 *
 *   php insights.php
 *
 * Writes logs/insights-YYYY-MM-DD.json and prints a short summary used to
 * adapt the content plan (best times, best formats).
 */

require __DIR__ . '/lib/graph.php';
require __DIR__ . '/lib/queue.php';

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "Missing config.php — fill in credentials first.\n");
    exit(1);
}
$config = require $configPath;
date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');
@mkdir($config['log_dir'], 0775, true);

$graph = new IgGraph($config);

try {
    $account = $graph->account();
    // Account-level reach/views over the last week (metrics available vary by API version).
    $acctInsights = [];
    foreach (['reach', 'profile_views'] as $metric) {
        try {
            $acctInsights[$metric] = $graph->accountInsights([$metric], 'day');
        } catch (Throwable $e) {
            $acctInsights[$metric] = ['error' => $e->getMessage()];
        }
    }

    // Per-post insights for items we published.
    $queue = new Queue($config['queue_path']);
    $postMetrics = [];
    foreach ($queue->byStatus('published') as $item) {
        if (empty($item['media_id'])) continue;
        try {
            $postMetrics[$item['id']] = [
                'media_id'  => $item['media_id'],
                'published' => $item['published_at'] ?? null,
                'insights'  => $graph->mediaInsights($item['media_id'], ['reach', 'likes', 'comments', 'saved']),
            ];
        } catch (Throwable $e) {
            $postMetrics[$item['id']] = ['error' => $e->getMessage()];
        }
    }

    $report = [
        'generated_at' => date('c'),
        'account'      => $account,
        'account_insights' => $acctInsights,
        'posts'        => $postMetrics,
    ];

    $file = rtrim($config['log_dir'], '/\\') . '/insights-' . date('Y-m-d') . '.json';
    file_put_contents($file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "Account: @{$account['username']} · followers: "
        . ($account['followers_count'] ?? '?') . " · posts: " . ($account['media_count'] ?? '?') . "\n";
    echo "Report written: {$file}\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Insights failed: " . $e->getMessage() . "\n");
    exit(1);
}
