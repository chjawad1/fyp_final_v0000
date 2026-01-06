<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // For now, we just return the view.
        // Later, we'll pass data like user counts, project stats, etc.
        return view('admin.dashboard');
    }
}