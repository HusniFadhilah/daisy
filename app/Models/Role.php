<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ID_ROLE_SUPERADMIN = 1;
    public const ID_ROLE_DE = 2;
    public const ID_ROLE_ASESOR = 3;
    public const ID_ROLE_VALIDATOR = 4;
    public const ID_ROLE_VERIFIKATOR = 5;
    public const ID_ROLE_ADMIN_UNIV = 6;
    public const ID_ROLE_ADMIN_PT = 7;
    public const ID_ROLE_KEUANGAN = 8;
    public const ID_ROLE_ASESOR_BANDING = 9;
    public const ID_ROLE_DEFAULT = 10;

    protected $fillable = [
        'name',
        'alias',
    ];

    public static function getRoleAlias($roleID)
    {
        $role = self::findOrFail($roleID);
        return $role ? $role->alias : null;
    }
}
