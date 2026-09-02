<?php
$pngPath = __DIR__ . '/../assets/images/partners/lb-sutton-council.png';
$svgPath = __DIR__ . '/../assets/images/partners/lb-sutton-council.svg';

$pngData = file_get_contents($pngPath);
$base64 = base64_encode($pngData);

$svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 190 50">
  <rect x="2" y="2" width="186" height="46" rx="4" fill="#142c19" stroke="#8e622d" stroke-width="1.5"/>
  <rect x="5" y="5" width="180" height="40" rx="3" fill="none" stroke="#caa06d" stroke-width="0.8"/>
  <rect x="10" y="8" width="170" height="34" rx="3" fill="#ffffff" opacity="0.95"/>
  <image href="data:image/png;base64,{$base64}" x="14" y="9" width="162" height="32" preserveAspectRatio="xMidYMid meet"/>
</svg>
SVG;

file_put_contents($svgPath, $svgContent);
echo "CREATED SUTTON PLAQUE SVG: " . strlen($svgContent) . " bytes\n";
