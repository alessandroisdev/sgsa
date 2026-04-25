<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/v1/auth/login', 'POST', [
    'email' => 'user@user.com',
    'password' => '12345678'
]);
$request->headers->set('Accept', 'application/json');
$request->headers->set('Content-Type', 'application/json');

$start = microtime(true);
$response = $kernel->handle($request);
$end = microtime(true);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 500) . "\n";
echo "Time: " . ($end - $start) . " seconds\n";
