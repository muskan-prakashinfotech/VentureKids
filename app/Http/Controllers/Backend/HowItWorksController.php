<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HowItWorks;
use App\Helpers\ChunkUploadHelper;

class HowItWorksController extends Controller
{
    public function createVideo()
    {
        $howItWorks = HowItWorks::first();

        return view('backend.howItWorks.create', compact('howItWorks'));
    }

    public function storeVideo(Request $request)
    {
        $request->validate([
            'video_url' => 'required',
        ]);

        $videoUrl = $request->video_url;

        $howItWorks = HowItWorks::first();

        if ($howItWorks) {
            $howItWorks->delete();
        }

        $newVideo = new HowItWorks();
        $newVideo->video_path = $videoUrl;
        $newVideo->save();

        return redirect()->back()->with('message', 'Video link saved successfully!');
    }

}
