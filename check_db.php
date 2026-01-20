<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (Schema::hasTable('users')) {
    echo "Current DB Connection: " . DB::connection()->getName() . "\n";
    echo "Table 'users' exists. Count: " . \App\Models\User::count();
} else {
    echo "Table 'users' does not exist.";
}
