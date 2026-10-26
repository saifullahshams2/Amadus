<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Display the main public landing page.
     */
    public function index(Request $request): View
    {
        return view('landing.index', [
            'user' => Auth::user(),
        ]);
    }
}
