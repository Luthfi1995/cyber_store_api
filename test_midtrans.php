<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$serverKey = config('services.midtrans.server_key');
$isProduction = config('services.midtrans.is_production');

echo "Server Key  : " . ($serverKey ?: 'KOSONG!') . PHP_EOL;
echo "Is Production: " . ($isProduction ? 'true' : 'false') . PHP_EOL;
echo "Environment  : " . app()->environment() . PHP_EOL . PHP_EOL;

if (empty($serverKey)) {
    echo "ERROR: Server key kosong, akan menggunakan mock response!" . PHP_EOL;
    exit(1);
}

$authHeader = 'Basic ' . base64_encode($serverKey . ':');
$snapUrl = $isProduction
    ? 'https://app.midtrans.com/snap/v1/transactions'
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

$payload = json_encode([
    'transaction_details' => [
        'order_id' => 'TEST-' . time(),
        'gross_amount' => 100000,
    ],
]);

$ch = curl_init($snapUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $authHeader,
    'Accept: application/json',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Status : " . $httpCode . PHP_EOL;
if ($curlError) {
    echo "cURL Error  : " . $curlError . PHP_EOL;
}
echo "Response    : " . $response . PHP_EOL;
