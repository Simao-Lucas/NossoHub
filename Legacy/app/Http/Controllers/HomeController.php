<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home.index', [
            'since' => '2025-07-28T20:30:00-03:00',
        ]);
    }
}
