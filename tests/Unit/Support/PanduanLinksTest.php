<?php

namespace Tests\Unit\Support;

use App\Support\PanduanLinks;
use Tests\TestCase;

class PanduanLinksTest extends TestCase
{
    public function test_settingKey_returns_panduan_prefix(): void
    {
        $this->assertSame('panduan.links.asesor', PanduanLinks::settingKey('asesor'));
        $this->assertSame('panduan.links.superadmin', PanduanLinks::settingKey('superadmin'));
        $this->assertSame('panduan.links.de', PanduanLinks::settingKey('de'));
    }

    public function test_keyForRole_returns_superadmin_for_null(): void
    {
        $this->assertSame('superadmin', PanduanLinks::keyForRole(null));
    }

    public function test_keyForRole_returns_superadmin_for_unknown_role(): void
    {
        $this->assertSame('superadmin', PanduanLinks::keyForRole('unknown_role'));
        $this->assertSame('superadmin', PanduanLinks::keyForRole(''));
    }

    public function test_keyForRole_returns_de_for_sekretariat(): void
    {
        $this->assertSame('de', PanduanLinks::keyForRole('sekretariat'));
    }

    public function test_keyForRole_returns_asesor_for_asesor_role(): void
    {
        $this->assertSame('asesor', PanduanLinks::keyForRole('asesor'));
    }

    public function test_keyForRole_returns_asesor_for_asesor_banding(): void
    {
        $this->assertSame('asesor', PanduanLinks::keyForRole('asesor_banding'));
    }

    public function test_keyForRole_returns_prodi_for_admin_prodi(): void
    {
        $this->assertSame('prodi', PanduanLinks::keyForRole('admin_prodi'));
    }

    public function test_keyForRole_returns_prodi_for_admin_univ(): void
    {
        $this->assertSame('prodi', PanduanLinks::keyForRole('admin_univ'));
    }

    public function test_keyForRole_returns_keuangan_for_keuangan_lamdepilar(): void
    {
        $this->assertSame('keuangan', PanduanLinks::keyForRole('keuangan_lamdepilar'));
    }

    public function test_keyForRole_returns_superadmin_for_super_admin(): void
    {
        $this->assertSame('superadmin', PanduanLinks::keyForRole('super_admin'));
    }

    public function test_keyForRole_returns_validator_for_validator(): void
    {
        $this->assertSame('validator', PanduanLinks::keyForRole('validator'));
    }

    public function test_keyForRole_returns_validator_for_verifikator(): void
    {
        $this->assertSame('validator', PanduanLinks::keyForRole('verifikator'));
    }

    public function test_get_returns_non_empty_url_for_valid_key(): void
    {
        $url = PanduanLinks::get('asesor');
        $this->assertNotEmpty($url);
        $this->assertStringStartsWith('https://', $url);
    }

    public function test_get_returns_empty_string_for_unknown_key(): void
    {
        $url = PanduanLinks::get('unknown_key_xyz');
        $this->assertIsString($url);
    }

    public function test_forRole_delegates_to_get_and_keyForRole(): void
    {
        $url = PanduanLinks::forRole('sekretariat');
        $expected = PanduanLinks::get(PanduanLinks::keyForRole('sekretariat'));
        $this->assertSame($expected, $url);
    }

    public function test_forRole_null_returns_superadmin_url(): void
    {
        $url = PanduanLinks::forRole(null);
        $expected = PanduanLinks::get('superadmin');
        $this->assertSame($expected, $url);
    }

    public function test_all_returns_array(): void
    {
        $all = PanduanLinks::all();
        $this->assertIsArray($all);
        $this->assertNotEmpty($all);
    }

    public function test_all_contains_expected_role_keys(): void
    {
        $all = PanduanLinks::all();
        foreach (['asesor', 'de', 'keuangan', 'prodi', 'superadmin', 'validator'] as $key) {
            $this->assertArrayHasKey($key, $all, "Missing key: {$key}");
        }
    }

    public function test_all_items_contain_key_and_url(): void
    {
        foreach (PanduanLinks::all() as $key => $item) {
            $this->assertArrayHasKey('key', $item, "Missing 'key' in {$key}");
            $this->assertArrayHasKey('url', $item, "Missing 'url' in {$key}");
            $this->assertSame($key, $item['key'], "Key mismatch for {$key}");
        }
    }
}
