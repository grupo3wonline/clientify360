<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function services(): View
    {
        return view('pages.services');
    }

    public function tools(): View
    {
        return view('pages.tools');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }
}
