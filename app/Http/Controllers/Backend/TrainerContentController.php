<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\Trainercontent;
use App\Models\Grade;
use App\Models\School;
use App\Models\Stream;
use App\Models\Trainerstream;
use App\Models\StudentNotification;
use App\Models\Students;
use App\Models\Studentscontent;
use App\Models\Trainer;
use App\Models\Trainerlavel;
use App\Models\TrainerNotification;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\TrainerContentImages;
use Illuminate\Support\Facades\Log;
use App\Helpers\ChunkUploadHelper;

class TrainerContentController extends Controller
{
    public function storeContent(Request $request)
    {
        $validated = $request->validate([
            'agegroup_id' => 'required',
            'stream_id' => 'required',
            'title' => 'required',
            // 'video'=> 'required_without:video_url|mimes:mp4',
            // 'video_url'=>'required_without:video|url',
        ]);

        // if (!$request->hasFile('video') && empty($request->video_url)) {
        //     return back()->withErrors(["video" => "The video field is required", "video_url" => "The video url field is required"])->withInput();
        // }
        
        if ($request->hasFile('video') && $request->video->getClientMimeType() != 'video/mp4') {
            return back()->withErrors(["video" => "The video must be a file of type: mp4."])->withInput();
        }
        
        if(!$request->hasFile('video') && !empty($request->video_url) && filter_var($request->video_url, FILTER_VALIDATE_URL) === FALSE) {
            return back()->withErrors(["video_url" => "The video url must be a valid URL."])->withInput();
        }
        
        $video_url = null;
        $video_name = null;
        if ($request->hasFile('video')) {
            $file2 = $request->file('video');
            $video_name = pathinfo($file2->getClientOriginalName(), PATHINFO_FILENAME);
            $video = uniqid() . '.' . $file2->getClientOriginalExtension();
            $path = public_path('/video/content/trainer');
            try {
                if (!$file2->move($path, $video)) { 
                    Log::error('Error in uploading video....');
                }
            } catch (\Exception $e) {
                Log::error($e);
            }
            $video_url = $video;
        }

        $pdf_url = null;
        $pdf_name = null;
        if (isset($request->session_pdf)) {
            $file = $request->file('session_pdf');
            $pdf_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('/files/content/trainer/');
            $file->move($path, $filename);
            $pdf_url = $filename;
        }

        $worksheet_url = null;
        $worksheet_name = null;
        if ($request->hasFile('worksheets')) {
            $file = $request->file('worksheets');
            $worksheet_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('/files/content/trainer/');
            $file->move($path, $filename);
            $worksheet_url = $filename;
        }
        
        $content = new Trainercontent;
        $content->agegroup_id = $request->agegroup_id;
        $content->stream_id = $request->stream_id;
        $content->title = $request->title;
        $content->video = isset($video_url) ? $video_url : null;
        $content->video_name = $video_name;
        $content->video_url = isset($request->video_url) ? $request->video_url : null;
        $content->drive_url = isset($request->drive_url) ? $request->drive_url : null;
        $content->pdf = $pdf_url;
        $content->pdf_name = $pdf_name;
        $content->worksheet = $worksheet_url;
        $content->worksheet_name = $worksheet_name;
        $content->session_presentation = $request->uploadedFileName ?? null;
        $content->session_presentation_name = $request->displayFileName ?? null;
        $content->is_publish = $request->contentPublish;
        $content->learning_object = $request->learning_object;
        $content->outcome_session = $request->outcome_session;
        $content->question_access_knowledge = $request->question_access_knowledge;
        $content->introduce_topic_student = $request->introduce_topic_student;
        $content->related_activity_one = $request->related_activity_one;
        $content->related_activity_two = $request->related_activity_two;
        $content->vocabulary = $request->vocabulary;
        $content->tips_of_parents = $request->tips_of_parents;
        $content->display_order_id = $content->getNextDisplayOrderId($request->stream_id);
        $content->save();

        /* START - STORE MULTIPLE IMAGES OF SESSION */
        if (isset($request->session_images)) {
            foreach ($request->file('session_images') as $imagefile) {
                $filename = uniqid() . '.' . $imagefile->getClientOriginalExtension();
                $path = public_path('/files/content/trainer/');
                $imagefile->move($path, $filename);

                $trainerContentImage = new TrainerContentImages;
                $trainerContentImage->trainercontents_id = $content->id;
                $trainerContentImage->attachment = $filename;
                $trainerContentImage->save();
            }
        }
        /* END - STORE MULTIPLE IMAGES OF SESSION */

        
        /*
        //  send Email to all 
        $stream = Trainerstream::with('agegroup')->find($request->stream_id);
        if(!$stream->title){
            $streamtitle = '';
        }
        $trainer = Trainer::all()->toArray();
        $notifications = [];
        if ($trainer) {
            foreach ($trainer as $trainers) {
                $trainer_email[] = $trainers['official_email_id'];
                $obj = [
                    'trainer_id'    => $trainers['id'],
                    'title'         => 'New Content Uploaded',
                    'description'   => $request->title . ' in ' . $stream->title,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
                $notifications[] = $obj;
            }
            $email_body = 'New Content Uploaded';
            file_put_contents('../resources/views/mail.blade.php', $email_body);

            $emaie_data = ['subject'=>'Content Created'];

            safeMailAction('trainer content upload mail', [
                'recipient' => $trainer_email,
            ], function () use ($emaie_data, $trainer_email) {
                $send_mail = Mail::send('mail', $emaie_data, function ($message) use ($emaie_data, $trainer_email) {
                    $message->to($trainer_email)->subject($emaie_data['subject']);
                });
            });
        }

        TrainerNotification::insert($notifications);
        // End Send Mail Section 
        */

        return redirect()->route('backend.contentlist.contentList')->with('success', 'Data Stored successfully.');
    }

    public function addContent()
    {
        $streams = Stream::get()->toArray();
        $AgeGroups = Trainerlavel::get()->toArray();
        return view('backend.content.add_content')->with('streams', $streams)->with('AgeGroups', $AgeGroups);
    }

    public function addTrainerStream(Request $request)
    {
        $request->validate([
            'stream_name' => 'required',
            'level'       => 'required|exists:trainerlavels,id',
        ]);
        $user_id = Session::get('user_id');
        $last_name = Session::get('last_name');
        $first_name = Session::get('first_name');
        $user_name = $first_name . ' ' . $last_name;
        $stream_name = $request->stream_name;

        $stream = new Trainerstream();
        $stream->title = $stream_name;
        $stream->agegroup_id = $request->level;
        $stream->display_order_id = $stream->getNextDisplayOrderId($request->level);
        $stream->creator_id = Auth::id();
        $stream->creator = $user_name;
        $stream->save();

        $get_data = Trainerstream::latest()->first()->toArray();

        return response()->json($get_data);
    }
     
    public function editTrainerStream(Request $request) {
        $data['title'] = $request->title;
        Trainerstream::where("id",$request->id)->update($data);
    }

    public function deleteTrainerStream(Request $request) {
        Trainercontent::where('stream_id', $request->stream)->delete();
        Trainerstream::where("id",$request->stream)->delete();
    }

    public function destoryTrainerStream(Request $request)
    {
        $stream = Trainerstream::find($request->stream);
        $contents = Studentscontent::where('stream_id', $stream->id)->get();
        foreach ($contents as $content) {
            if ($content->video) {
                $destinationPath = public_path('/video/content/');
                FacadesFile::delete($destinationPath . $content->video);
            }
            if ($content->worksheet) {
                $destinationPath = public_path('/files/content/');
                FacadesFile::delete($destinationPath . $content->worksheet);
            }
        }
        $contents = Studentscontent::where('stream_id', $stream->id)->delete();

        $contents = Content::where('stream_id', $stream->id)->get();
        foreach ($contents as $content) {
            if ($content->video) {
                $destinationPath = public_path('/video/content/');
                FacadesFile::delete($destinationPath . $content->video);
            }
            if ($content->worksheet) {
                $destinationPath = public_path('/files/content/');
                FacadesFile::delete($destinationPath . $content->worksheet);
            }
        }
        $contents = Content::where('stream_id', $stream->id)->delete();
        $stream->delete();
    }

    public function changeTrainerStream(Request $request) {
        return Trainerstream::where('agegroup_id', $request->agegroupId)->get();
    }

    public function contentList()
    {
        $allGrade = Trainerlavel::all();
        return view('backend.content.content_list', compact('allGrade'));
    }

    public function streamList(Trainerlavel $grade)
    {
        $streams = Trainerstream::where('agegroup_id', $grade->id)->orderBy('display_order_id')->get();
        $contents = Trainercontent::whereIn('stream_id', $streams->pluck('id'))->orderBy('display_order_id')->get();

        return view('backend.content.streamlist', compact('grade', 'streams', 'contents'));
    }

    public function contentView($id)
    {
        $content = Trainercontent::with(['getstream', 'getagegroup'])->orderBy('display_order_id')->find($id);
        return view('backend.content.view_content')->with('content', $content);
    }

    public function contentEdit($id)
    {
        $streams = Trainerstream::get()->toArray();
        $AgeGroups = Trainerlavel::get()->toArray();
        // $content = Trainercontent::where('id', $id)->orderBy('display_order_id')->toArray();
        $content = Trainercontent::where('id', $id)->get()->toArray(); 
        $contentImages = TrainerContentImages::where('trainercontents_id', $id)->get(); 
        return view('backend.content.edit_content')->with('streams', $streams)->with('AgeGroups', $AgeGroups)->with('content', $content[0])->with('contentImages', $contentImages);
    }

    public function updateContent(Request $request)
    {
        $validated = $request->validate([
            'agegroup_id' => 'required',
            'stream_id' => 'required',
            'title' => 'required',
        ]);
        
        $content = Trainercontent::find($request->id);

        if ($request->hasFile('video')) {
            if ($content->video) {
                $destinationPath = public_path('/video/content/trainer/');
                FacadesFile::delete($destinationPath . $content->video);
            }
            $file2 = $request->file('video');
            $video_name = pathinfo($file2->getClientOriginalName(), PATHINFO_FILENAME);
            $video = uniqid() . '.' . $file2->getClientOriginalExtension();
            $path = public_path('/video/content/trainer/');
            $file2->move($path, $video);
        } else {
            if (!empty($content->video) && $content->video != 'no video') {
                $video = $content->video;
                $video_name = $content->video_name;
            } else {
                $video = null;
                $video_name = null;
            }
        }

        if ($request->session_pdf) {
            if ($content->pdf) {
                $destinationPath = public_path('/files/content/trainer/');
                FacadesFile::delete($destinationPath . $content->pdf);
            }

            $file2 = $request->file('session_pdf');
            $pdf_name = pathinfo($file2->getClientOriginalName(), PATHINFO_FILENAME);
            $pdf = uniqid() . '.' . $file2->getClientOriginalExtension();
            $path = public_path('/files/content/trainer/');
            $file2->move($path, $pdf);
            $pdf_url = $pdf;
        } else {
            if (!empty($content->pdf)) {
                $pdf_url = $content->pdf;
                $pdf_name = $content->pdf_name;
            } else {
                $pdf_url = null;
                $pdf_name = null;
            }
        }

        if ($request->hasFile('worksheets')) {
            if ($content->worksheet) {
                $destinationPath = public_path('/files/content/trainer/');
                FacadesFile::delete($destinationPath . $content->worksheet);
            }
            $file = $request->file('worksheets');
            $worksheet_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('/files/content/trainer/');
            $file->move($path, $filename);
            $worksheet_url = $filename;
        } else {
            if (!empty($content->worksheet) && $content->worksheet != 'no worksheet') {
                $worksheet_url = $content->worksheet;
                $worksheet_name = $content->worksheet_name;
            } else {
                $worksheet_url = null;
                $worksheet_name = null;
            }
        }

        if (isset($request->uploadedFileName) && isset($request->displayFileName)) {
            if ($content->session_presentation) {
                $destinationPath = public_path('/files/content/trainer/');
                FacadesFile::delete($destinationPath . $content->session_presentation);
            }
            $content->session_presentation = $request->uploadedFileName ?? null;
            $content->session_presentation_name = $request->displayFileName ?? null;
        }

        $content->agegroup_id = $request->agegroup_id;
        $content->stream_id = $request->stream_id;
        $content->title = $request->title;
        $content->video = $video;
        $content->video_name = $video_name;
        $content->video_url = isset($request->video_url) ? $request->video_url : null;
        $content->drive_url = isset($request->drive_url) ? $request->drive_url : null;
        $content->pdf = $pdf_url;
        $content->pdf_name = $pdf_name;
        $content->worksheet = $worksheet_url;
        $content->worksheet_name = $worksheet_name;
        $content->is_publish = $request->contentPublish;
        $content->learning_object = $request->learning_object;
        $content->outcome_session = $request->outcome_session;
        $content->question_access_knowledge = $request->question_access_knowledge;
        $content->introduce_topic_student = $request->introduce_topic_student;
        $content->related_activity_one = $request->related_activity_one;
        $content->related_activity_two = $request->related_activity_two;
        $content->vocabulary = $request->vocabulary;
        $content->tips_of_parents = $request->tips_of_parents;
        $content->save();

        /* START SESSION OF IMAGES STORE */
        if (isset($request->session_images)) {
            foreach ($request->file('session_images') as $imagefile) {
                $filename = uniqid() . '.' . $imagefile->getClientOriginalExtension();
                $path = public_path('/files/content/trainer/');
                $imagefile->move($path, $filename);

                $trainerContentImage = new TrainerContentImages;
                $trainerContentImage->trainercontents_id = $content->id;
                $trainerContentImage->attachment = $filename;
                $trainerContentImage->save();
            }
        }
        /* END SESSION OF IMAGES STORE */

        /*
        //  send Email to all 
        $stream = Trainerstream::with('agegroup')->find($request->stream_id);
        $trainer = Trainer::all()->toArray();
        $notifications = [];
        if ($trainer) {
            foreach ($trainer as $trainers) {
                $trainer_email[] = $trainers['official_email_id'];

                $obj = [
                    'trainer_id'    => $trainers['id'],
                    'title'         => 'Content updated',
                    'description'   => $request->title . ' in ' . $stream->title,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
                $notifications[] = $obj;
            }
        }
        TrainerNotification::insert($notifications);
        */
        
        return redirect()->route('backend.contentlist.contentList')->with('update_success', 'Data Updated successfully.');
    }

    public function contentDelete($id)
    {
        $data = Trainercontent::find($id);
        if (!is_null($data)) {
            $data->delete();
        }
        return redirect()->route('backend.contentlist.contentList')->with('delete_success', 'Data deleted successfully.');
    }

    public function updateOrderTrainerstream(request $request)
    {
        if (!empty($request->trainerstreamids)) {
            $trainerstreamids = json_decode($request->trainerstreamids);
            foreach ($trainerstreamids as $index => $trainerStreamId) {
                Trainerstream::where("id",$trainerStreamId)->update(['display_order_id' => ($index+1)]);
            }
        }
    }

    public function updateOrderTrainercontent(request $request)
    {
        if (!empty($request->trainercontentids)) {
            $trainercontentids = json_decode($request->trainercontentids);
            foreach ($trainercontentids as $index => $trainerStreamId) {
                Trainercontent::find($trainerStreamId)->update(['display_order_id' => ($index+1)]);
            }
        }
    }

    public function deleteTrainerContentVideo(Request $request)
    {
        $contentId = $request->contentId;
        $content = Trainercontent::find($contentId);
        if($content->video) {
            $destinationPath = public_path('/video/content/trainer/');
            FacadesFile::delete($destinationPath . $content->video);
            Trainercontent::where("id",$contentId)->update(['video' => null, 'video_name' => null ]);
            return true;
        }
        return false;
    }
    
    public function deleteTrainerSessionImage(Request $request)
    {
        $contentId = $request->contentId;
        $content = TrainerContentImages::find($contentId);
        if($content->attachment) {
            $destinationPath = public_path('/files/content/trainer/');
            FacadesFile::delete($destinationPath . $content->attachment);
            TrainerContentImages::where("id",$contentId)->delete();
            return true;
        }
        return false;
    }

    public function deleteTrainerSessionPdf(Request $request)
    {
        $contentId = $request->contentId;
        $content = Trainercontent::find($contentId);
        if($content->pdf) {
            $destinationPath = public_path('/files/content/trainer/');
            FacadesFile::delete($destinationPath . $content->pdf);
            Trainercontent::where("id",$contentId)->update(['pdf' => null, 'pdf_name' => null]);
            return true;
        }
        return false;
    }

    public function deleteTrainerWorksheet(Request $request)
    {
        $contentId = $request->contentId;
        $content = Trainercontent::find($contentId);
        if($content->worksheet) {
            $destinationPath = public_path('/files/content/trainer/');
            FacadesFile::delete($destinationPath . $content->worksheet);
            Trainercontent::where("id",$contentId)->update(['worksheet' => null, 'worksheet_name' => null]);
            return true;
        }
        return false;
    }

    public function deleteTrainerSessionPresentation(Request $request)
    {
        $contentId = $request->contentId;
        $content = Trainercontent::find($contentId);
        if($content->session_presentation) {
            $destinationPath = public_path('/files/content/trainer/');
            FacadesFile::delete($destinationPath . $content->session_presentation);
            Trainercontent::where("id",$contentId)->update(['session_presentation' => null, 'session_presentation_name' => null]);
            return true;
        }
        return false;
    }

    public function uploadFile(Request $request) {
        return ChunkUploadHelper::uploadFile($request);
    }

}
