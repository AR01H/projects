<?php
$url = 'https://www.advocacyforall.org.uk/wp-content/uploads/2024/02/Lb_Sutton-ADVO-768x478.png';
$dest1 = __DIR__ . '/../assets/images/partners/lb-sutton-council.png';
$dest2 = __DIR__ . '/../assets/images/certifications/lb-sutton-council.png';
$dest3 = __DIR__ . '/../assets/images/sugarcane/lb-sutton-council.png';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$data = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code === 200 && !empty($data)) {
    @mkdir(dirname($dest1), 0777, true);
    @mkdir(dirname($dest2), 0777, true);
    @mkdir(dirname($dest3), 0777, true);
    file_put_contents($dest1, $data);
    file_put_contents($dest2, $data);
    file_put_contents($dest3, $data);
    echo "DOWNLOADED SUCCESSFULLY! Size: " . strlen($data) . " bytes\n";
} else {
    echo "FAILED with code: " . $code . "\n";
}
