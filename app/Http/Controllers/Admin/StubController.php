<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StubController extends Controller
{
    public function home(): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }
}
