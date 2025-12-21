<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Check admin user
$admin = User::where('email', 'admin@daisy.ac.id')->first();

if ($admin) {
    echo "✓ Admin user exists\n";
    echo "Name: {$admin->name}\n";
    echo "Email: {$admin->email}\n";
    echo "Role: {$admin->role}\n";
    echo "Password hash: " . substr($admin->password, 0, 20) . "...\n";
    
    // Test password
    $testPassword = 'password';
    if (Hash::check($testPassword, $admin->password)) {
        echo "✓ Password 'password' is CORRECT\n";
    } else {
        echo "✗ Password 'password' is WRONG\n";
        echo "Updating password...\n";
        $admin->password = Hash::make('password');
        $admin->save();
        echo "✓ Password updated successfully\n";
    }
} else {
    echo "✗ Admin user NOT found. Creating...\n";
    User::create([
        'name' => 'Administrator',
        'email' => 'admin@daisy.ac.id',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    echo "✓ Admin user created\n";
}

echo "\nTotal users in database: " . User::count() . "\n";
