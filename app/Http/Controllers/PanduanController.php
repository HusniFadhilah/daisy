<?php

namespace App\Http\Controllers;

use App\Support\PanduanLinks;
use Illuminate\Support\Facades\Auth;

class PanduanController extends Controller
{
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->away(PanduanLinks::forRole(Auth::user()->role_selected));
    }

    public function show(string $slug)
    {
        abort(404);
    }
}
