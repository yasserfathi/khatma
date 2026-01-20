<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = \App\Models\User::create([
        'name' => 'Test User',
        'email' => null,
        'phone' => '1234567890',
        'password' => bcrypt('password'),
    ]);

    echo "User created successfully.\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . ($user->email ?? 'NULL') . "\n";
    echo "Phone: " . $user->phone . "\n";

    // Test Sanctum Token
    $token = $user->createToken('test-token');
    echo "Sanctum Token Created: " . $token->plainTextToken . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
