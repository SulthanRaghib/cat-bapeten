<?php
declare(strict_types=1);

/**
 * CAT BAPETEN — PWA Asset Generator (source-icon build)
 *
 * Menghasilkan dari source-icon.png:
 *   - Standard icons      : icon-{size}.png           (72–512px, 8 ukuran)
 *   - Maskable icons      : icon-maskable-{size}.png  (192 & 512px, safe zone 20%)
 *   - Apple splash screens: splash-{w}x{h}.png        (13 ukuran perangkat iOS)
 *
 * Jalankan sekali: php public/pwa/generate-icons.php
 * Membutuhkan ekstensi PHP GD.
 */

if (! extension_loaded('gd')) {
    die("Ekstensi GD tidak tersedia. Install: apt-get install php-gd\n");
}

$dir        = __DIR__;
$sourceFile = $dir . '/source-icon.png';

if (! file_exists($sourceFile)) {
    die("source-icon.png tidak ditemukan di: {$sourceFile}\n");
}

// ─── Warna background dari ikon sumber ────────────────────────────────────────
// Diambil dari analisis: sudut ikon adalah #01060F (navy gelap)
define('BG',    [1,   6,  16]);   // #01060F — navy gelap dari source-icon.png
define('WHITE', [255, 255, 255]);

// ─── Helper: buat canvas dengan background navy ───────────────────────────────
function makeNavyCanvas(int $w, int $h): \GdImage
{
    $img = imagecreatetruecolor($w, $h);
    imageantialias($img, true);
    $bg = imagecolorallocate($img, ...BG);
    imagefill($img, 0, 0, $bg);
    return $img;
}

// ─── Helper: load source dan resize ke dalam canvas di posisi tertentu ────────
function pasteSource(\GdImage $canvas, string $sourceFile, int $dstX, int $dstY, int $dstW, int $dstH): void
{
    $src  = imagecreatefrompng($sourceFile);
    $srcW = imagesx($src);
    $srcH = imagesy($src);
    imagecopyresampled($canvas, $src, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
    imagedestroy($src);
}

// ═══════════════════════════════════════════════════════════════════════════════
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  1. Standard Icons (resize langsung dari source)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

foreach ([72, 96, 128, 144, 152, 192, 384, 512] as $size) {
    $canvas = makeNavyCanvas($size, $size);
    pasteSource($canvas, $sourceFile, 0, 0, $size, $size);
    imagepng($canvas, "{$dir}/icon-{$size}.png", 6);
    imagedestroy($canvas);
    echo "  + icon-{$size}.png\n";
}

// ═══════════════════════════════════════════════════════════════════════════════
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  2. Maskable Icons (icon di safe zone 60% tengah)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
// Android memotong ikon ke bulat/kotak-bulat. Konten HARUS di dalam
// 60% area tengah (20% padding tiap sisi). Background solid navy.

foreach ([192, 512] as $size) {
    $canvas   = makeNavyCanvas($size, $size);
    $inner    = (int) ($size * 0.6);           // ukuran ikon di dalam safe zone
    $offset   = (int) (($size - $inner) / 2);  // offset untuk tengah-tengah
    pasteSource($canvas, $sourceFile, $offset, $offset, $inner, $inner);
    imagepng($canvas, "{$dir}/icon-maskable-{$size}.png", 6);
    imagedestroy($canvas);
    echo "  + icon-maskable-{$size}.png\n";
}

// ═══════════════════════════════════════════════════════════════════════════════
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  3. Apple Splash Screens (13 perangkat iOS)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
// Strategi: render di ukuran LOGIS (dp) agar teks proporsional,
// lalu scale up ke piksel fisik via imagecopyresampled.

// [physW, physH, logW, logH, label]
$splashes = [
    [750,  1334, 375, 667,  'iPhone SE / iPhone 8'],
    [1242, 2208, 414, 736,  'iPhone 8 Plus'],
    [1125, 2436, 375, 812,  'iPhone X / 11 Pro / 12 mini'],
    [828,  1792, 414, 896,  'iPhone XR / 11'],
    [1242, 2688, 414, 896,  'iPhone XS Max / 11 Pro Max'],
    [1170, 2532, 390, 844,  'iPhone 12 / 13 / 14'],
    [1284, 2778, 428, 926,  'iPhone 12/13 Pro Max / 14 Plus'],
    [1179, 2556, 393, 852,  'iPhone 14 Pro / 15 / 15 Pro'],
    [1290, 2796, 430, 932,  'iPhone 14/15 Pro Max'],
    [1488, 2266, 744, 1133, 'iPad mini 6'],
    [1640, 2360, 820, 1180, 'iPad Air 5 / iPad 10'],
    [1668, 2388, 834, 1194, 'iPad Pro 11"'],
    [2048, 2732, 1024, 1366, 'iPad Pro 12.9"'],
];

foreach ($splashes as [$pw, $ph, $lw, $lh, $label]) {
    // ── Gambar di ukuran logis ──
    $logical = makeNavyCanvas($lw, $lh);
    $fg      = imagecolorallocate($logical, ...WHITE);
    $cx      = (int) ($lw / 2);

    // Icon: lebar 38% layar, posisi vertikal 36%–top
    $iconSize = (int) ($lw * 0.38);
    $iconX    = (int) (($lw - $iconSize) / 2);
    $iconY    = (int) ($lh * 0.33);
    pasteSource($logical, $sourceFile, $iconX, $iconY, $iconSize, $iconSize);

    // "CAT BAPETEN" — tepat di bawah icon
    $tF     = 5;
    $title  = 'CAT BAPETEN';
    $tW     = imagefontwidth($tF) * strlen($title);
    $tY     = $iconY + $iconSize + (int) ($lh * 0.04);
    imagestring($logical, $tF, $cx - (int) ($tW / 2), $tY, $title, $fg);

    // Subtitle kecil
    $sF  = 3;
    $sub = 'Sistem Computer Assisted Test';
    $sW  = imagefontwidth($sF) * strlen($sub);
    $sY  = $tY + imagefontheight($tF) + (int) ($lh * 0.01);
    imagestring($logical, $sF, $cx - (int) ($sW / 2), $sY, $sub, $fg);

    // Footer
    $bF     = 2;
    $bottom = 'Badan Pengawas Tenaga Nuklir';
    $bW     = imagefontwidth($bF) * strlen($bottom);
    imagestring($logical, $bF, $cx - (int) ($bW / 2), (int) ($lh * 0.90), $bottom, $fg);

    // ── Scale up ke piksel fisik ──
    $physical = imagecreatetruecolor($pw, $ph);
    imagecopyresampled($physical, $logical, 0, 0, 0, 0, $pw, $ph, $lw, $lh);
    imagedestroy($logical);

    imagepng($physical, "{$dir}/splash-{$pw}x{$ph}.png", 6);
    imagedestroy($physical);
    echo "  + splash-{$pw}x{$ph}.png  [{$label}]\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Semua aset PWA berhasil dibuat!\n";
echo "  Direktori: {$dir}/\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
