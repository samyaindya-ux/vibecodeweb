<?php
/**
 * VibeCodeWeb Instagram publisher — cron entry point.
 *
 * Publishes queue items that are APPROVED and DUE (scheduled_at <= now).
 * Run hourly via cron (GlobeHost) or a Windows Scheduled Task.
 *
 *   php publisher.php            # respects config 'dry_run'
 *   php publisher.php --live     # force live publish (overrides dry_run)
 *   php publisher.php --dry-run  # force dry-run
 *
 * Only items a human marked "approved" in review.php are ever published.
 */

require __DIR__ . '/lib/graph.php';
require __DIR__ . '/lib/queue.php';
require __DIR__ . '/lib/db.php';

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "Missing config.php — copy config.sample.php and fill in credentials.\n");
    exit(1);
}
$config = require $configPath;

date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');

// CLI flag overrides for the dry-run safety switch.
$argvFlags = $argv ?? [];
$dryRun = (bool) ($config['dry_run'] ?? true);
if (in_array('--live', $argvFlags, true))     $dryRun = false;
if (in_array('--dry-run', $argvFlags, true))  $dryRun = true;

@mkdir($config['log_dir'], 0775, true);
$logFile = rtrim($config['log_dir'], '/\\') . '/publisher.log';

function logline(string $logFile, string $msg): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    echo $line . "\n";
    file_put_contents($logFile, $line . "\n", FILE_APPEND);
}

$queue = new Queue(vcw_db($config));
$now   = time();

$due = array_filter($queue->byStatus('approved'), function ($item) use ($now) {
    $sched = isset($item['scheduled_at']) ? strtotime($item['scheduled_at']) : 0;
    return $sched <= $now;
});

if (!$due) {
    logline($logFile, 'No approved+due items. Nothing to publish.');
    exit(0);
}

logline($logFile, count($due) . ' item(s) due.' . ($dryRun ? '  [DRY-RUN]' : '  [LIVE]'));

$graph = $dryRun ? null : new IgGraph($config);
$assetsBase = rtrim($config['assets_base_url'], '/') . '/';

foreach ($due as $item) {
    $id   = $item['id'];
    $type = $item['type'] ?? 'image';
    $caption = trim(($item['caption'] ?? '') . "\n\n" . ($item['hashtags'] ?? ''));

    // Resolve media filenames to public URLs.
    $urls = array_map(function ($m) use ($assetsBase) {
        return preg_match('#^https?://#', $m) ? $m : $assetsBase . ltrim($m, '/');
    }, $item['media'] ?? []);

    try {
        if ($dryRun) {
            logline($logFile, "DRY-RUN {$id} [{$type}] media=" . json_encode($urls)
                . ' caption="' . mb_substr($caption, 0, 60) . '..."');
            continue;
        }

        if ($type === 'image') {
            $c = $graph->createImageContainer($urls[0], $caption);
            $mediaId = $graph->publish($c);
        } elseif ($type === 'carousel') {
            $children = array_map(fn($u) => $graph->createCarouselChild($u), $urls);
            $parent   = $graph->createCarouselParent($children, $caption);
            $mediaId  = $graph->publish($parent);
        } elseif ($type === 'reel') {
            $c = $graph->createReelContainer($urls[0], $caption, $item['cover'] ?? null);
            $graph->waitForContainer($c);
            $mediaId = $graph->publish($c);
        } else {
            throw new RuntimeException("Unknown type '{$type}'");
        }

        $queue->update($id, function (&$it) use ($mediaId) {
            $it['status'] = 'published';
            $it['media_id'] = $mediaId;
            $it['published_at'] = date('c');
        });
        logline($logFile, "PUBLISHED {$id} -> media_id={$mediaId}");
    } catch (Throwable $e) {
        $queue->update($id, function (&$it) use ($e) {
            $it['status'] = 'failed';
            $it['error'] = $e->getMessage();
            $it['failed_at'] = date('c');
        });
        logline($logFile, "FAILED {$id}: " . $e->getMessage());
    }
}

logline($logFile, 'Run complete.');
