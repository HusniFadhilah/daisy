<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$userId = 34;
$user = User::find($userId);

if ($user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    
    $passwords = ['password', 'password123', '=Secret1234'];
    foreach ($passwords as $pwd) {
        if (Hash::check($pwd, $user->password)) {
            echo "Password: '{$pwd}'\n";
            break;
        }
    }
} else {
    echo "User ID {$userId} tidak ditemukan\n";
    echo "\nUser terakhir:\n";
    $lastUser = User::orderBy('id', 'desc')->first();
    if ($lastUser) {
        echo "ID: {$lastUser->id}\n";
        echo "Name: {$lastUser->name}\n";
        echo "Email: {$lastUser->email}\n";
    }
}
