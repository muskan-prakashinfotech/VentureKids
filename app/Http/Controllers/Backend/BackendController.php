<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Students;
use App\Models\Trainer;
use App\Models\Country;

class BackendController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('permission:edit articles')->only('testmiddleware');
    //     $this->middleware('role:admin|writer')->only('testmiddleware');
    // }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['total_school'] = applyCountryScope(School::query(), 'country_id')->count();
        $data['total_student'] = applyCountryScope(Students::query(), 'country_id')->count();
        $data['total_trainer'] = applyCountryScope(Trainer::query(), 'country_id')->count();

        $data['latest_ten_school'] = applyCountryScope(
            School::select(['id', 'user_id', 'school_name', 'country_id'])->with(['user' => function($query) {
            $query->select(['id', 'suspend']);
        }]),
            'country_id'
        )->orderBy('id', 'desc')->take(10)->get()->toArray();

        $getCountry = Country::get(['id', 'name'])->sortBy('name')->toArray();
        foreach($getCountry as $key => $val) {
            $country[$val['id']] = $val;
        }
        $data['country'] = $country;
        return view('backend.index', compact('data'));
    }
}
