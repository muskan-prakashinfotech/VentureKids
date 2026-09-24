<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

class RealQAssessmentDashboardController extends Controller
{
    public function index()
    {
        return view('backend.realq_assessment.dashboard');
    }
}
