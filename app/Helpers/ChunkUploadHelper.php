<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Illuminate\Http\UploadedFile;

class ChunkUploadHelper
{
    public static function uploadFile($request) 
    {    
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
    
        if (!$receiver->isUploaded()) {
            // file not uploaded
        }
    
        $fileReceived = $receiver->receive(); // receive file

        if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $path = '';
            switch ($request->moduleName) {
                case 'scorm':
                    $path = '/scorm-files/stream/';
                    break;
                case 'trainer-content':
                    $path = '/files/content/trainer/';
                    break;
                case 'scormAssignment':
                    $path ='/scorm-files/assignment/';
                    break;
                default:
                    break;
            }
            return self::saveFile($fileReceived->getFile(), $path);
        }
        
        // otherwise return percentage information
        $handler = $fileReceived->handler();
        return [
            'done' => $handler->getPercentageDone(),
            'status' => true
        ];
    }
    
    protected static function saveFile(UploadedFile $file, $path)
    {
        $fileName = self::createFilename($file);
        
        // Build the file path
        $finalPath = public_path($path);

        // move the file
        $file->move($finalPath, $fileName);

        return response()->json([
            'path' => $finalPath,
            'name' => $fileName,
            'display_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        ]);
    }

    protected static function createFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        
        // $filename = str_replace(".".$extension, "", $file->getClientOriginalName()); // Filename without extension
        // Add timestamp hash to name of the file
        $filename = md5(time()) . "." . $extension;

        return $filename;
    }    
}