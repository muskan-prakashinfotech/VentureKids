<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class FrontendController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()) {
            switch(auth()->user()->group) {
                case 1:
                    return redirect()->route('backend.dashboard');
                case 2:
                    return redirect()->route('school.dashboard');
                case 3:
                    return redirect()->route('trainer.dashboard');
                case 4:
                    return redirect()->route('student.dashboard');
            }
        }
        return redirect()->route('login');
    }

    /**
     * Privacy Policy Page.
     *
     * @return \Illuminate\Http\Response
     */
    public function privacy()
    {
        $body_class = '';

        return view('frontend.privacy', compact('body_class'));
    }

    /**
     * Terms & Conditions Page.
     *
     * @return \Illuminate\Http\Response
     */
    public function terms()
    {
        $body_class = '';

        return view('frontend.terms', compact('body_class'));
    }
}
