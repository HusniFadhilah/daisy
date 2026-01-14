<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'superadmin@daisy.lamdepilar.or.id')->first();

if ($user) {
    echo "User ditemukan: {$user->name}\n";
    echo "Email: {$user->email}\n\n";
    
    $testPasswords = [
        '=Secret1234',
        'Secret1234',
        '=secret1234',
    ];
    
    echo "Testing passwords:\n";
    foreach ($testPasswords as $password) {
        $match = Hash::check($password, $user->password);
        echo "Password '{$password}': " . ($match ? "✓ MATCH" : "✗ TIDAK MATCH") . "\n";
    }
    
    echo "\nHash yang tersimpan di database:\n";
    echo substr($user->password, 0, 60) . "...\n";
} else {
    echo "User tidak ditemukan!\n";
}
