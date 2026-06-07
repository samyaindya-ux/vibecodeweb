<?php
/**
 * Branded 1080x1080 post-image generator (PHP GD).
 *
 * On-brand with vibecodeweb.in: dark background (#020617), top tricolor bar
 * (saffron/white/green), the site logo, a big headline, and a footer handle.
 * Self-contained — no headless browser, runs fine in cron.
 *
 * CLI:
 *   php generate.php --headline="AI that runs your business 24/7" \
 *       --accent=neon --out=post1.png [--sub="Automations · Web · SaaS"]
 *
 * Programmatic:
 *   require 'generate.php'; vcw_generate_image([...]);
 *
 * Accents: saffron (#f97316), primary (#3b82f6), neon (#10b981).
 */

const VCW_W = 1080;
const VCW_H = 1080;

/** Resolve a usable bold TTF across Windows (local) and Linux (GlobeHost). */
function vcw_font(bool $bold = true): ?string
{
    $candidates = $bold
        ? ['C:/Windows/Fonts/arialbd.ttf', 'C:/Windows/Fonts/segoeuib.ttf',
           '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
           '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf']
        : ['C:/Windows/Fonts/arial.ttf', 'C:/Windows/Fonts/segoeui.ttf',
           '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
           '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf'];
    foreach ($candidates as $f) {
        if (is_file($f)) return $f;
    }
    return null; // caller falls back to GD bitmap font
}

function vcw_hex($img, string $hex): int
{
    $hex = ltrim($hex, '#');
    return imagecolorallocate($img,
        hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
}

/** Word-wrap text to a max pixel width for a given TTF + size. */
function vcw_wrap($font, float $size, string $text, int $maxWidth): array
{
    $words = preg_split('/\s+/', trim($text));
    $lines = [];
    $cur = '';
    foreach ($words as $w) {
        $try = $cur === '' ? $w : "$cur $w";
        $box = imagettfbbox($size, 0, $font, $try);
        $width = abs($box[2] - $box[0]);
        if ($width > $maxWidth && $cur !== '') {
            $lines[] = $cur;
            $cur = $w;
        } else {
            $cur = $try;
        }
    }
    if ($cur !== '') $lines[] = $cur;
    return $lines;
}

/**
 * Generate one branded image.
 * $opts: headline (required), sub (optional), accent (saffron|primary|neon),
 *        out (absolute path), logo (path to logo png).
 */
function vcw_generate_image(array $opts): string
{
    $accents = ['saffron' => '#f97316', 'primary' => '#3b82f6', 'neon' => '#10b981'];
    $accentHex = $accents[$opts['accent'] ?? 'neon'] ?? $accents['neon'];

    $img = imagecreatetruecolor(VCW_W, VCW_H);
    imagesavealpha($img, true);

    // Background: dark base with a subtle vertical gradient toward accent-tinted dark.
    [$ar, $ag, $ab] = sscanf($accentHex, '#%02x%02x%02x');
    for ($y = 0; $y < VCW_H; $y++) {
        $t = $y / VCW_H;
        $r = (int) (2 + ($ar * 0.06) * $t);
        $g = (int) (6 + ($ag * 0.06) * $t);
        $b = (int) (23 + ($ab * 0.06) * $t);
        $c = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, VCW_W, $y, $c);
    }

    // Top tricolor bar (India: saffron / white / green).
    $barH = 14;
    imagefilledrectangle($img, 0, 0, (int)(VCW_W / 3), $barH, vcw_hex($img, '#f97316'));
    imagefilledrectangle($img, (int)(VCW_W / 3), 0, (int)(2 * VCW_W / 3), $barH, vcw_hex($img, '#ffffff'));
    imagefilledrectangle($img, (int)(2 * VCW_W / 3), 0, VCW_W, $barH, vcw_hex($img, '#15803d'));

    // Logo (top-left), if available.
    $logoPath = $opts['logo'] ?? (__DIR__ . '/../../images/new_site_logo.png');
    if (is_file($logoPath)) {
        $logo = @imagecreatefrompng($logoPath);
        if ($logo) {
            $lw = imagesx($logo); $lh = imagesy($logo);
            $target = 110;
            $scale = $target / max($lw, $lh);
            imagecopyresampled($img, $logo, 80, 70, 0, 0,
                (int)($lw * $scale), (int)($lh * $scale), $lw, $lh);
            imagedestroy($logo);
        }
    }

    $font     = vcw_font(true);
    $fontReg  = vcw_font(false);
    $white    = vcw_hex($img, '#f8fafc');
    $accent   = vcw_hex($img, $accentHex);
    $muted    = vcw_hex($img, '#94a3b8');

    $marginX  = 90;
    $maxText  = VCW_W - 2 * $marginX;

    if ($font) {
        // Headline — large, wrapped, vertically centered-ish.
        $size = 64;
        $lines = vcw_wrap($font, $size, $opts['headline'] ?? '', $maxText);
        // Shrink if too many lines.
        while (count($lines) > 6 && $size > 40) {
            $size -= 6;
            $lines = vcw_wrap($font, $size, $opts['headline'] ?? '', $maxText);
        }
        $lineH = (int)($size * 1.35);
        $blockH = count($lines) * $lineH;
        $y = (int)((VCW_H - $blockH) / 2) + $size;
        foreach ($lines as $i => $line) {
            // Last line gets the accent color for emphasis.
            $color = ($i === count($lines) - 1) ? $accent : $white;
            imagettftext($img, $size, 0, $marginX, $y, $color, $font, $line);
            $y += $lineH;
        }

        // Subtext (optional).
        if (!empty($opts['sub']) && $fontReg) {
            imagettftext($img, 30, 0, $marginX, $y + 20, $muted, $fontReg, $opts['sub']);
        }

        // Footer handle + site.
        if ($fontReg) {
            imagettftext($img, 26, 0, $marginX, VCW_H - 70, $accent, $fontReg, '@vibecodeweb.in');
            imagettftext($img, 26, 0, $marginX, VCW_H - 70 + 38, $muted, $fontReg, 'vibecodeweb.in  ·  Proudly Built in India');
        }
    } else {
        // No TTF available — degrade gracefully with the bitmap font.
        imagestring($img, 5, $marginX, 400, $opts['headline'] ?? '', $white);
        imagestring($img, 4, $marginX, VCW_H - 80, '@vibecodeweb.in  ·  vibecodeweb.in', $accent);
    }

    $out = $opts['out'] ?? (__DIR__ . '/../../ig-assets/post.png');
    @mkdir(dirname($out), 0775, true);
    imagepng($img, $out);
    imagedestroy($img);
    return $out;
}

// ---- CLI --------------------------------------------------------------------
if (PHP_SAPI === 'cli' && realpath($argv[0]) === realpath(__FILE__)) {
    $args = [];
    foreach (array_slice($argv, 1) as $a) {
        if (preg_match('/^--([^=]+)=(.*)$/s', $a, $m)) $args[$m[1]] = $m[2];
    }
    if (empty($args['headline'])) {
        fwrite(STDERR, "Usage: php generate.php --headline=\"...\" [--sub=\"...\"] [--accent=neon] [--out=path]\n");
        exit(1);
    }
    $path = vcw_generate_image([
        'headline' => $args['headline'],
        'sub'      => $args['sub'] ?? null,
        'accent'   => $args['accent'] ?? 'neon',
        'out'      => isset($args['out'])
            ? (__DIR__ . '/../../ig-assets/' . basename($args['out']))
            : null,
    ]);
    echo "Wrote: {$path}\n";
}
