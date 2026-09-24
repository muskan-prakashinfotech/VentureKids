<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PlayAffirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlayAffirmationController extends Controller
{
    public function index()
    {
        $affirmation = PlayAffirmation::latest()->first();

        return view('backend.affirmations.index', compact('affirmation'));
    }

    /**Store the Audio File and Delete the existing one */
    public function store(Request $request)
    {
        $request->validate([
            'file_path' => 'required|mimes:mp3,wav|max:10240' // 10MB
        ]);

        // Delete existing
        $existing = PlayAffirmation::latest()->first();
        if ($existing && file_exists(public_path($existing->file_path))) {
            unlink(public_path($existing->file_path));
            $existing->delete();
        }

        $file = $request->file('file_path');
        $extension = $file->getClientOriginalExtension();
        $filename = md5(time()) . "." . $extension;
        $destination = public_path('affirmation');

        if (!file_exists($destination)) {
            mkdir($destination, 0775, true);
        }

        $file->move($destination, $filename);

        $relativePath = 'affirmation/' . $filename;

        PlayAffirmation::create([
            'file_path' => $relativePath
        ]);

        return redirect()->route('backend.student.playaffirmation')->with('success', 'Audio uploaded successfully!');
    }

   /*Delete Functionality to delete the audio file*/ 
    public function deleteAffirmationFile(Request $request)
    {
        $affirmationId = $request->id;
        $affirmation = PlayAffirmation::find($affirmationId);

        if ($affirmation && !empty($affirmation->file_path)) {
            $filePath = public_path($affirmation->file_path);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $affirmation->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }
}
