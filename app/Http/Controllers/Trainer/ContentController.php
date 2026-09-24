<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Trainercontent;
use App\Models\Contentview;
use App\Models\Trainerlavel;
use App\Models\Trainerstream;
use App\Models\Trainer;
use Illuminate\Support\Facades\Session;
use App\Models\TrainerContentImages;

class ContentController extends Controller
{
    // public function __construct()
    // {
    //     //$this->middleware('auth');
    //     $this->middleware('permission:trainer_edit');
    //     //$this->middleware('role:admin|writer')->only('testmiddleware');

    //     $this->module_name = 'users';
    // }

    public function content_list()
    {
        $allGrade = Trainerlavel::orderBy('display_order_id')->get();
        $trainer_id = Session::get('trainer_id');
        $trainer = Trainer::select('grade_id')->find($trainer_id);
        $hasAccess = false;
        $trainerGradeId = [];
        if($trainer && !empty($trainer->grade_id)) {
            $hasAccess = true;
            $trainerGradeId = $trainer->grade_id;
        }
        
        return view('trainer.content.list', compact('allGrade', 'hasAccess', 'trainerGradeId'));
    }

    public function streamList(Trainerlavel $grade)
    {
        /* START - CHECK TRAINER HAS ACCESS OF LEVEL OR NOT */
        $trainer_id = Session::get('trainer_id');
        $trainer = Trainer::select('grade_id')->find($trainer_id);
        $hasAccess = false;
        if($trainer && !empty($trainer->grade_id) && in_array($grade->id, explode(",", $trainer->grade_id))) {
            $hasAccess = true;
        }
        if(!$hasAccess) {
            return redirect()->route('trainer.content/list.contentList');
        }
        /* END - CHECK TRAINER HAS ACCESS OF LEVEL OR NOT */

        $streams = Trainerstream::with('trainerStreamImages')->where('agegroup_id', $grade->id)->orderBy('display_order_id')->get();
        
        $contents = Trainercontent::with('trainerContentImages')->whereIn('stream_id', $streams->pluck('id'))->where('is_publish', 1)->orderBy('display_order_id')->get();
        foreach($contents as $content) {
            $content->hasAccess = true;
            if(empty($content->video) && empty($content->video_url) && empty($content->drive_url) && empty($content->pdf) && empty($content->worksheet) && empty($content->session_presentation) && empty($content->learning_object) && empty($content->outcome_session) && empty($content->question_access_knowledge) && empty($content->introduce_topic_student) && empty($content->related_activity_one) && empty($content->related_activity_two) && empty($content->vocabulary) && empty($content->tips_of_parents) && empty($content->trainer_content_images)) {
                $content->hasAccess = false;
            }
        }
        
        // if(!$contents->count()) {
        //     abort(404);
        // }
        
        return view('trainer.content.streamlist', compact('grade', 'streams', 'contents'));
    }

    public function contentView($id)
    {
        $trainer_id = Session::get('trainer_id');
        
        $content = Trainercontent::with(['trainerContentImages', 'getagegroup', 'getstream'])->find($id);

        if($content) {
            $content = $content->toArray();

            if(empty($content['video']) && empty($content['video_url']) && empty($content['drive_url']) && empty($content['pdf']) && empty($content['worksheet']) && empty($content['session_presentation']) && empty($content['learning_object']) && empty($content['outcome_session']) && empty($content['question_access_knowledge']) && empty($content['introduce_topic_student']) && empty($content['related_activity_one']) && empty($content['related_activity_two']) && empty($content['vocabulary']) && empty($content['tips_of_parents']) && empty($content['trainer_content_images'])) {
                return redirect()->route('trainer.contentlist.streamlist', $content['agegroup_id']);
            }
            
            /* START - CHECK TRAINER HAS ACCESS OF LEVEL OR NOT */
            $trainer = Trainer::select('grade_id')->find($trainer_id);
            $hasAccess = false;
            if($trainer && !empty($trainer->grade_id) && in_array($content['agegroup_id'], explode(",", $trainer->grade_id))) {
                $hasAccess = true;
            }
            if(!$hasAccess) {
                return redirect()->route('trainer.content/list.contentList');
            }
            /* END - CHECK TRAINER HAS ACCESS OF LEVEL OR NOT */

       
            /* START - CHECK CONTENT IS PUBLISHED */
            if(!$content['is_publish']) {
                abort(404);
            }
            /* END - CHECK CONTENT IS PUBLISHED */
            $viewed = Contentview::where(['content_id' => $id, 'trainer_id' => $trainer_id])->count();
        } else {
            return redirect()->route('trainer.content/list.contentList');
        }
        
        return view('trainer.content.view_content', compact('content', 'viewed'));
    }

    public function finishVideo(Trainercontent $content)
    {
        $trainer = Trainer::find(Session::get('trainer_id'));
        Contentview::updateOrcreate([
            'content_id' => $content->id,
            'trainer_id' => $trainer->id,
        ], []);

        $stream = Trainerstream::find($content->stream_id);

        return redirect()->route('trainer.contentlist.streamlist', $stream->agegroup_id);
    }

    public function pdfView($trainerContentId)
    {
        $pdf = Trainercontent::find($trainerContentId, ['pdf']);
        if (!empty($pdf)) {
            $pdf = $pdf->toArray()['pdf'];

            return response()->file(public_path('/files/content/trainer/'.$pdf), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$pdf.'"'
            ]);
        }        
    }

    public function contentImagesDownload($trainerContentId)
    {
        $trainerContentImages = TrainerContentImages::where('trainercontents_id', $trainerContentId)->pluck('attachment')->toArray();

        $zip_file = "content-images({$trainerContentId}).zip";
        $basePath = "";
        $zip = new \ZipArchive();
        $res = $zip->open(public_path($zip_file), \ZipArchive::CREATE);
        if ($res == TRUE)
            {
                foreach ($trainerContentImages as $contentImage) {
                    $zip->addFromString($contentImage, file_get_contents(public_path('/files/content/trainer/'.$contentImage)));
                }
                $zip->close();
                return response()->download($zip_file)->deleteFileAfterSend(true);
            }
    }
}
