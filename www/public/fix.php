<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('migrate:refresh', [
    '--path' => 'database/migrations/2026_04_24_174900_create_audits_table.php'
]);
echo $kernel->output();
