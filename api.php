<?php
require __DIR__.'/vendor/autoload.php';

$client = new \GuzzleHttp\Client([
    'base_uri' => 'https://homologation.lydia-app.com',
    
]);
$response = $client->post('/api/request/do.json');

echo $response->getStatusCode();
echo "\n\n";
echo $response->getBody();
echo "\n\n";