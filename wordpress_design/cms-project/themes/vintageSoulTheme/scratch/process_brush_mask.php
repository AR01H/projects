<?php
$sourcePath = 'C:/Users/Akhilesh/.gemini/antigravity-ide/brain/0a1ef30c-283e-40df-882a-97da8bebe8d5/.user_uploaded/media_1788287573469.png';
$destPng = __DIR__ . '/../assets/images/textures/edge/hero-deckle-mask.png';

$src = imagecreatefrompng($sourcePath);
if (!$src) {
    die("Failed to open source image\n");
}

$srcW = imagesx($src);
$srcH = imagesy($src);

$targetW = 1400;
$targetH = 900;

$out = imagecreatetruecolor($targetW, $targetH);
imagesavealpha($out, true);
imagealphablending($out, false);

// Fill 100% transparent
$transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
imagefilledrectangle($out, 0, 0, $targetW, $targetH, $transparent);

// Full-coverage dry brush mask
$brushW = 700; // transition width
$brushX = 180; // starts at x=180, reaches full solid at x=880

for ($y = 0; $y < $targetH; $y++) {
    $srcY = (int) min($srcH - 1, max(0, ($y / $targetH) * $srcH));
    
    for ($x = 0; $x < $targetW; $x++) {
        if ($x < $brushX) {
            // Left of the brush -> 100% transparent
            $alpha = 127;
        } elseif ($x >= $brushX + $brushW) {
            // Right of the brush -> 100% solid artwork cover
            $alpha = 0;
        } else {
            // Inside transition zone
            $brushRelX = ($x - $brushX) / $brushW;
            $srcX = (int) min($srcW - 1, max(0, $brushRelX * $srcW));
            
            $rgb = imagecolorat($src, $srcX, $srcY);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            
            // Dark ink is opaque artwork (alpha=0), white paper is transparent (alpha=127)
            $lum = ($r * 0.299 + $g * 0.587 + $b * 0.114) / 255.0;
            $inkIntensity = 1.0 - $lum;
            
            // Contrast curve
            $inkIntensity = pow($inkIntensity, 0.85);
            
            // Blend into full solid right coverage
            $coverageBias = pow($brushRelX, 1.4);
            $finalOpacity = max($inkIntensity, $coverageBias);
            
            if ($brushRelX > 0.65) {
                // Smoothly force 100% solid towards the right
                $blendToSolid = ($brushRelX - 0.65) / 0.35;
                $finalOpacity = $finalOpacity * (1 - $blendToSolid) + 1.0 * $blendToSolid;
            }
            
            $finalOpacity = min(1.0, max(0.0, $finalOpacity));
            $alpha = (int) round((1.0 - $finalOpacity) * 127);
        }
        
        $col = imagecolorallocatealpha($out, 255, 255, 255, $alpha);
        imagesetpixel($out, $x, $y, $col);
    }
}

imagepng($out, $destPng, 9);
imagedestroy($out);
imagedestroy($src);

echo "SUCCESSFULLY CREATED FULL COVERAGE DRY-BRUSH MASK: " . filesize($destPng) . " bytes\n";
