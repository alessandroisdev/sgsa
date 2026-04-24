<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('migrate', ['--force' => true]);

// Garante que o administrador padrão não perca acesso!
\App\Models\User::where('email', 'admin@sgsa.com')->update(['role' => 'admin']);

echo $kernel->output();
echo "\nAdmin user 'admin@sgsa.com' set to admin role.";
