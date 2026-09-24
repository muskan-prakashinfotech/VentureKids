<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Students;
use Illuminate\Support\Facades\Session;
use App\Models\AdminOthers;
use App\Models\User;

class TermAndPrivacyPolicyController extends Controller
{
    public function term_and_privacy_policy(){
        $user_id = Session::get('user_id');
        $user = User::where('id',$user_id)->first()->toArray();


        $terms = AdminOthers::where('setting_name','student')->first()->toArray();
       
        return view('student.terms-and-privacy.term_and_privacy_policy')->with('terms',$terms)->with('user',$user);
    }

    public function saveTermsAndPrivacyPolicy(Request $request)
    {
        $validated = $request->validate([
            'termandcondition' => 'required',
        ]);

        $user_id = Session::get('user_id');
        $termsandcondition  = $request->termandcondition;

        $user = User::where('id',$user_id)->first();
        $user->termandcondition = $termsandcondition;
        $user->save();

        return redirect()->back()->with('success', 'Terms and condition accepted.');
    }
}