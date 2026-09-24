<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

class ProjectManagementDashboardController extends Controller
{
    public function index()
    {
        return view('backend.project_management.dashboard');
    }
}
