<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Contentview;
use App\Models\Meeting;
use App\Models\RequestedCertificate;
use App\Models\Trainer;
use Illuminate\Support\Facades\Session;
use PDF;

class CertificateController extends Controller
{
    public function trainerCertificate()
    {
        $trainerId = Session::get('trainer_id');
        
        $trainer = Trainer::where('id', $trainerId)->first();
        
        $downloadCertificate = 0;
        
        if($trainer->assessment_done == 1 && $trainer->demo_video == 1 && $trainer->training_hour == 1) {
            $downloadCertificate = 1;
        }

        return view('trainer.certificate.certificate', compact('downloadCertificate'));
    }

    public function requestCertificate()
    {
        $trainerId = Session::get('trainer_id');
        
        $trainer = Trainer::where('id', $trainerId)->first();
        
        if($trainer->assessment_done == 1 && $trainer->demo_video == 1 && $trainer->training_hour == 1) {

            $pdf = PDF::loadView('certificates.trainercertificate', ['trainer' => $trainer]);
            
            $pdf->setPaper('A4', 'landscape');
            
            $pdfname = $trainer->trainer_name . '_' . 'certificate.pdf';

            return $pdf->download($pdfname);

        } else {

            return redirect()->back()->with('failed', 'You are not eligible for certificate.');
            
        }
    }
}
