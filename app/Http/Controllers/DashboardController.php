<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $role = $request->user()->role;

        return match ($role) {
            'admin'      => redirect()->route('admin.dashboard'),
            'supervisor' => redirect()->route('supervisor.dashboard'),
            'student'    => redirect()->route('student.dashboard'),
            default      => redirect()->route('profile.edit'),
        };
    }
}