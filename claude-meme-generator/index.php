<?php
/**
 * Claude Chat Meme Generator
 *
 * Standalone PHP script that generates images mimicking Claude AI chat screenshots.
 * Great for memes! Drop this folder on any PHP-enabled web server.
 *
 * Usage:
 *   1. Open in browser — fill out the form
 *   2. Or pass query params: ?user=...&claude=...&generate=1
 *   3. ?download=1 to get the PNG directly
 */

// ─── Font discovery ──────────────────────────────────────────────────────────
$regularFont = null;
$boldFont = null;

// Check local fonts/ dir first
$fontDir = __DIR__ . '/fonts';
if (is_dir($fontDir)) {
    foreach (['Inter-Regular.ttf', 'DejaVuSans.ttf', 'arial.ttf', 'NotoSans-Regular.ttf'] as $f) {
        if (file_exists("$fontDir/$f")) { $regularFont = "$fontDir/$f"; break; }
    }
    foreach (['Inter-Bold.ttf', 'DejaVuSans-Bold.ttf', 'arialbd.ttf', 'NotoSans-Bold.ttf'] as $f) {
        if (file_exists("$fontDir/$f")) { $boldFont = "$fontDir/$f"; break; }
    }
}

// Try system fonts as fallback
if (!$regularFont) {
    foreach ([
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/usr/share/fonts/TTF/DejaVuSans.ttf',
        '/usr/share/fonts/dejavu-sans-fonts/DejaVuSans.ttf',
        '/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
    ] as $path) {
        if (file_exists($path)) { $regularFont = $path; break; }
    }
}
if (!$boldFont) {
    foreach ([
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/TTF/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/dejavu-sans-fonts/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/noto/NotoSans-Bold.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
    ] as $path) {
        if (file_exists($path)) { $boldFont = $path; break; }
    }
}

$hasTTF = ($regularFont !== null);
if (!$boldFont) $boldFont = $regularFont;

// ─── Input ───────────────────────────────────────────────────────────────────
$userText   = isset($_REQUEST['user'])   ? trim($_REQUEST['user'])   : '';
$claudeText = isset($_REQUEST['claude']) ? trim($_REQUEST['claude']) : '';
$modelName  = isset($_REQUEST['model'])  ? trim($_REQUEST['model'])  : 'Sonnet 4.6';
$chatTitle  = isset($_REQUEST['title'])  ? trim($_REQUEST['title'])  : '';
$generate   = isset($_REQUEST['generate']);
$download   = isset($_REQUEST['download']);

// ─── Route ───────────────────────────────────────────────────────────────────
if (!$generate || $userText === '' || $claudeText === '') {
    showForm($userText, $claudeText, $modelName, $chatTitle);
    exit;
}

$img = generateMeme($userText, $claudeText, $modelName, $chatTitle, $hasTTF, $regularFont, $boldFont);

if ($download) {
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="claude-meme-' . time() . '.png"');
    imagepng($img);
    imagedestroy($img);
    exit;
}

// Preview page with embedded base64 image
ob_start();
imagepng($img);
$base64 = base64_encode(ob_get_clean());
imagedestroy($img);
showPreview($base64, $userText, $claudeText, $modelName, $chatTitle);
exit;


// ═════════════════════════════════════════════════════════════════════════════
//  IMAGE GENERATION
// ═════════════════════════════════════════════════════════════════════════════

function generateMeme($userText, $claudeText, $modelName, $chatTitle, $hasTTF, $regularFont, $boldFont) {
    $width         = 620;
    $pad           = 28;
    $fontSize      = $hasTTF ? 11.5 : 3;
    $smallSize     = $hasTTF ? 9.5  : 2;
    $lineH         = $hasTTF ? 20   : 16;
    $contentW      = $width - $pad * 2 - 60;
    $userBubbleMax = (int)($width * 0.68);

    // Wrap text
    $uLines = wrapText($userText,   $contentW - 20, $fontSize, $hasTTF, $regularFont);
    $cLines = wrapText($claudeText, $contentW,      $fontSize, $hasTTF, $regularFont);

    // Heights
    $headerH   = 48;
    $titleBarH = 30;
    $gap       = 18;
    $uBubbleH  = count($uLines) * $lineH + 24;
    $cBlockH   = count($cLines) * $lineH + 16;
    $footerH   = 52;
    $discH     = 18;

    $totalH = $headerH + $titleBarH + $gap + $uBubbleH + $gap + $cBlockH + $gap + $footerH + $discH;

    // Canvas
    $img = imagecreatetruecolor($width, $totalH);
    imagesavealpha($img, true);

    // Palette — warm cream Claude theme
    $bg          = imagecolorallocate($img, 252, 247, 243);
    $white       = imagecolorallocate($img, 255, 255, 255);
    $dark        = imagecolorallocate($img, 41,  37,  36);
    $muted       = imagecolorallocate($img, 140, 130, 125);
    $bubbleBg    = imagecolorallocate($img, 238, 231, 224);
    $orange      = imagecolorallocate($img, 207, 114, 52);
    $borderLight = imagecolorallocate($img, 230, 222, 216);
    $footerBg    = imagecolorallocate($img, 247, 242, 238);

    imagefilledrectangle($img, 0, 0, $width, $totalH, $bg);

    // ── Header (traffic lights + tabs) ───────────────────────────────────
    $y = 0;
    imagefilledrectangle($img, 0, 0, $width, $headerH, $bg);

    // macOS traffic-light dots
    $dotY = 20;
    imagefilledellipse($img, 22,  $dotY, 12, 12, imagecolorallocate($img, 255, 95, 87));
    imagefilledellipse($img, 40,  $dotY, 12, 12, imagecolorallocate($img, 255, 189, 46));
    imagefilledellipse($img, 58,  $dotY, 12, 12, imagecolorallocate($img, 39,  201, 63));

    // Navigation arrows
    $arrowCol = imagecolorallocate($img, 180, 170, 165);
    // Left arrow <
    imageline($img, 82, $dotY - 4, 78, $dotY, $arrowCol);
    imageline($img, 78, $dotY, 82, $dotY + 4, $arrowCol);
    // Right arrow >
    imageline($img, 96, $dotY - 4, 100, $dotY, $arrowCol);
    imageline($img, 100, $dotY, 96, $dotY + 4, $arrowCol);

    // Tabs: Chat  Cowork  Code
    $tabs = ['Chat', 'Cowork', 'Code'];
    $tabStartX = (int)($width / 2) - 65;
    foreach ($tabs as $i => $tab) {
        $tx = $tabStartX + $i * 58;
        if ($i === 0) {
            drawRoundedRect($img, $tx - 8, 10, $tx + 32, 32, 6, $white);
        }
        drawText($img, $tx, 14, $tab, $smallSize + 0.5, $i === 0 ? $dark : $muted, $hasTTF, $regularFont);
    }

    imageline($img, 0, $headerH, $width, $headerH, $borderLight);

    // ── Title bar ────────────────────────────────────────────────────────
    $titleY = $headerH + 8;
    $title  = !empty($chatTitle) ? $chatTitle : 'Claude Chat';
    drawText($img, $pad + 8, $titleY, $title, $smallSize, $muted, $hasTTF, $regularFont);
    // Small down-chevron after title
    $chevX = $pad + 8 + getTextWidth($title, $smallSize, $hasTTF, $regularFont) + 8;
    $chevY = $titleY + ($hasTTF ? 10 : 6);
    imageline($img, $chevX, $chevY, $chevX + 4, $chevY + 3, $muted);
    imageline($img, $chevX + 4, $chevY + 3, $chevX + 8, $chevY, $muted);

    // ── User message bubble (right-aligned) ──────────────────────────────
    $uY     = $headerH + $titleBarH + $gap;
    $uTextW = getMaxLineWidth($uLines, $fontSize, $hasTTF, $regularFont);
    $bW     = min($userBubbleMax, $uTextW + 32);
    $bX     = $width - $pad - $bW;

    drawRoundedRect($img, $bX, $uY, $bX + $bW, $uY + $uBubbleH, 16, $bubbleBg);

    $tx = $bX + 16;
    $ty = $uY + ($hasTTF ? 20 : 8);
    foreach ($uLines as $i => $line) {
        drawText($img, $tx, $ty + $i * $lineH, $line, $fontSize, $dark, $hasTTF, $regularFont);
    }

    // ── Claude response (with icon) ──────────────────────────────────────
    $cY    = $uY + $uBubbleH + $gap;
    $iconX = $pad + 4;
    $iconY = $cY + 2;
    drawClaudeIcon($img, $iconX, $iconY, $orange);

    $cTextX = $iconX + 30;
    $cTextY = $cY;
    foreach ($cLines as $i => $line) {
        $isBold = false;
        $boldPart = '';
        $rest = '';
        if (preg_match('/^\*\*(.+?)\*\*(.*)$/', $line, $m)) {
            $isBold   = true;
            $boldPart = $m[1];
            $rest     = $m[2];
        }

        $ly = $cTextY + $i * $lineH;
        if ($isBold && $hasTTF) {
            imagettftext($img, $fontSize, 0, $cTextX, $ly + 14, $dark, $boldFont, $boldPart);
            if ($rest !== '') {
                $bw = getTextWidth($boldPart, $fontSize, $hasTTF, $boldFont);
                imagettftext($img, $fontSize, 0, $cTextX + $bw + 2, $ly + 14, $dark, $regularFont, $rest);
            }
        } else {
            $display = str_replace(['**', '__'], '', $line);
            drawText($img, $cTextX, $ly, $display, $fontSize, $dark, $hasTTF, $regularFont);
        }
    }

    // ── Footer ───────────────────────────────────────────────────────────
    $fY = $totalH - $footerH - $discH;
    imagefilledrectangle($img, 0, $fY, $width, $fY + $footerH, $footerBg);
    imageline($img, 0, $fY, $width, $fY, $borderLight);

    // Reply placeholder
    drawText($img, $pad + 20, $fY + 12, 'Reply...', $smallSize + 1, $muted, $hasTTF, $regularFont);

    // Model badge
    $badge = $modelName . '  Extended';
    $badgeW = getTextWidth($badge, $smallSize, $hasTTF, $regularFont);
    drawText($img, $width - $pad - $badgeW - 36, $fY + 14, $badge, $smallSize, $muted, $hasTTF, $regularFont);

    // Send button
    $sx = $width - $pad - 14;
    $sy = $fY + 24;
    imagefilledellipse($img, $sx, $sy, 24, 24, $orange);
    imageline($img, $sx, $sy - 5, $sx, $sy + 4, $white);
    imageline($img, $sx - 4, $sy - 1, $sx, $sy - 5, $white);
    imageline($img, $sx + 4, $sy - 1, $sx, $sy - 5, $white);

    // + button on left
    $plusX = $pad + 6;
    $plusY = $fY + 24;
    imageellipse($img, $plusX, $plusY, 20, 20, $borderLight);
    imageline($img, $plusX - 4, $plusY, $plusX + 4, $plusY, $muted);
    imageline($img, $plusX, $plusY - 4, $plusX, $plusY + 4, $muted);

    // ── Disclaimer ───────────────────────────────────────────────────────
    $disc   = 'Claude is AI and can make mistakes. Please double-check responses.';
    $discW  = getTextWidth($disc, $hasTTF ? 7 : 1, $hasTTF, $regularFont);
    $discX  = (int)(($width - $discW) / 2);
    drawText($img, $discX, $totalH - $discH, $disc, $hasTTF ? 7 : 1, $muted, $hasTTF, $regularFont);

    return $img;
}


// ═════════════════════════════════════════════════════════════════════════════
//  HELPERS
// ═════════════════════════════════════════════════════════════════════════════

function drawText($img, $x, $y, $text, $size, $color, $hasTTF, $font) {
    if ($hasTTF && $font) {
        imagettftext($img, $size, 0, $x, $y + (int)($size + 3), $color, $font, $text);
    } else {
        $gdFont = max(1, min(5, (int)$size));
        imagestring($img, $gdFont, $x, $y, $text, $color);
    }
}

function drawClaudeIcon($img, $x, $y, $color) {
    $cx = $x + 10;
    $cy = $y + 10;
    for ($a = 0; $a < 360; $a += 60) {
        $r = deg2rad($a);
        $ex = $cx + (int)(8 * cos($r));
        $ey = $cy + (int)(8 * sin($r));
        imagesetthickness($img, 2);
        imageline($img, $cx, $cy, $ex, $ey, $color);
    }
    imagesetthickness($img, 1);
    imagefilledellipse($img, $cx, $cy, 5, 5, $color);
}

function drawRoundedRect($img, $x1, $y1, $x2, $y2, $r, $color) {
    imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $color);
    imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $color);
    imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
}

function wrapText($text, $maxW, $size, $hasTTF, $font) {
    $lines = [];
    foreach (explode("\n", $text) as $para) {
        if (trim($para) === '') { $lines[] = ''; continue; }
        $cur = '';
        foreach (explode(' ', $para) as $word) {
            $test = $cur === '' ? $word : "$cur $word";
            if (getTextWidth($test, $size, $hasTTF, $font) > $maxW && $cur !== '') {
                $lines[] = $cur;
                $cur = $word;
            } else {
                $cur = $test;
            }
        }
        if ($cur !== '') $lines[] = $cur;
    }
    return $lines ?: [''];
}

function getTextWidth($text, $size, $hasTTF, $font) {
    if ($hasTTF && $font) {
        $box = imagettfbbox($size, 0, $font, $text);
        return abs($box[2] - $box[0]);
    }
    return strlen($text) * ($size <= 2 ? 6 : 7);
}

function getMaxLineWidth($lines, $size, $hasTTF, $font) {
    $max = 0;
    foreach ($lines as $l) {
        $w = getTextWidth($l, $size, $hasTTF, $font);
        if ($w > $max) $max = $w;
    }
    return $max;
}


// ═════════════════════════════════════════════════════════════════════════════
//  HTML PAGES
// ═════════════════════════════════════════════════════════════════════════════

function showForm($userText, $claudeText, $modelName, $chatTitle) {
    $u = htmlspecialchars($userText);
    $c = htmlspecialchars($claudeText);
    $m = htmlspecialchars($modelName);
    $t = htmlspecialchars($chatTitle);
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Claude Chat Meme Generator</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#1a1a1a;color:#e0e0e0;
  min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}
.box{background:#2a2a2a;border-radius:16px;padding:32px;max-width:560px;width:100%;
  box-shadow:0 8px 32px rgba(0,0,0,.4)}
h1{font-size:22px;margin-bottom:6px;color:#fff}
.sub{color:#888;margin-bottom:24px;font-size:13px}
.spark{color:#cf7234}
label{display:block;font-size:11px;font-weight:700;color:#999;margin-bottom:4px;
  text-transform:uppercase;letter-spacing:.5px}
textarea,input[type=text]{width:100%;padding:10px 12px;border:1px solid #444;border-radius:8px;
  background:#1a1a1a;color:#e0e0e0;font-size:14px;font-family:inherit;margin-bottom:16px;resize:vertical}
textarea:focus,input:focus{outline:none;border-color:#cf7234}
textarea{min-height:70px}
textarea.lg{min-height:130px}
.row{display:flex;gap:12px}
.row>div{flex:1}
button{width:100%;padding:13px;background:#cf7234;color:#fff;border:none;border-radius:10px;
  font-size:15px;font-weight:600;cursor:pointer;transition:background .2s}
button:hover{background:#b8622b}
.hint{font-size:11px;color:#666;margin-top:10px;text-align:center}
.hint code{background:#333;padding:1px 5px;border-radius:3px;font-size:10px}
@media(max-width:500px){.row{flex-direction:column;gap:0}.box{padding:20px}}
</style>
</head>
<body>
<div class="box">
  <h1><span class="spark">&#10038;</span> Claude Meme Generator</h1>
  <p class="sub">Generate fake Claude chat screenshots &mdash; perfect for memes!</p>
  <form method="POST">
    <label>User Message</label>
    <textarea name="user" placeholder="I sinned this morning, what help can you offer?">{$u}</textarea>
    <label>Claude's Response (your fake answer)</label>
    <textarea name="claude" class="lg" placeholder="That's quite an opener! I'm happy to help...&#10;&#10;**If you're looking for spiritual guidance** — I can listen.&#10;&#10;**If it's a coding confession** — I can help absolve that too.">{$c}</textarea>
    <div class="row">
      <div><label>Model Badge</label><input type="text" name="model" value="{$m}" placeholder="Sonnet 4.6"></div>
      <div><label>Chat Title</label><input type="text" name="title" value="{$t}" placeholder="Seeking guidance after sin"></div>
    </div>
    <input type="hidden" name="generate" value="1">
    <button type="submit">Generate Meme Image</button>
  </form>
  <p class="hint">Use <code>**bold**</code> in Claude's response for bold text. Line breaks become new lines.</p>
</div>
</body>
</html>
HTML;
}

function showPreview($base64, $userText, $claudeText, $modelName, $chatTitle) {
    $ue = htmlspecialchars($userText);
    $ce = htmlspecialchars($claudeText);
    $me = htmlspecialchars($modelName);
    $te = htmlspecialchars($chatTitle);
    $dl = http_build_query(['user'=>$userText,'claude'=>$claudeText,'model'=>$modelName,'title'=>$chatTitle,'generate'=>1,'download'=>1]);
    $edit = http_build_query(['user'=>$userText,'claude'=>$claudeText,'model'=>$modelName,'title'=>$chatTitle]);
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Claude Meme</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#1a1a1a;color:#e0e0e0;
  min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:24px 16px}
h1{font-size:20px;margin-bottom:16px;color:#fff}
.preview{background:#2a2a2a;border-radius:12px;padding:16px;margin-bottom:16px;
  box-shadow:0 8px 32px rgba(0,0,0,.4);max-width:100%;overflow-x:auto}
.preview img{max-width:100%;border-radius:6px;display:block}
.actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
a.btn{padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;
  cursor:pointer;transition:background .2s;display:inline-block}
.primary{background:#cf7234;color:#fff}
.primary:hover{background:#b8622b}
.secondary{background:#444;color:#e0e0e0}
.secondary:hover{background:#555}
</style>
</head>
<body>
<h1>&#10038; Your Claude Meme</h1>
<div class="preview"><img src="data:image/png;base64,{$base64}" alt="Claude Chat Meme"></div>
<div class="actions">
  <a class="btn primary" href="?{$dl}">Download PNG</a>
  <a class="btn secondary" href="?{$edit}">Edit</a>
  <a class="btn secondary" href="?">New Meme</a>
</div>
</body>
</html>
HTML;
}
