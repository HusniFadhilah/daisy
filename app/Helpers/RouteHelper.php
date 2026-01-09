<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;

class RouteHelper
{
    /**
     * Encrypt ID untuk route
     */
    public static function encryptId($id)
    {
        return Crypt::encryptString($id);
    }

    /**
     * Decrypt ID dari route
     */
    public static function decryptId($encrypted)
    {
        try {
            return Crypt::decryptString($encrypted);
        } catch (\Exception $e) {
            abort(404, 'Invalid token');
        }
    }
}
