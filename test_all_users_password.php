<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== TESTING PASSWORD SEMUA USER ===\n\n";

$users = User::all();
$defaultPassword = '=Secret1234';

foreach ($users as $user) {
    echo "User: {$user->name} ({$user->email})\n";
    
    $testPasswords = [
        '=Secret1234',
        'password',
        'password123',
    ];
    
    $matched = false;
    foreach ($testPasswords as $pwd) {
        if (Hash::check($pwd, $user->password)) {
            echo "  ✓ Password match: '{$pwd}'\n";
            $matched = true;
            break;
        }
    }
    
    if (!$matched) {
        echo "  ✗ Password TIDAK MATCH dengan password standar\n";
    }
    
    echo "\n";
}
