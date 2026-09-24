<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;
use App\Models\Stream;
use Illuminate\Support\Facades\File as FacadesFile;
use App\Helpers\ChunkUploadHelper;

class StreamController extends Controller
{
    public function index()
    {
        $streamData = Stream::with('agegroup')->get();
        return view('backend.stream.index', compact('streamData'));
    }

    public function create()
    {
        $levels = Grade::all();
              
        return view('backend.stream.create', compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'level' => 'required',
            'title' => 'required',
            'description' => 'required',
            'image' => 'required',
        ]);

        $scormFileName = $request->uploadedFileName;
        $no_of_questions = $request->no_of_questions;

        if(!empty($scormFileName) && !isset($no_of_questions)) {
            return back()->withErrors(["no_of_questions" => "The no. of scorm questions field is required."])->withInput();
        }
        
        $stream = new Stream();
        $stream->agegroup_id = $request->level;
        $stream->title = $request->title;
        $stream->description = $request->description;
        $stream->videoUrl = $request->videoUrl;
        $stream->creator = 'Super Admin';
        
        $image = $request->file('image');
        if(!empty($image)) {
            $extension = strtolower($image->getClientOriginalExtension());
            if(!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                return back()->withErrors(["image" => "Only JPG, JPEG or PNG files are allowed."])->withInput();
            }
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = public_path('/image/stream');
            $image->move($path, $imageName);
            $stream->image = $imageName;
        }

        /*
        $scormFile = $request->file('scormFile');
        if(!empty($scormFile)) {
            $extension = strtolower($scormFile->getClientOriginalExtension());
            if(!in_array($extension, ['zip', 'xml'])) {
                return back()->withErrors(["scormFile" => "Only ZIP or XML files are allowed."])->withInput();
            }
            $scormFileName = uniqid() . '.' . $scormFile->getClientOriginalExtension();
            $path = public_path('/scorm-files/stream/');
            $scormFile->move($path, $scormFileName);
            $stream->scormFile = $scormFileName;
        }
        */
        
        if(!empty($scormFileName)) {
            $stream->scormFile = $scormFileName;
            $stream->no_of_questions = $no_of_questions;
        }

        $stream->display_order_id = $stream->getNextDisplayOrderId($request->level);
        
        $stream->save();

        return redirect()->route('backend.stream.index')->with('success', 'Stream added successfully.');
    }

    public function edit($streamId)
    {
        $streamData = Stream::find($streamId);
        $levels = Grade::all();

        return view('backend.stream.edit', compact('streamData', 'levels'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'level' => 'required',
            'title' => 'required',
            'description' => 'required',
        ]);

        $scormFileName = $request->uploadedFileName;
        $no_of_questions = $request->no_of_questions;
    
        if(!empty($scormFileName) && !isset($no_of_questions)) {
            return back()->withErrors(["no_of_questions" => "The no. of scorm questions field is required."])->withInput();
        }
        
        $streamId = $request->streamId;
        $stream = Stream::find($streamId);

        if($stream) {
            
            $stream->agegroup_id = $request->level;
            $stream->title = $request->title;
            $stream->description = $request->description;
            $stream->videoUrl = $request->videoUrl;
            
            $image = $request->file('image');
            if(!empty($image)) {
                $extension = strtolower($image->getClientOriginalExtension());
                if(!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                    return back()->withErrors(["image" => "Only JPG, JPEG or PNG files are allowed."])->withInput();
                }
                if($stream->image) {
                    $destinationPath = public_path('/image/stream/');
                    FacadesFile::delete($destinationPath . $stream->image);
                }
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = public_path('/image/stream/');
                $image->move($path, $imageName);
                $stream->image = $imageName;
            } else if(empty($stream->image)) {
                return back()->withErrors(["image" => "This field is required."])->withInput();
            }

            /*
            $scormFile = $request->file('scormFile');
            if(!empty($scormFile)) {
                $extension = strtolower($scormFile->getClientOriginalExtension());
                if(!in_array($extension, ['zip', 'xml'])) {
                    return back()->withErrors(["scormFile" => "Only ZIP or XML files are allowed."])->withInput();
                }
                if($stream->scormFile) {
                    $destinationPath = public_path('/scorm-files/stream/');
                    FacadesFile::delete($destinationPath . $stream->scormFile);
                    $scormFileNameWithoutExtension = pathinfo($stream->scormFile, PATHINFO_FILENAME);
                    FacadesFile::deleteDirectory($destinationPath . $scormFileNameWithoutExtension);
                }
                $scormFileName = uniqid() . '.' . $scormFile->getClientOriginalExtension();
                $path = public_path('/scorm-files/stream/');
                $scormFile->move($path, $scormFileName);
                $stream->scormFile = $scormFileName;
            }
            */
            if(!empty($scormFileName)) {
                if($stream->scormFile) {
                    $destinationPath = public_path('/scorm-files/stream/');
                    FacadesFile::delete($destinationPath . $stream->scormFile);
                    $scormFileNameWithoutExtension = pathinfo($stream->scormFile, PATHINFO_FILENAME);
                    FacadesFile::deleteDirectory($destinationPath . $scormFileNameWithoutExtension);
                }
                $stream->scormFile = $scormFileName;
            }

            if(!empty($scormFileName) || !empty($stream->scormFile)) {
                $stream->no_of_questions = $no_of_questions;
            }

            $stream->save();
        }

        return redirect()->route('backend.stream.index')->with('success', 'Stream updated successfully.');
    }

    public function destory($streamId)
    {
        $stream = Stream::find($streamId);

        if($stream) {
            if($stream->image) {
                $destinationPath = public_path('/image/stream/');
                FacadesFile::delete($destinationPath . $stream->image);
            }
            if($stream->scormFile) {
                $destinationPath = public_path('/scorm-files/stream/');
                FacadesFile::delete($destinationPath . $stream->scormFile);
                $scormFileNameWithoutExtension = pathinfo($stream->scormFile, PATHINFO_FILENAME);
                FacadesFile::deleteDirectory($destinationPath . $scormFileNameWithoutExtension);
            }
            $stream->delete(); 
        }

        return redirect()->route('backend.stream.index')->with('success', 'Stream deleted successfully.');
    }

    public function deleteScormFile(Request $request) {
        $streamId = (int)$request->streamId;
        if($streamId) {
            $streamData = Stream::find($streamId);
            if(!empty($streamData) && $streamData->scormFile) {
                $destinationPath = public_path('/scorm-files/stream/');
                FacadesFile::delete($destinationPath . $streamData->scormFile);
                $scormFileNameWithoutExtension = pathinfo($streamData->scormFile, PATHINFO_FILENAME);
                FacadesFile::deleteDirectory($destinationPath . $scormFileNameWithoutExtension);
            }
            $streamData->scormFile = null;
            $streamData->no_of_questions = null;
            $streamData->save();
        }
        return true;
    }

    public function uploadSCORMFile(Request $request) {
        return ChunkUploadHelper::uploadFile($request);
    }

}