<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'superadmin@daisy.lamdepilar.or.id')->first();

if ($user) {
    $newPassword = '=Secret1234';
    $user->password = Hash::make($newPassword);
    $user->save();
    
    echo "✓ Password berhasil direset!\n\n";
    echo "Email: {$user->email}\n";
    echo "Password baru: {$newPassword}\n\n";
    
    // Verify
    if (Hash::check($newPassword, $user->password)) {
        echo "✓ Verifikasi berhasil! Password sudah benar.\n";
    } else {
        echo "✗ Verifikasi gagal!\n";
    }
} else {
    echo "User tidak ditemukan!\n";
}
