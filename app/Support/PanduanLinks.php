<?php

namespace App\Support;

use App\Models\AppSetting;
use Throwable;

class PanduanLinks
{
    public static function all(): array
    {
        return collect(config('panduan.roles', []))
            ->map(function (array $item, string $key) {
                $item['key'] = $key;
                $item['url'] = self::get($key);

                return $item;
            })
            ->all();
    }

    public static function get(string $key): string
    {
        $default = config("panduan.roles.{$key}.url", '');

        try {
            return AppSetting::where('key', self::settingKey($key))->value('value') ?: $default;
        } catch (Throwable) {
            return $default;
        }
    }

    public static function forRole(?string $role): string
    {
        $key = self::keyForRole($role);

        return self::get($key);
    }

    public static function keyForRole(?string $role): string
    {
        foreach (config('panduan.roles', []) as $key => $item) {
            if (in_array($role, $item['roles'] ?? [], true)) {
                return $key;
            }
        }

        return 'superadmin';
    }

    public static function settingKey(string $key): string
    {
        return "panduan.links.{$key}";
    }
}
