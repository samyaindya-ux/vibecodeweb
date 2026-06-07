<?php
/**
 * Minimal Instagram Graph API helper for VibeCodeWeb automation.
 *
 * Wraps the container -> publish flow for single images, carousels and reels,
 * plus insights reads. No third-party dependencies (uses cURL).
 *
 * Docs: https://developers.facebook.com/docs/instagram-platform/content-publishing
 */

class IgGraph
{
    private string $token;
    private string $igId;
    private string $base;

    public function __construct(array $config)
    {
        $this->token = $config['access_token'];
        $this->igId  = (string) $config['ig_business_id'];
        $this->base  = 'https://graph.facebook.com/' . $config['graph_version'];
    }

    // ---- low-level HTTP -----------------------------------------------------

    private function request(string $method, string $path, array $params = []): array
    {
        $params['access_token'] = $this->token;
        $url = $this->base . '/' . ltrim($path, '/');

        $ch = curl_init();
        if (strtoupper($method) === 'GET') {
            $url .= '?' . http_build_query($params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException("cURL error: {$err}");
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException("Non-JSON response (HTTP {$code}): {$raw}");
        }
        if (isset($data['error'])) {
            $msg = $data['error']['message'] ?? 'unknown';
            throw new RuntimeException("Graph API error (HTTP {$code}): {$msg}");
        }
        return $data;
    }

    // ---- account ------------------------------------------------------------

    /** Sanity check: returns username + follower count. Used by verify-auth. */
    public function account(): array
    {
        return $this->request('GET', $this->igId, [
            'fields' => 'username,followers_count,media_count,name,biography',
        ]);
    }

    // ---- publishing ---------------------------------------------------------

    /** Create a single-image container; returns container id. */
    public function createImageContainer(string $imageUrl, string $caption): string
    {
        $res = $this->request('POST', "{$this->igId}/media", [
            'image_url' => $imageUrl,
            'caption'   => $caption,
        ]);
        return $res['id'];
    }

    /** Create a carousel child container (image only); returns container id. */
    public function createCarouselChild(string $imageUrl): string
    {
        $res = $this->request('POST', "{$this->igId}/media", [
            'image_url'        => $imageUrl,
            'is_carousel_item' => 'true',
        ]);
        return $res['id'];
    }

    /** Create the carousel parent from child container ids; returns container id. */
    public function createCarouselParent(array $childIds, string $caption): string
    {
        $res = $this->request('POST', "{$this->igId}/media", [
            'media_type' => 'CAROUSEL',
            'children'   => implode(',', $childIds),
            'caption'    => $caption,
        ]);
        return $res['id'];
    }

    /** Create a reel container; returns container id (must poll before publish). */
    public function createReelContainer(string $videoUrl, string $caption, ?string $coverUrl = null): string
    {
        $params = [
            'media_type' => 'REELS',
            'video_url'  => $videoUrl,
            'caption'    => $caption,
        ];
        if ($coverUrl) {
            $params['cover_url'] = $coverUrl;
        }
        $res = $this->request('POST', "{$this->igId}/media", $params);
        return $res['id'];
    }

    /** Poll a container's processing status. Returns FINISHED|IN_PROGRESS|ERROR|... */
    public function containerStatus(string $containerId): string
    {
        $res = $this->request('GET', $containerId, ['fields' => 'status_code']);
        return $res['status_code'] ?? 'UNKNOWN';
    }

    /** Wait until a (reel/video) container finishes processing. */
    public function waitForContainer(string $containerId, int $maxTries = 30, int $sleep = 5): void
    {
        for ($i = 0; $i < $maxTries; $i++) {
            $status = $this->containerStatus($containerId);
            if ($status === 'FINISHED') {
                return;
            }
            if ($status === 'ERROR' || $status === 'EXPIRED') {
                throw new RuntimeException("Container {$containerId} failed processing: {$status}");
            }
            sleep($sleep);
        }
        throw new RuntimeException("Container {$containerId} not ready after {$maxTries} tries");
    }

    /** Publish a finished container; returns the published media id. */
    public function publish(string $containerId): string
    {
        $res = $this->request('POST', "{$this->igId}/media_publish", [
            'creation_id' => $containerId,
        ]);
        return $res['id'];
    }

    // ---- insights -----------------------------------------------------------

    /** Account-level insights for a metric set over a period. */
    public function accountInsights(array $metrics, string $period = 'day'): array
    {
        return $this->request('GET', "{$this->igId}/insights", [
            'metric' => implode(',', $metrics),
            'period' => $period,
        ]);
    }

    /** Media-level insights for one published post. */
    public function mediaInsights(string $mediaId, array $metrics): array
    {
        return $this->request('GET', "{$mediaId}/insights", [
            'metric' => implode(',', $metrics),
        ]);
    }
}
