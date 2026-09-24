<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AssingmentFiles;
use App\Models\Grade;
use App\Models\RequestedStudentCertificate;
use App\Models\School;
use App\Models\StudentCommunications;
use App\Models\AssignmentComment;
use App\Models\AssignmentDetails;
use App\Models\Trainer;
use App\Models\TrainerAllocation;
use App\Models\Stream;
use App\Models\Studentscontent;
use App\Models\TrainerAllocationNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Helpers\ChunkUploadHelper;

class StudentCommunication extends Controller
{
    public function createAssignment()
    {
        $schools = School::select(['id', 'school_name'])->whereHas('user', function($query) {
            $query->where('suspend',2);
        })->get();
        
        $grades = Grade::all();
        $filteredLevels = [];
        $filtered_collection = $grades->filter(function ($item) use (&$filteredLevels) {
            if($item->is_primary == 1) {
                $filteredLevels['primary'][$item->id] = $item->toArray();        
            } else {
                $filteredLevels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();

        return view('backend.student_communication.create_assignment', [
            'schools' => $schools,
            'filteredLevels' => $filteredLevels,
        ]);
    }

    public function saveAssignment(Request $request)
    {
        $validated = $request->validate([
            'assignment_title' => 'required',
            'school_id' => 'required',
            'grade_id' => 'required',
            'upload_icon' => 'required|image|mimes:jpeg,jpg,png|max:1024',
            'category' => 'required|in:facilitated,self_learning',
            'scorm_file' => 'nullable|file|mimes:zip,rar',
            'is_active' => 'required',
        ]);

        $scormFileName = $request->uploadedFileName;

        if ($request->category === 'facilitated' && empty($request->multi_assignment)) {
            return redirect()->back()->withInput()->with('error', 'Attachment is required for Facilitated category.');
        }

        if ($request->category === 'self_learning' && empty($scormFileName)) {
            return redirect()->back()->withInput()->with('error', 'SCORM file is required for Self Learning category.');
        }

        $assignment = new StudentCommunications();
        $assignment->title = $request->assignment_title;
        $assignment->school_id = $request->school_id;
        $assignment->grade_id = $request->grade_id;
        $assignment->trainer_id = $request->trainer_id;
        $assignment->stream_id = $request->stream_id;
        $assignment->session_id = $request->session_id;
        $assignment->comment = $request->comment;
        $assignment->category = $request->category;
        $assignment->is_active = $request->is_active;

        $upload_icon = $request->file('upload_icon');
        if(!empty($upload_icon)) {
            $iconName = uniqid() . '.' . $upload_icon->getClientOriginalExtension();
            $path = public_path('/image/assignment');
            $upload_icon->move($path, $iconName);
            $assignment->icon_name = $iconName;
        }
        
        $display_order_id = (int) $request->display_order;
        if(empty($display_order_id)) {
            $assignment->display_order_id = $assignment->getNextDisplayOrderId();
        } else {
            $assignment->display_order_id = $display_order_id;
        }

          // Save SCORM file only if category is self_learning
        if ($request->category === 'self_learning' && !empty($scormFileName)) {
            $assignment->scorm_file = $scormFileName;
        }

        $assignment->save();

        $multiAssignment = $request->multi_assignment;
        if ($request->category === 'facilitated' && $multiAssignment) {
            for ($i = 0; $i < count($multiAssignment); $i++) {
                $assignmentFile = new AssingmentFiles();
                $assignmentFile->assignment_id = $assignment->id;
                $assignmentFile->attachment = $multiAssignment[$i];
                $assignmentFile->save();
            }
        }     
        
        return redirect('/admin/assignmentlist')->with('message', 'Assignment Created Successfully!');
    }

    public function multiAssignment(Request $request)
    {
        //multiple image upload
        $files = $request->file('file');
        // echo "<pre>"; print_r($files); die();

        if ($request->hasFile('file')) {
            // $fileName = [];
            foreach ($files as $file) {
                $fileType = $file->getClientOriginalExtension();
                $fileName = 'assignment_' . Str::random(10) . '.' . $fileType;
                // echo "<pre>"; print_r($fileName);
                $file->move('image/assignment/', $fileName);
                $imageUrl = 'image/assignment/' . $fileName;
                echo $imageUrl;
            }
        }
    }

    public function allowCertificate(Request $request)
    {
        $reqcertificates = RequestedStudentCertificate::orderBy('file', 'asc')->latest()->with('student')->get();

        return view('backend.student.certificatelist', compact('reqcertificates'));
    }

    public function uploadCertificate(Request $request)
    {
        $request->validate([
            'file' => 'required',
            'student_id' => 'required|exists:students,id',
        ]);

        $request_file = $request->file('file');

        $file_name = Str::random(10); //unique nmae generate every time
        $ext = strtolower($request_file->getClientOriginalExtension());
        $file_full_name = 'certificates_' . $file_name . '.' . $ext;

        $upload_path = 'certificates/';

        $request_file->move($upload_path, $file_full_name);

        RequestedStudentCertificate::where('student_id', $request->student_id)->update(['file' => $file_full_name]);

        return redirect()->back()->with('success', 'Certificate uploaded successfully');
    }

    // public function sendAssignmentMail(){
    //     // echo "hello"; die();

    //     $assignment = StudentCommunications::find(1)->toArray();
    //     // echo "<pre>";
    //     // print_r($assignment);
    //     // die();

    //     // $email = "afiqur@sahajjo.com";
    //     $email = "nazmul@sahajjo.com";
    //     echo $emailSub = "You have new assignment notification!! <br>";
    //     echo $emailBody = 'Your school id is: '. $assignment['school_id'] . '<br>
    //     Your grade id is: '. $assignment['grade_id'] .'<br>
    //     Your attachment is: '. $assignment['attachment'] . '<br>
    //     And your comment is: '.$assignment['comment'].'<br> <br>
    //     Thanks <br>
    //     Md: Afiqur Rahman';

    //     // die();
    //     file_put_contents('../resources/views/mail.blade.php',$emailBody);
    //     $data = array('email'=> $email,'subject'=> $emailSub);

    //     Mail::send('mail', $data, function($message) use ($data){
    //         $message->to($data['email'], 'Tutorials Point')->subject
    //            ($data['subject']);
    //      });
    // }

    public function assignmentlist() {
        $assignments = StudentCommunications::orderBy('id', 'DESC')->with(['school', 'level', 'trainer'])->get();
        return view('backend.student_communication.assignment_list')->with('assignments', $assignments);
    }

    public function editAssignment($assignmentId) {
        if(!empty($assignmentId)) {
            $assignments = StudentCommunications::find($assignmentId);
            if(!empty($assignments)) {
                $assingmentFiles = AssingmentFiles::where('assignment_id', $assignmentId)->get();
                $schools = School::select(['id', 'school_name'])->whereHas('user', function($query) {
                    $query->where('suspend',2);
                })->get();
                
                $grades = Grade::all();
                $filteredLevels = [];
                $filtered_collection = $grades->filter(function ($item) use (&$filteredLevels) {
                    if($item->is_primary == 1) {
                        $filteredLevels['primary'][$item->id] = $item->toArray();        
                    } else {
                        $filteredLevels['add-ons'][$item->id] = $item->toArray();;
                    }
                })->values();
                $school_id = $assignments->school_id;
                $trainers = [];
                if(!empty($school_id)) {
                    $trainers = TrainerAllocationNew::select(['id', 'school_id', 'trainer_id'])->with(['getTrainer' => function($query) {
                        $query->select('id','user_id','trainer_name');
                    }])->where('school_id', $school_id)->groupBy('trainer_id')->get()->toArray();
                }
                
                $streamList = Stream::where('agegroup_id', $assignments->grade_id)->get()->toArray();
                
                $sessionList = Studentscontent::where(['ageGroup_id' => $assignments->grade_id, 'stream_id' => $assignments->stream_id, 'is_publish' => 1])->get()->toArray();
                
                return view('backend.student_communication.edit_assignment', [
                    'assignments' => $assignments,
                    'assingmentFiles' => $assingmentFiles,
                    'schools' => $schools,
                    'filteredLevels' => $filteredLevels,
                    'trainers' => $trainers,
                    'streamList' => $streamList,
                    'sessionList' => $sessionList
                ]);    
            } else {
                return redirect('/admin/assignmentlist');    
            }
        } else {
            return redirect('/admin/assignmentlist');
        }
    }

    public function updateAssignment(Request $request)
    {
        $validated = $request->validate([
            'assignment_title' => 'required',
            'school_id' => 'required',
            'grade_id' => 'required',
            'category' => 'required|in:facilitated,self_learning',
            'scorm_file' => 'nullable|file|mimes:zip,rar',
            'is_active' => 'required',
        ]);
        $assingmentData = StudentCommunications::find($request->assignmentId);

        $assignment['title'] = $request->assignment_title;
        $assignment['school_id'] = $request->school_id;
        $assignment['grade_id'] = $request->grade_id;
        $assignment['trainer_id'] = $request->trainer_id;
        $assignment['stream_id'] = $request->stream_id;
        $assignment['session_id'] = $request->session_id;
        $assignment['comment'] = $request->comment;
        $assignment['category'] = $request->category;
        $assignment['is_active'] = $request->is_active;

        $upload_icon = $request->file('upload_icon');
        if(!empty($upload_icon)) {
            
            if(!empty($assingmentData->icon_name)) {
                $destinationPath = public_path('/image/assignment/');
                FacadesFile::delete($destinationPath . $assingmentData->icon_name);
            }
            $iconName = uniqid() . '.' . $upload_icon->getClientOriginalExtension();
            $path = public_path('/image/assignment');
            $upload_icon->move($path, $iconName);
            $assignment['icon_name'] = $iconName;
        }
        
        $display_order_id = (int) $request->display_order;
        if(!empty($display_order_id)) {  
            $assignment['display_order_id'] = $display_order_id;
        } 

        if ($request->category === 'self_learning') {
            $scormFileName = $request->uploadedFileName;

            if (empty($scormFileName) && empty($assingmentData->scorm_file)) {
                return redirect()->back()->withInput()->with('error', 'SCORM file is required for Self Learning category.');
            }

            if (!empty($scormFileName)) {
                // delete old scorm file if exists
                if (!empty($assingmentData ->scorm_file)) {
                    $destinationPath = public_path('/scorm-files/assignment/');
                    FacadesFile::delete($destinationPath .  $assingmentData->scorm_file);
                    $scormFileNameWithoutExtension = pathinfo($assingmentData->scorm_file, PATHINFO_FILENAME);
                    FacadesFile::deleteDirectory($destinationPath . $scormFileNameWithoutExtension);
                }
                $assignment['scorm_file'] = $scormFileName;
                AssingmentFiles::where('assignment_id', $request->assignmentId)->delete();
            } 
        } else {
            // If facilitated remove scorm_file if switching from self_learning
            if (!empty($assingmentData->scorm_file)) {
                $destinationPath = public_path('/scorm-files/assignment/');
                FacadesFile::delete($destinationPath . $assingmentData->scorm_file);
                $scormFolder = pathinfo($assingmentData->scorm_file, PATHINFO_FILENAME);
                FacadesFile::deleteDirectory($destinationPath . $scormFolder);
            }
            $assignment['scorm_file'] = null;
        }

        StudentCommunications::where('id', $request->assignmentId)->update($assignment);

        $multiAssignment = $request->multi_assignment;
        if ($request->category === 'facilitated' && $multiAssignment) {
            if(empty($request->multi_assignment)) {
                return redirect()->back()->withInput()->with('error', 'Attachment is required for Facilitated category.');
            }
            for ($i = 0; $i < count($multiAssignment); $i++) {
                $assignmentFile = new AssingmentFiles();
                $assignmentFile->assignment_id = $request->assignmentId;
                $assignmentFile->attachment = $multiAssignment[$i];
                $assignmentFile->save();
            }
        }

        return redirect('/admin/assignmentlist')->with('message', 'Assignment Updated Successfully!');
    }

    public function deleteAssignment($assignmentId)
    {
        if(!empty($assignmentId)) {
            $assingmentFiles = AssingmentFiles::where('assignment_id', $assignmentId)->get();
            if($assingmentFiles->count()) {
                foreach($assingmentFiles as $attachment) {
                    FacadesFile::delete($attachment->attachment);
                }
                AssingmentFiles::where('assignment_id', $assignmentId)->delete();
            }
            AssignmentComment::where('assignment_id', $assignmentId)->delete();
            AssignmentDetails::where('assignment_id', $assignmentId)->delete();
            StudentCommunications::find($assignmentId)->delete();
        }

        return redirect('/admin/assignmentlist')->with('message', 'Assignment Deleted Successfully!');
    }

    public function deleteAssignmentFile(Request $request) { 
        $attachmentId = $request->attachmentId;
        if(!empty($attachmentId)) {
            $assingmentFile = AssingmentFiles::find($attachmentId);
            if($assingmentFile->count()) {
                FacadesFile::delete($assingmentFile->attachment);
                AssingmentFiles::find($attachmentId)->delete();
                return true;
            }
        }
        return false;
    }

    public function getSchoolTrainers(Request $request) {
        $school_id = (int) $request->school_id;
        if($school_id) {
            $trainer_list = TrainerAllocationNew::select(['id', 'school_id', 'trainer_id'])->with(['getTrainer' => function($query) {
                $query->select('id','user_id','trainer_name');
            }])->where('school_id', $school_id)->groupBy('trainer_id')->get();
            
            return json_encode(['status' => 'success', 'trainers' => $trainer_list]);
        }
        return json_encode(['status' => 'fail']);
    }

    public function deleteAssignmentIcon(Request $request) { 
        $assignmentId = $request->assignmentId;
        if(!empty($assignmentId)) {
            $assingmentData = StudentCommunications::find($assignmentId);
            if($assingmentData->count() && !empty($assingmentData->icon_name)) {
                $destinationPath = public_path('/image/assignment/');
                FacadesFile::delete($destinationPath . $assingmentData->icon_name);
                $assingmentData->icon_name = null;
                $assingmentData->save();
                return true;
            }
        }
        return false;
    }

    public function deleteAssignmentScormFile(Request $request) {
        $assignmentId =$request->assignmentId;
        if($assignmentId) {
           $assignmentData = StudentCommunications::find($assignmentId);
            if(!empty($assignmentData) && $assignmentData->scorm_file) {
                $destinationPath = public_path('/scorm-files/assignment/');
                FacadesFile::delete($destinationPath . $assignmentData->scorm_file);
                $scormFileNameWithoutExtension = pathinfo($assignmentData->scorm_file, PATHINFO_FILENAME);
                FacadesFile::deleteDirectory($destinationPath . $scormFileNameWithoutExtension);
            }
            $assignmentData->scorm_file = null;
            $assignmentData->save();
        }
        return true;
    }

    public function uploadAssignmentSCORMFile(Request $request) {
        return ChunkUploadHelper::uploadFile($request);
    }


}
