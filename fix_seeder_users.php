<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== UPDATE SEEDER USERS ===\n\n";

$seederEmails = [
    'superadmin@daisy.lamdepilar.or.id',
    'sekretariat@lamdepilar.or.id',
    'budi.santoso@daisy.lamdepilar.or.id',
    'asesor1@daisy.lamdepilar.or.id',
    'asesor2@daisy.lamdepilar.or.id',
    'siti.nurhaliza@daisy.lamdepilar.or.id',
    'ahmad.fauzi@daisy.lamdepilar.or.id',
    'dewi.lestari@daisy.lamdepilar.or.id',
    'validator1@daisy.lamdepilar.or.id',
    'validator2@daisy.lamdepilar.or.id',
    'eko.prasetyo@daisy.lamdepilar.or.id',
    'verifikator1@daisy.lamdepilar.or.id',
    'verifikator2@daisy.lamdepilar.or.id',
    'rudi.hermawan@daisy.lamdepilar.or.id',
    'rina.wati@daisy.lamdepilar.or.id',
    'pt1@daisy.lamdepilar.or.id',
    'pt2@daisy.lamdepilar.or.id',
    'upps1@daisy.lamdepilar.or.id',
    'upps2@daisy.lamdepilar.or.id',
    'default@daisy.lamdepilar.or.id',
    'remahankecil@gmail.com',
];

$updated = \App\Models\User::whereIn('email', $seederEmails)
    ->update(['must_change_password' => false]);

echo "Updated {$updated} seeder users to must_change_password = false\n\n";

echo "Sekarang coba login dengan:\n";
echo "Email: superadmin@daisy.lamdepilar.or.id\n";
echo "Password: =Secret1234\n";
