<?php

namespace App\Http\Controllers\trainer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\AdminOthers;
use App\Models\User;

class TermsAndPrivacyPolicyController extends Controller
{
     public function termsAndPrivacyPolicy()
    {
        $user_id = Session::get('user_id');
        $user = User::where('id',$user_id)->first()->toArray();


        $terms = AdminOthers::where('setting_name','teacher')->first()->toArray();
        return view('trainer.terms-and-condition.termsandprivacypolicy')->with('terms',$terms)->with('user',$user);
    }
    public function saveTermsAndPrivacyPolicy(Request $request)
    {
        $user_id = Session::get('user_id');
        $termsandcondition  = $request->termandcondition;

        $user = User::where('id',$user_id)->first();
        $user->termandcondition = $termsandcondition;
        $user->save();

        return redirect()->back()->with('success', 'Terms and condition accepted.');
    }
}
