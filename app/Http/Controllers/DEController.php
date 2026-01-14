<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DEController extends Controller
{
    /**
     * Display pengajuan akreditasi list
     */
    public function pengajuan()
    {
        return view('de.pengajuan.index');
    }
}
