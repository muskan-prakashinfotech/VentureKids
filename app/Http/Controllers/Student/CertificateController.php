<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StudentAttendance;
use App\Models\StudentCommunications;
use App\Models\StudentFeedback;
use App\Models\Students;
use App\Models\Submission;
use App\Models\Grade;
use App\Models\StudentCertificates;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use File;
use PDF;

class CertificateController extends Controller
{
    public function studentCertificate()
    {
        $student_id = Session::get('student_id');
        $student = Students::find($student_id);
        $studentLevelCertificate = [];
        $studentGrade = $student->grade_id;
        if(!empty($studentGrade)) {
            $allGrades = Grade::where('is_publish', 1)->whereIn('id',explode(',',$studentGrade))->pluck('grade', 'id');
            $studCertificate = StudentCertificates::select('grade_id')->where(['student_id' => $student_id])->pluck('grade_id')->toArray();
            foreach($allGrades as $gradeId => $gradeName) {
                $studentLevelCertificate[$gradeId]['grade_name'] = $gradeName;
                if(in_array($gradeId, $studCertificate)) {
                    $studentLevelCertificate[$gradeId]['assessment'] = 1;
                } else {
                    $studentLevelCertificate[$gradeId]['assessment'] = 0;
                }
            }
        }
        return view('student.certificate.certificate', ['studentLevelCertificate' => $studentLevelCertificate]);
    }

    public function requestCertificate($gradeId)
    {
        if(!empty($gradeId)) {
            $grade = Grade::find($gradeId);
            if(!empty($grade)) {
                $studentId = Session::get('student_id');
                $student = Students::find($studentId);
                $assessment = StudentCertificates::where([
                    'student_id' => $studentId,
                    'grade_id' => $gradeId
                ])->first();
                if($assessment) {
                    $schoolLogoPath = asset('asset/images/logo.png');
                    if ($student && $student->school && isset($student->school->school_logo)) {
                        $logoPath = Storage::disk('tenant_uploads')->path($student->school->school_logo);
                        if (File::exists($logoPath)) {
                            $schoolLogoPath = url('tenants/'.$student->school->school_logo);
                        }
                    }

                    if ($assessment->pdf_path && File::exists(public_path($assessment->pdf_path))) {
                        $pdfname = $student->name . '_' . $grade->grade . '_' . 'certificate.pdf';
                        return response()->file(public_path($assessment->pdf_path), [
                            'Content-Disposition' => 'attachment; filename="' . $pdfname . '"'
                        ]);
                    }

                    $pdf = PDF::loadView('certificates.studentcertificate', [
                        'student' => $student,
                        'grade' => $grade,
                        'certificate' => $assessment,
                        'custom_quote' => null,
                        'schoolLogoPath' => $schoolLogoPath
                    ]);
                    $pdf->setPaper('A4', 'landscape');
                    $pdfname = $student->name . '_' . $grade->grade . '_' . 'certificate.pdf';
                    return $pdf->download($pdfname);
                } 
            }
        }
        
        return redirect()->back()->with('failed', 'You are not eligible for certificate.');
    }

}
