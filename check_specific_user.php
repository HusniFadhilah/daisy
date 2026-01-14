<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== CARI USER DENGAN KATA 'tesfixx' atau 'gmail' ===\n\n";

$users = User::where('email', 'like', '%tesfixx%')
    ->orWhere('email', 'like', '%gmail%')
    ->orWhere('name', 'like', '%tesfixx%')
    ->get();

if ($users->isEmpty()) {
    echo "Tidak ada user dengan kata 'tesfixx' atau 'gmail'\n";
} else {
    foreach ($users as $user) {
        echo "ID: {$user->id}\n";
        echo "Nama: {$user->name}\n";
        echo "Email: {$user->email}\n";
        
        $testPasswords = ['password', 'password123', '=Secret1234'];
        foreach ($testPasswords as $pwd) {
            if (Hash::check($pwd, $user->password)) {
                echo "Password: '{$pwd}' ✓\n";
                break;
            }
        }
        echo str_repeat("-", 50) . "\n";
    }
}
