<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== USER TERDAFTAR DI DATABASE ===\n\n";

$users = \App\Models\User::all();

echo "Total user: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Nama: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "Role Selected: {$user->role_selected}\n";
    echo "Must Change Password: " . ($user->must_change_password ? 'true' : 'false') . "\n";
    echo str_repeat("-", 50) . "\n";
}
