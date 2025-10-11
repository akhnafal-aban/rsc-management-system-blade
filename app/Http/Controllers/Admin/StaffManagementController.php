<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use App\Http\Controllers\Controller;

final class StaffManagementController extends Controller
{
    public function index(): View
    {
        return view('pages.StaffManagement');
    }
}


