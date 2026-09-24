<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\PendingSchoolApprovedMail;
use App\Models\PendingSchool;
use App\Models\User;
use App\Services\SchoolOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PendingSchoolController extends Controller
{
    public function index()
    {
        $pendingSchools = PendingSchool::where('status', 'pending')
            ->latest()
            ->get();

        return view('backend.pending_schools.index', compact('pendingSchools'));
    }

    public function approve(Request $request, PendingSchool $pendingSchool, SchoolOnboardingService $onboardingService)
    {
        if ($pendingSchool->status !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        try {
            $result = $onboardingService->createSchool(array_merge(
                $pendingSchool->form_data,
                [
                    'created_by' => $pendingSchool->submitted_by,
                    'created_type' => 'partner',
                ]
            ));

            $pendingSchool->status = 'approved';
            $pendingSchool->reviewed_by = auth()->id();
            $pendingSchool->reviewed_at = now();
            $pendingSchool->save();

            $submittedBy = User::find($pendingSchool->submitted_by);
            if ($submittedBy && !empty($submittedBy->email)) {
                safeMailAction('pending school approved mail', [
                    'pending_school_id' => $pendingSchool->id,
                    'recipient' => $submittedBy->email,
                    'school_name' => $result['school']->school_name ?? null,
                ], function () use ($submittedBy, $result) {
                    Mail::to($submittedBy->email)->send(
                        new PendingSchoolApprovedMail(
                            $result['school']->school_name,
                            $submittedBy->name ?? 'Partner',
                            $result['school']->official_email_id,
                            route('login')
                        )
                    );
                });
            }

            return redirect()->route('backend.pending-schools.index')
                ->with('message', 'Pending school approved and created successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to approve pending school', [
                'pending_school_id' => $pendingSchool->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, PendingSchool $pendingSchool)
    {
        if ($pendingSchool->status !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $pendingSchool->status = 'rejected';
        $pendingSchool->reviewed_by = auth()->id();
        $pendingSchool->reviewed_at = now();
        $pendingSchool->rejection_reason = $request->input('rejection_reason');
        $pendingSchool->save();

        $submittedBy = User::find($pendingSchool->submitted_by);
        if ($submittedBy && !empty($submittedBy->email)) {
            safeMailAction('pending school rejected mail', [
                'pending_school_id' => $pendingSchool->id,
                'recipient' => $submittedBy->email,
            ], function () use ($submittedBy) {
                Mail::raw(
                    'Your school request has been rejected by the Super Admin.',
                    function ($message) use ($submittedBy) {
                        $message->to($submittedBy->email)->subject('School Request Rejected');
                    }
                );
            });
        }

        return redirect()->route('backend.pending-schools.index')
            ->with('message', 'Pending school rejected.');
    }
}
