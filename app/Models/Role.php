<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
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
