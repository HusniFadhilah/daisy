<?php

if (!function_exists('getRoleIcon')) {
    function getRoleIcon($roleName): string
    {
        $icons = [
            'super_admin' => 'shield-fill-check',
            'sekretariat' => 'person-badge',
            'asesor' => 'clipboard-check',
            'asesor_banding' => 'clipboard-data',
            'validator' => 'check2-circle',
            'verifikator' => 'shield-check',
            'admin_univ' => 'building',
            'admin_prodi' => 'mortarboard',
            'keuangan_lamdepilar' => 'cash-coin',
            'default' => 'person',
        ];

        return $icons[$roleName] ?? $icons['default'];
    }
}

if (!function_exists('getRoleDescription')) {
    function getRoleDescription($roleName): string
    {
        $descriptions = [
            'super_admin' => 'Akses penuh ke seluruh sistem',
            'sekretariat' => 'LAMDEPILAR - Review dan evaluasi asesmen akreditasi',
            'asesor' => 'Melakukan penilaian dokumen akreditasi',
            'asesor_banding' => 'Melakukan penilaian surveillance banding',
            'validator' => 'Melakukan validasi hasil penilaian asesor',
            'verifikator' => 'Melakukan verifikasi dokumen dan data',
            'admin_univ' => 'Unit Pengelola Perguruan Tinggi',
            'admin_prodi' => 'Unit Pengelola Program Studi',
            'keuangan_lamdepilar' => 'Melakukan validasi pembayaran akreditasi',
            'default' => 'Pengguna umum',
        ];

        return $descriptions[$roleName] ?? 'User role';
    }
}
