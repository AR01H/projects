<?php
// Generate a high-resolution authentic ink-spatter & dry-brush deckle mask (1200x900)
$width = 1200;
$height = 900;

$im = imagecreatetruecolor($width, $height);
imagesavealpha($im, true);
imagealphablending($im, false);

// Fill 100% transparent
$transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefilledrectangle($im, 0, 0, $width, $height, $transparent);

// Deckle cut contour boundary curve x(y)
function get_deckle_x($y, $height) {
    $normY = $y / $height;
    // Organic torn curve
    $baseX = 260 + sin($normY * 3.14159 * 1.5) * 80 - cos($normY * 6.28) * 40;
    
    // Low frequency tears
    $tear = sin($y * 0.035) * 14 + cos($y * 0.08) * 8;
    
    // High frequency micro-roughness
    $micro = sin($y * 0.25) * 3 + cos($y * 0.55) * 2;
    
    return $baseX + $tear + $micro;
}

// Pseudo-random deterministic noise hash
function pnoise($x, $y, $seed = 42) {
    $n = sin($x * 12.9898 + $y * 78.233 + $seed * 43.123) * 43758.5453;
    return $n - floor($n);
}

// 1. Generate base smooth opacity map + dry-brush texture
for ($y = 0; $y < $height; $y++) {
    $edgeX = get_deckle_x($y, $height);
    $brushZone = 320; // 320px organic transition brush zone
    
    for ($x = 0; $x < $width; $x++) {
        if ($x < $edgeX - 40) {
            $alpha = 127; // Transparent
        } elseif ($x >= $edgeX + $brushZone) {
            $alpha = 0;   // Fully Opaque
        } else {
            // Distance into brush zone
            $dist = ($x - $edgeX) / $brushZone;
            
            // Base gradient
            $baseOpacity = pow(max(0, min(1, $dist)), 1.2);
            
            // Multi-octave stipple spatter noise
            $n1 = pnoise($x * 0.2, $y * 0.2, 101);
            $n2 = pnoise($x * 0.05, $y * 0.05, 202);
            $n3 = pnoise($x * 0.8, $y * 0.8, 303);
            $fineGrain = ($n1 * 0.5 + $n2 * 0.35 + $n3 * 0.15);
            
            // Apply heavy ink-spatter texture in the transition region
            $stippleNoise = ($fineGrain - 0.5) * 0.65;
            
            // Dry brush striations
            $brushFiber = sin(($y + sin($x * 0.02) * 20) * 0.12) * 0.12;
            
            $finalOpacity = max(0, min(1, $baseOpacity + $stippleNoise + $brushFiber));
            
            // Non-linear threshold for crisp ink droplets
            if ($dist < 0.35 && $fineGrain > 0.82) {
                // Stray droplets in the empty zone
                $finalOpacity = max($finalOpacity, $fineGrain * 0.9);
            }
            if ($dist > 0.45 && $dist < 0.85 && $fineGrain < 0.18) {
                // Micro paper voids inside the ink body
                $finalOpacity *= 0.6;
            }
            
            $alpha = (int) round((1 - $finalOpacity) * 127);
            $alpha = max(0, min(127, $alpha));
        }
        
        $col = imagecolorallocatealpha($im, 255, 255, 255, $alpha);
        imagesetpixel($im, $x, $y, $col);
    }
}

// 2. Add organic splatter spray droplets (large, medium, fine droplets)
$dropletCount = 1800;
for ($i = 0; $i < $dropletCount; $i++) {
    $y = mt_rand(0, $height - 1);
    $edgeX = get_deckle_x($y, $height);
    
    // Spread droplets around the transition zone (-60px to +280px)
    $xOffset = mt_rand(-60, 280);
    $x = (int) ($edgeX + $xOffset);
    
    if ($x >= 0 && $x < $width) {
        $radius = mt_rand(1, 3);
        // Probability higher near edge
        $dropAlpha = mt_rand(0, 40); // High opacity droplet
        $dropCol = imagecolorallocatealpha($im, 255, 255, 255, $dropAlpha);
        imagefilledellipse($im, $x, $y, $radius * 2, $radius * 2, $dropCol);
    }
}

$destPng = __DIR__ . '/../assets/images/textures/edge/hero-deckle-mask.png';
imagepng($im, $destPng, 9);
imagedestroy($im);

echo "GENERATED INK-SPATTER DECKLE MASK: " . filesize($destPng) . " bytes\n";
