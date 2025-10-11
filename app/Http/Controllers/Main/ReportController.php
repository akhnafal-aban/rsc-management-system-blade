<?php

declare(strict_types=1);

namespace App\Http\Controllers\Main;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        return view('pages.Reports');
    }
}


