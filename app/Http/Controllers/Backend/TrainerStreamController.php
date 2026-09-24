<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Trainerlavel;
use App\Models\TrainerStreamImages;
use Illuminate\Http\Request;
use App\Models\Trainerstream;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Log;

class TrainerStreamController extends Controller
{
    public function index()
    {
        $streamData = Trainerstream::with('agegroup')->get();
        return view('backend.stream.trainer.index', compact('streamData'));
    }

    public function create()
    {
        $levels = Trainerlavel::all();
              
        return view('backend.stream.trainer.create', compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'level' => 'required',
            'title' => 'required',
            'pdf' => 'nullable|file|mimes:pdf|max:30720',
            'worksheet' => 'nullable|file|mimes:xls,xlsx,doc,docx,ppt,pptx,pdf|max:30720',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        
        $stream = new Trainerstream();
        $stream->title = $request->title;
        $stream->agegroup_id = $request->level;
        
        $video_url = null;
        $video_name = null;
        if ($request->hasFile('video')) {
            $file2 = $request->file('video');
            $video_name = pathinfo($file2->getClientOriginalName(), PATHINFO_FILENAME);
            $video = uniqid() . '.' . $file2->getClientOriginalExtension();
            $path = public_path('/video/stream/trainer/');
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
        if (isset($request->pdf)) {
            $file = $request->file('pdf');
            $pdf_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('/files/stream/trainer/');
            $file->move($path, $filename);
            $pdf_url = $filename;
        }

        $worksheet_url = null;
        $worksheet_name = null;
        if ($request->hasFile('worksheet')) {
            $file = $request->file('worksheet');
            $worksheet_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('/files/stream/trainer/');
            $file->move($path, $filename);
            $worksheet_url = $filename;
        }

        $stream->video = isset($video_url) ? $video_url : null;
        $stream->video_name = $video_name;
        $stream->video_url = isset($request->video_url) ? $request->video_url : null;
        $stream->drive_url = isset($request->drive_url) ? $request->drive_url : null;
        $stream->pdf = $pdf_url;
        $stream->pdf_name = $pdf_name;
        $stream->worksheet = $worksheet_url;
        $stream->worksheet_name = $worksheet_name;
        $stream->learning_object = $request->learning_object;
        $stream->outcome_session = $request->outcome_session;
        $stream->question_prior_knowledge = $request->question_prior_knowledge;
        $stream->introduce_topic = $request->introduce_topic;
        $stream->related_activity_one = $request->related_activity_one;
        $stream->related_activity_two = $request->related_activity_two;
        $stream->vocabulary = $request->vocabulary;
        $stream->home_assignments = $request->home_assignments;
        $stream->creator_id = 1;
        $stream->display_order_id = $stream->getNextDisplayOrderId($request->level);
        $stream->creator = 'Super Admin';

        $stream->save();

        if (isset($request->images)) {
            foreach ($request->file('images') as $imagefile) {
                $filename = uniqid() . '.' . $imagefile->getClientOriginalExtension();
                $path = public_path('/image/stream/trainer/');
                $imagefile->move($path, $filename);
                $trainerStreamImage = new TrainerStreamImages;
                $trainerStreamImage->stream_id = $stream->id;
                $trainerStreamImage->attachment = $filename;
                $trainerStreamImage->save();
            }
        }

        return redirect()->route('backend.trainerstream.index')->with('success', 'Trainer Stream Added Successfully!');
    }

    public function edit($streamId)
    {
        $streamData = Trainerstream::with('trainerStreamImages')->find($streamId);
        $levels = Trainerlavel::all();

        return view('backend.stream.trainer.edit', compact('streamData', 'levels'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'level' => 'required',
            'title' => 'required',
            'pdf' => 'nullable|file|mimes:pdf|max:30720',
            'worksheet' => 'nullable|file|mimes:xls,xlsx,doc,docx,ppt,pptx,pdf|max:30720',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        
        $streamId = $request->streamId;
        $stream = Trainerstream::find($streamId);

        if($stream) {
            $stream->title = $request->title;
            $stream->agegroup_id = $request->level;
            
            if ($request->hasFile('video')) {
                if ($stream->video) {
                    $destinationPath = public_path('/video/stream/trainer/');
                    FacadesFile::delete($destinationPath . $stream->video);
                }
                $file2 = $request->file('video');
                $video_name = pathinfo($file2->getClientOriginalName(), PATHINFO_FILENAME);
                $video = uniqid() . '.' . $file2->getClientOriginalExtension();
                $path = public_path('/video/stream/trainer/');
                $file2->move($path, $video);
            } else {
                if (!empty($stream->video) && $stream->video != 'no video') {
                    $video = $stream->video;
                    $video_name = $stream->video_name;
                } else {
                    $video = null;
                    $video_name = null;
                }
            }
    
            if ($request->pdf) {
                if ($stream->pdf) {
                    $destinationPath = public_path('/files/stream/trainer/');
                    FacadesFile::delete($destinationPath . $stream->pdf);
                }
    
                $file2 = $request->file('pdf');
                $pdf_name = pathinfo($file2->getClientOriginalName(), PATHINFO_FILENAME);
                $pdf = uniqid() . '.' . $file2->getClientOriginalExtension();
                $path = public_path('/files/stream/trainer/');
                $file2->move($path, $pdf);
                $pdf_url = $pdf;
            } else {
                if (!empty($stream->pdf)) {
                    $pdf_url = $stream->pdf;
                    $pdf_name = $stream->pdf_name;
                } else {
                    $pdf_url = null;
                    $pdf_name = null;
                }
            }
    
            if ($request->hasFile('worksheet')) {
                if ($stream->worksheet) {
                    $destinationPath = public_path('/files/stream/trainer/');
                    FacadesFile::delete($destinationPath . $stream->worksheet);
                }
                $file = $request->file('worksheet');
                $worksheet_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $path = public_path('/files/stream/trainer/');
                $file->move($path, $filename);
                $worksheet_url = $filename;
            } else {
                if (!empty($stream->worksheet) && $stream->worksheet != 'no worksheet') {
                    $worksheet_url = $stream->worksheet;
                    $worksheet_name = $stream->worksheet_name;
                } else {
                    $worksheet_url = null;
                    $worksheet_name = null;
                }
            }
            
            $stream->video = $video;
            $stream->video_name = $video_name;
            $stream->video_url = isset($request->video_url) ? $request->video_url : null;
            $stream->drive_url = isset($request->drive_url) ? $request->drive_url : null;
            $stream->pdf = $pdf_url;
            $stream->pdf_name = $pdf_name;
            $stream->worksheet = $worksheet_url;
            $stream->worksheet_name = $worksheet_name;
            $stream->learning_object = $request->learning_object;
            $stream->outcome_session = $request->outcome_session;
            $stream->question_prior_knowledge = $request->question_prior_knowledge;
            $stream->introduce_topic = $request->introduce_topic;
            $stream->related_activity_one = $request->related_activity_one;
            $stream->related_activity_two = $request->related_activity_two;
            $stream->vocabulary = $request->vocabulary;
            $stream->home_assignments = $request->home_assignments;
            
            $stream->save();

            if (isset($request->images)) {
                foreach ($request->file('images') as $imagefile) {
                    $filename = uniqid() . '.' . $imagefile->getClientOriginalExtension();
                    $path = public_path('/image/stream/trainer/');
                    $imagefile->move($path, $filename);
                    $trainerStreamImage = new TrainerStreamImages;
                    $trainerStreamImage->stream_id = $stream->id;
                    $trainerStreamImage->attachment = $filename;
                    $trainerStreamImage->save();
                }
            }
        }

        return redirect()->route('backend.trainerstream.index')->with('success', 'Trainer Stream Updated Successfully!');
    }

    public function destory($streamId)
    {
        $stream = Trainerstream::find($streamId);

        if($stream) {
            if ($stream->video) {
                $destinationPath = public_path('/video/stream/trainer/');
                FacadesFile::delete($destinationPath . $stream->video);
            }
            if ($stream->pdf) {
                $destinationPath = public_path('/files/stream/trainer/');
                FacadesFile::delete($destinationPath .$stream->pdf);
            }
            if ($stream->worksheet) {
                $destinationPath = public_path('/files/stream/trainer/');
                FacadesFile::delete($destinationPath . $stream->worksheet);
            }
            $trainerStreamImages = TrainerStreamImages::where('stream_id', $stream->id)->get();
            if($trainerStreamImages->count()) {
                foreach($trainerStreamImages as $attachment) {
                    $destinationPath = public_path('/image/stream/trainer/');
                    FacadesFile::delete($destinationPath . $attachment->attachment);
                }
                TrainerStreamImages::where('stream_id', $stream->id)->delete();
            }

            $stream->delete(); 
        }

        return redirect()->route('backend.trainerstream.index')->with('success', 'Trainer Stream Deleted Successfully!');
    }

    public function deleteStreamVideo(Request $request)
    {
        $streamId = $request->streamId;
        $stream = Trainerstream::find($streamId);
        if($stream) {
            if($stream->video) {
                $destinationPath = public_path('/video/stream/trainer/');
                FacadesFile::delete($destinationPath . $stream->video);
            }
            Trainerstream::where("id",$streamId)->update(['video' => null, 'video_name' => null ]);
        }
        return true;
    }
    
    public function deleteStreamImage(Request $request)
    {
        $attachmentId = $request->attachmentId;
        $trainerStreamImage = TrainerStreamImages::find($attachmentId);
        if($trainerStreamImage) {
            if($trainerStreamImage->attachment) {
                $destinationPath = public_path('/image/stream/trainer/');
                FacadesFile::delete($destinationPath . $trainerStreamImage->attachment);
            }
            TrainerStreamImages::find($attachmentId)->delete();
        }
        return true;
    }

    public function deleteStreamPdf(Request $request)
    {
        $streamId = $request->streamId;
        $stream = Trainerstream::find($streamId);
        if($stream) {
            if ($stream->pdf) {
                $destinationPath = public_path('/files/stream/trainer/');
                FacadesFile::delete($destinationPath .$stream->pdf);
            }
            Trainerstream::where("id", $streamId)->update(['pdf' => null, 'pdf_name' => null]);
        }
        return true;
    }

    public function deleteStreamWorksheet(Request $request)
    {
        $streamId = $request->streamId;
        $stream = Trainerstream::find($streamId);
        if($stream) {
            if ($stream->worksheet) {
                $destinationPath = public_path('/files/stream/trainer/');
                FacadesFile::delete($destinationPath . $stream->worksheet);
            }
            Trainerstream::where("id", $streamId)->update(['worksheet' => null, 'worksheet_name' => null]);
        }
        return true;
    }
}