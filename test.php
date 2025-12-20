<?php

$apiKey = 'QdmyQgVNsniYZGMMgec94ptkfgfR6TTpIyw2okOm';
$body = ['model' => 'command-a-03-2025', 'message' => 'test'];
$json = json_encode($body, JSON_UNESCAPED_UNICODE);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.cohere.ai/v1/chat',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $json,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json)
    ]
]);

$result = curl_exec($ch);
echo "Status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo "Response: " . $result . "\n";
