<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\School;
use App\Models\Students;
use App\Models\Trainer;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Country;
use App\Models\EventChallenge;
use App\Jobs\SendEmail;
use App\Models\EventResource;
use App\Models\EventPosterAttachment;
use Illuminate\Support\Facades\File as FacadesFile;

class EventController extends Controller
{
    public function createevent()
    {
        $partnerCountry = isPartnerUser() ? Country::find(partnerCountryId()) : null;
        $partnerCurrency = $this->partnerEventCurrency();

        if (isPartnerUser() && empty($partnerCurrency)) {
            return redirect()->route('backend.eventlist.eventlist')
                ->with(['success' => 'error', 'message' => 'No currency has been assigned to your account.']);
        }

        return view('backend.event.add_event')->with([
            'countries' => Country::get(['id', 'name'])->sortBy('name'),
            'currencies' => \DB::table('currencies')->orderBy('code')->get(['code']),
            'partnerCountry' => $partnerCountry,
            'partnerCurrency' => $partnerCurrency,
        ]);
    }

    public function eventlist()
    {
        $event = Event::query()
            ->when(isPartnerUser(), function ($query) {
                $query->where('visibility_type', 2)
                    ->where('country_id', partnerCountryId());
            })
            ->orderByDesc('id')
            ->get();

        return view('backend.event.event_list', compact('event'));
    }

    public function eventstore(Request $req)
    {
        $partnerCurrency = $this->partnerEventCurrency();

        if (isPartnerUser()) {
            $req->validate([
                'event_name' => 'required|string',
                'event_image' => 'required|file|mimes:jpeg,jpg,png',
                'event_date' => 'required|date',
                'event_last_date' => 'required|date',
                'event_description' => 'required|string',
                'event_fee' => 'required|numeric',
                'currency' => 'required|string',
                'poster_attachment' => 'required|array|min:1',
                'poster_attachment.*' => 'required|file|mimes:pdf|mimetypes:application/pdf',
                'eventPublish' => 'required|in:0,1',
            ]);
        } else {
            $req->validate([
                'visibility_type' =>'required|in:1,2',
                'event_name' => 'required|string',
                'event_image' => 'required|file|mimes:jpeg,jpg,png',
                'event_date' => 'required|date',
                'event_last_date' => 'required|date',
                'event_description' => 'required|string',
                'country' => $req->visibility_type == 2 ? 'required' : 'nullable',
                'event_fee' => 'required|numeric',
                'currency' => 'required|string',
                'poster_attachment' => 'required|array|min:1',
                'poster_attachment.*' => 'required|file|mimes:pdf|mimetypes:application/pdf',
                'eventPublish' => 'required|in:0,1',
            ]);
        }

        $visibilityType = isPartnerUser() ? 2 : (int) $req->visibility_type;
        $countryId = isPartnerUser() ? partnerCountryId() : ($visibilityType === 2 ? $req->country : null);
        if (isPartnerUser() && empty($countryId)) {
            abort(403, 'Access denied.');
        }
        if (isPartnerUser() && empty($partnerCurrency)) {
            abort(403, 'No currency has been assigned to your account.');
        }
        if (isPartnerUser()) {
            $req->merge(['currency' => $partnerCurrency]);
        }

        $event = new Event;
        $event->visibility_type = $visibilityType;
        $event->country_id = $countryId;
        $event->event_name = $req->event_name;
        $event->event_date = $req->event_date;
        $event->event_last_date = $req->event_last_date;
        $event->event_address = $req->event_address;
        $event->event_fee = $req->event_fee;
        $event->event_description = $req->event_description;
        $event->currency = $req->currency;
        $event->reward_amount = $req->reward_amount;
        $event->reward_description = $req->reward_description;
        $event->event_video = $req->event_video;
        $event->is_publish = (int) $req->eventPublish;

        $event_image = $req->event_image;
        if($event_image) {
            $extension = strtolower($event_image->getClientOriginalExtension());
            if(!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                return back()->withErrors(["event_image" => "Only JPG, JPEG or PNG files are allowed."])->withInput();
            }
            // $imageSize = getimagesize($event_image);
            // if($imageSize[0] < 1920 || $imageSize[1] < 500) {
            //     return back()->withErrors(["event_image" => "Image should be 1920*500 pixel."])->withInput();
            // }   
        }

        if ($event_image) {
            $image_name = Str::random(10); //unique nmae generate every time
            $ext = strtolower($event_image->getClientOriginalExtension());
            $image_full_name = 'eventimage_' . $image_name . '.' . $ext;

            $upload_path = 'image/event/';

            $success = $event_image->move($upload_path, $image_full_name);

            $event->event_image = $upload_path . $image_full_name;
        }

        
        $success = $event->save();
        
        $event_id = $event->id;
        
        $poster_attachment = $req->poster_attachment;
        if (!empty($poster_attachment)) {
            foreach ($poster_attachment as $poster) {
                $extension = strtolower($poster->getClientOriginalExtension());
                if($extension == 'pdf') {
                    $eventPosterAttachment = new EventPosterAttachment();
                    $eventPosterAttachment->title = str_replace(".".$poster->getClientOriginalExtension(), "", $poster->getClientOriginalName());
                    $eventPosterAttachment->event_id = $event_id;
                    $name = Str::random(10);
                    $image_full_name = 'poster_' . $name . '.' . $extension;
                    $upload_path = 'image/event/poster/';
                    $poster->move($upload_path, $image_full_name);
                    $eventPosterAttachment->attachment = $image_full_name;
                    $eventPosterAttachment->save();
                }
            }
        }
        
        $resource_title = $req->resource_title;
        $resource_description = $req->resource_description;
        $resource_icon = $req->resource_icon;
        if (!empty($resource_title)) {
            foreach($resource_title as $key => $title) {
                if(!empty($title) && !empty($resource_description[$key])) {
                    $eventResource = new EventResource();
                    $eventResource->event_id = $event_id;
                    $eventResource->title = $title;
                    $eventResource->description = $resource_description[$key];
                    if(array_key_exists($key, $resource_icon) && $resource_icon[$key]) {
                        $extension = strtolower($resource_icon[$key]->getClientOriginalExtension());
                        if(in_array($extension, ['jpeg', 'jpg', 'png'])) {
                            $name = Str::random(10);
                            $ext = $extension;
                            $icon_full_name = 'highlight_' . $name . '.' . $ext;
                            $upload_path = 'image/event/highlight/';
                            $success = $resource_icon[$key]->move($upload_path, $icon_full_name);
                            $eventResource->attachment = $icon_full_name;
                        }
                    }
                    $eventResource->save();
                }
            }
        }
        
        $email_subject = "New Challenge Created - ".$req->event_name;
        $email_body['challenge_name'] = $req->event_name;
        $email_body['challenge_date'] = $req->event_date;
        $email_body['challenge_last_date'] = $req->event_last_date;
        $email_body['challenge_image'] = $req->event_image;
        
        /*
        // Start Trainer Mail send
        $trainer = Trainer::where('country_id',$req->country)->get()->toArray();
        if ($trainer) {
            foreach ($trainer as $trainers) {
                $trainer_email = $trainers['official_email_id'];
                dispatch(new SendEmail([
                    'toEmail' => $trainer_email,
                    'subject' => $email_subject,
                    'emailBody' => $email_body
                ]));
            }            
        }
        // End Trainer Mail send 

        // Start School Mail send
        $school_data = School::where('country_id',$req->country)->get()->toArray();
        if ($school_data) {
            foreach ($school_data as $school_datas) {
                $school_email = $school_datas['official_email_id'];
                dispatch(new SendEmail([
                    'toEmail' => $school_email,
                    'subject' => $email_subject,
                    'emailBody' => $email_body
                ]));
            }
        }
        // End School Mail send 

        // Start Student Mail send
        $students = Students::with('user')->where('country_id',$req->country)->get()->toArray();
        if ($students) {
            foreach ($students as $students_data) {
                $students_email = $students_data['user']['email'];
                dispatch(new SendEmail([
                    'toEmail' => $students_email,
                    'subject' => $email_subject,
                    'emailBody' => $email_body
                ]));
            }
        }
        // End Student Mail send
        */

        if ($success) {
            if (isPartnerUser()) {
                $superAdminEmail = env('MAIL_ADMIN');
                if (!empty($superAdminEmail)) {
                safeDispatchAction('industry challenge created email', [
                    'recipient' => $superAdminEmail,
                    'event_id' => $event->id ?? null,
                    'event_name' => $event->event_name ?? null,
                ], function () use ($superAdminEmail, $event) {
                    dispatch(new SendEmail([
                        'toEmail' => $superAdminEmail,
                        'subject' => 'New Industry Challenge Created - ' . $event->event_name,
                        'emailBody' => [
                            'challenge_name' => $event->event_name,
                            'challenge_date' => $event->event_date,
                            'challenge_last_date' => $event->event_last_date,
                        ],
                    ]));
                });
                }
            }

            $notification = [
                'message'=>'Event successfully Inserted!',
                'success'=>'success',
            ];

            return redirect()->route('backend.eventlist.eventlist')->with($notification);
        }
    }

    public function viewevent($id)
    {
        $event = Event::find($id);
        $this->ensurePartnerCanAccessEvent($event);

        return view('backend.event.view_event', compact('event'));
    }

    public function editevent($id)
    {
        $event = Event::find($id);
        $this->ensurePartnerCanAccessEvent($event);
        $partnerCurrency = $this->partnerEventCurrency();

        if (isPartnerUser() && empty($partnerCurrency)) {
            return redirect()->route('backend.eventlist.eventlist')
                ->with(['success' => 'error', 'message' => 'No currency has been assigned to your account.']);
        }

        $resourceData = EventResource::where('event_id', $id)->get();
        if($resourceData->count()) {
            $resourceData = $resourceData->toArray();
        } else {
            $resourceData = [];
        }

        $posterData = EventPosterAttachment::where('event_id', $id)->get();
        if($posterData->count()) {
            $posterData = $posterData->toArray();
        } else {
            $posterData = [];
        }
        return view('backend.event.edit_event')->with([
            'event' => $event,
            'countries' => Country::get(['id', 'name'])->sortBy('name'),
            'currencies' => \DB::table('currencies')->orderBy('code')->get(['code']),
            'partnerCountry' => isPartnerUser() ? Country::find(partnerCountryId()) : null,
            'partnerCurrency' => $partnerCurrency,
            'resourceData' => $resourceData,
            'posterData' => $posterData
        ]);
    }

    public function eventupdate(Request $req)
    {
        $id = $req->event_id;
        $event = Event::find($id);
        $this->ensurePartnerCanAccessEvent($event);
        $partnerCurrency = $this->partnerEventCurrency();
        $hasExistingPoster = EventPosterAttachment::where('event_id', $id)->exists();
        $posterRules = $hasExistingPoster ? 'nullable|array' : 'required|array|min:1';

        if (isPartnerUser()) {
            $req->validate([
                'event_name' => 'required|string',
                'event_image' => 'nullable|file|mimes:jpeg,jpg,png',
                'event_date' => 'required|date',
                'event_last_date' => 'required|date',
                'event_description' => 'required|string',
                'event_fee' => 'required|numeric',
                'currency' => 'required|string',
                'poster_attachment' => $posterRules,
                'poster_attachment.*' => 'file|mimes:pdf|mimetypes:application/pdf',
                'eventPublish' => 'required|in:0,1',
            ]);
        } else {
            $req->validate([
                'visibility_type' => 'required|in:1,2',
                'event_name' => 'required|string',
                'event_image' => 'nullable|file|mimes:jpeg,jpg,png',
                'event_date' => 'required|date',
                'event_last_date' => 'required|date',
                'event_description' => 'required|string',
                'country' => $req->visibility_type == 2 ? 'required' : 'nullable',
                'event_fee' => 'required|numeric',
                'currency' => 'required|string',
                'poster_attachment' => $posterRules,
                'poster_attachment.*' => 'file|mimes:pdf|mimetypes:application/pdf',
                'eventPublish' => 'required|in:0,1',
            ]);
        }

        $visibilityType = isPartnerUser() ? 2 : (int) $req->visibility_type;
        $countryId = isPartnerUser() ? partnerCountryId() : ($visibilityType === 2 ? $req->country : null);
        if (isPartnerUser() && empty($partnerCurrency)) {
            abort(403, 'No currency has been assigned to your account.');
        }
        if (isPartnerUser()) {
            $req->merge(['currency' => $partnerCurrency]);
        }

        $event->visibility_type = $visibilityType;
        $event->country_id = $countryId;
        $event->event_name = $req->event_name;
        $event->event_date = $req->event_date;
        $event->event_last_date = $req->event_last_date;
        $event->event_address = $req->event_address;
        $event->event_fee = $req->event_fee;
        $event->event_description = $req->event_description;
        $event->currency = $req->currency;
        $event->reward_amount = $req->reward_amount;
        $event->reward_description = $req->reward_description;
        $event->event_video = $req->event_video;
        $event_image = $req->event_image;
        $event->is_publish = (int) $req->eventPublish;
        
        $old_event_image = $req->old_event_image;
        if(empty($old_event_image) && empty($event_image)) {
            return back()->withErrors(["event_image" => "The event image field is required."])->withInput();
        }

        if($event_image) {
            $extension = strtolower($event_image->getClientOriginalExtension());
            if(!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                return back()->withErrors(["event_image" => "Only JPG, JPEG or PNG files are allowed."])->withInput();
            }
            // $imageSize = getimagesize($event_image);
            // if($imageSize[0] < 1920 || $imageSize[1] < 500) {
            //     return back()->withErrors(["event_image" => "Image should be 1920*500 pixel."])->withInput();
            // }   
        }
        
        if ($event_image) {
            if ($old_event_image != '') {
                if (File::exists($old_event_image)) {
                    unlink($old_event_image);
                }
            }

            $image_name = Str::random(10); //unique nmae generate every time
            $ext = strtolower($event_image->getClientOriginalExtension());
            $image_full_name = 'eventimage_' . $image_name . '.' . $ext;

            $upload_path = 'image/event/';

            $success = $event_image->move($upload_path, $image_full_name);

            $event->event_image = $upload_path . $image_full_name;
        }

        $success = $event->save();

        $event_id = $id;

        $poster_attachment = $req->poster_attachment;
        $posterIdList = $req->posterIdList;
        if(!empty($poster_attachment)) {
            foreach($poster_attachment as $key => $poster) {
                $posterData = [];
                $flagUpdate = false;
                if(!empty($posterIdList) && array_key_exists($key, $posterIdList)) {
                    if(array_key_exists($key, $poster_attachment)) {
                        $posterAttachment = EventPosterAttachment::find($posterIdList[$key]);
                        if($posterAttachment->attachment) {
                            $destinationPath = public_path('/image/event/poster/');
                            FacadesFile::delete($destinationPath . $posterAttachment->attachment);
                        }
                    }
                    $flagUpdate = true;
                }
                if(array_key_exists($key, $poster_attachment)) {
                    $extension = strtolower($poster->getClientOriginalExtension());
                    if($extension == 'pdf') {
                        $posterData['event_id'] = $event_id;
                        $posterData['title'] = str_replace(".".$poster->getClientOriginalExtension(), "", $poster->getClientOriginalName());
                        $name = Str::random(10);
                        $image_full_name = 'poster_' . $name . '.' . $extension;
                        $upload_path = 'image/event/poster/';
                        $poster->move($upload_path, $image_full_name);
                        $posterData['attachment'] = $image_full_name;
                    }   
                }
                if($flagUpdate) {
                    EventPosterAttachment::where('id', $posterIdList[$key])->update($posterData);
                } else {
                    EventPosterAttachment::insert($posterData);
                }
            }
        }
        
        $resource_title = $req->resource_title;
        $resource_description = $req->resource_description;
        $resource_icon = $req->resource_icon;
        $resourceIdList = $req->resourceIdList;
        if(!empty($resource_title)) {
            foreach($resource_title as $key => $title) {
                if(!empty($title) && !empty($resource_description[$key])) {
                    $resourceData = [];
                    $resourceData['event_id'] = $event_id;
                    $resourceData['title'] = $title;
                    $resourceData['description'] = $resource_description[$key];
                    $flagUpdate = false;
                    if(!empty($resourceIdList) && array_key_exists($key, $resourceIdList)) {
                        if(!empty($resource_icon) && array_key_exists($key, $resource_icon)) {
                            $resource = EventResource::find($resourceIdList[$key]);
                            if($resource->attachment) {
                                $destinationPath = public_path('/image/event/highlight/');
                                FacadesFile::delete($destinationPath . $resource->attachment);
                            }
                        }
                        $flagUpdate = true;
                    }
                   
                    if(!empty($resource_icon) && array_key_exists($key, $resource_icon)) {
                        $extension = strtolower($resource_icon[$key]->getClientOriginalExtension());
                        if(in_array($extension, ['jpeg', 'jpg', 'png'])) {
                            $name = Str::random(10);
                            $ext = $extension;
                            $icon_full_name = 'highlight_' . $name . '.' . $ext;
                            $upload_path = 'image/event/highlight/';
                            $resource_icon[$key]->move($upload_path, $icon_full_name);
                            $resourceData['attachment'] = $icon_full_name;
                        }
                    }
                    if($flagUpdate) {
                        EventResource::where('id', $resourceIdList[$key])->update($resourceData);
                    } else {
                        EventResource::insert($resourceData);
                    }
                }
            }
        }

        $email_subject = "Challenge Edited - ".$req->event_name;
        $email_body['challenge_name'] = $req->event_name;
        $email_body['challenge_date'] = $req->event_date;
        $email_body['challenge_last_date'] = $req->event_last_date;
        $email_body['challenge_image'] = $req->event_image;
        
        /*
        //  Start Trainer Mail send
        $trainer = Trainer::where('country_id',$req->country)->get()->toArray();
        if ($trainer) {
            foreach ($trainer as $trainers) {
                $trainer_email = $trainers['official_email_id'];
                dispatch(new SendEmail([
                    'toEmail' => $trainer_email,
                    'subject' => $email_subject,
                    'emailBody' => $email_body
                ]));
            }            
        }
        // End Trainer Mail send

        // Start School Mail send
        $school_data = School::where('country_id',$req->country)->get()->toArray();
        if ($school_data) {
            foreach ($school_data as $school_datas) {
                $school_email = $school_datas['official_email_id'];
                dispatch(new SendEmail([
                    'toEmail' => $school_email,
                    'subject' => $email_subject,
                    'emailBody' => $email_body
                ]));
            }
        }
        // End School Mail send

        // Start Student Mail send
        $students = Students::with('user')->where('country_id',$req->country)->get()->toArray();
        if ($students) {
            foreach ($students as $students_data) {
                $students_email = $students_data['user']['email'];
                dispatch(new SendEmail([
                    'toEmail' => $students_email,
                    'subject' => $email_subject,
                    'emailBody' => $email_body
                ]));
            }
        }
        // End Student Mail send
        */
        
        if ($success) {
            $notification = [
                'message'=>'Event successfully Updated!',
                'success'=>'success',
            ];

            return redirect()->route('backend.eventlist.eventlist')->with($notification);
        }
    }

    public function eventdelete($id)
    {
        if (isPartnerUser()) {
            abort(403, 'Partners cannot delete Industry Challenges.');
        }

        $event = Event::find($id);
        $this->ensurePartnerCanAccessEvent($event);
        if ($event->event_image != '') {
            if (File::exists($event->event_image)) {
                unlink($event->event_image);
            }
        }

        $eventResource = EventResource::where('event_id', $id)->get();
        if($eventResource->count()) {
            foreach($eventResource as $resource) {
                if($resource->attachment) {
                    $destinationPath = public_path('/image/event/highlight/');
                    FacadesFile::delete($destinationPath . $resource->attachment);
                }
            }
            EventResource::where('event_id', $id)->delete();
        }

        $eventPoster = EventPosterAttachment::where('event_id', $id)->get();
        if($eventPoster->count()) {
            foreach($eventPoster as $poster) {
                if($poster->attachment) {
                    $destinationPath = public_path('/image/event/poster/');
                    FacadesFile::delete($destinationPath . $poster->attachment);
                }
            }
            EventPosterAttachment::where('event_id', $id)->delete();
        }
        
        
        $success = Event::where('id', $id)->delete();

        /*
        //Start All Trainer Email send
        $trainer = Trainer::all()->toArray();

        if ($trainer) {
            foreach ($trainer as $trainers) {
                $trainer_email[] = $trainers['official_email_id'];
            }
            //$change=["{app_name}","{receiver_name}","{action_by}"];
            //$change_to=['venderkids',$data['school_name'],"Super admin"];
            //$email_body=str_replace($change,$change_to,$notification['mail_body']);
            $email_body = 'Event has been Deleted';
            file_put_contents('../resources/views/mail.blade.php', $email_body);

            $emaie_data = ['subject'=>'Event Deleted'];

            safeMailAction('event trainer delete mail', [
                'recipient' => $trainer_email,
            ], function () use ($emaie_data, $trainer_email) {
                $send_mail = Mail::send('mail', $emaie_data, function ($message) use ($emaie_data, $trainer_email) {
                    $message->to($trainer_email)->subject($emaie_data['subject']);
                });
            });
        }
        // End Trainer Email send

        //Start All School Email send
        $school_data = School::all()->toArray();
        if ($school_data) {
            foreach ($school_data as $school_datas) {
                $school_email[] = $school_datas['official_email_id'];
            }
            //$change=["{app_name}","{receiver_name}","{action_by}"];
            //$change_to=['venderkids',$data['school_name'],"Super admin"];
            //$email_body=str_replace($change,$change_to,$notification['mail_body']);
            $email_body = 'Event has been Deleted';
            file_put_contents('../resources/views/mail.blade.php', $email_body);

            $emaie_data = ['subject'=>'Event Deleted'];

            safeMailAction('event school delete mail', [
                'recipient' => implode(',', (array) $school_email),
            ], function () use ($emaie_data, $school_email) {
                $send_mail = Mail::send('mail', $emaie_data, function ($message) use ($emaie_data, $school_email) {
                    $message->to($school_email)->subject($emaie_data['subject']);
                });
            });
        }
        // End School Email send

        // Start All Student Email send
        $students = Students::with('user')->get()->toArray();
        if ($students) {
            foreach ($students as $students_data) {
                $students_email[] = $students_data['user']['email'];
            }

            $email_body = 'Event has been Deleted';
            file_put_contents('../resources/views/mail.blade.php', $email_body);

            $emaie_data = ['subject'=>'Event Deleted'];

            safeMailAction('event student delete mail', [
                'recipient' => implode(',', (array) $students_email),
            ], function () use ($emaie_data, $students_email) {
                $send_mail = Mail::send('mail', $emaie_data, function ($message) use ($emaie_data, $students_email) {
                    $message->to($students_email)->subject($emaie_data['subject']);
                });
            });
        }
        // End Student Email send 
        */
        
        if ($success) {
            $notification = [
                'message'=>'Event Successfully deleted!',
                'success'=>'success',
            ];

            return redirect()->back()->with($notification);
        }
    }

    public function viewchallenge($id)
    {
        if (!empty($id)) {
            $event = Event::find($id);
            $this->ensurePartnerCanAccessEvent($event);
            $eventChallenges = EventChallenge::with('challengesFiles')->where('event_id', $id)->get();
            $studentChallengeList = [];
            foreach ($eventChallenges as $eventChallenge) {
                $studentSchoolInfo = Students::select(['id', 'user_id', 'school_id','name'])
                    ->with([
                    'school' => function ($query) {
                        $query->select(['id', 'user_id', 'school_name']);
                    }])
                    ->find($eventChallenge->student_id);

                $studentChallengeList[] = [
                    'schoolName' => $studentSchoolInfo->school->school_name,
                    'studentName' => $studentSchoolInfo->name,
                    'description' => $eventChallenge->description,
                    'attachmentId' => ($eventChallenge->challengesFiles->isNotEmpty()) ? true : false,
                    'eventChallengeId' => $eventChallenge->id
                ];
            }
            return view('backend.event.student_challenges', compact('studentChallengeList'));
        } else {
            return redirect()->back()->with(['success' => 'error', 'message' => 'Event id not found!']);
        }
    }

    public function attachmentdownload(Request $request)
    {
        $attachments = [];
        if ($request['eventChallengeId']) {
            $studentEventChallenges = EventChallenge::with('challengesFiles')->where('id',$request['eventChallengeId'])->first();            
            if (empty($studentEventChallenges)) {
                return redirect()->back()->with(['success' => 'error', 'message' => 'Challenge files are not found!']);
            }
            $event = Event::find($studentEventChallenges->event_id);
            $this->ensurePartnerCanAccessEvent($event);
            $studentName = Students::select('name')->find($studentEventChallenges->student_id) ?? null;
            if (!$studentName) {
                return redirect()->back()->with(['success' => 'error', 'message' => 'Student Not found!']);
            }
            foreach ($studentEventChallenges->challengesFiles as $challengeFile) {
                if (!empty($challengeFile)) {
                    $attachments[] = $challengeFile->attachment;
                }
            }
            
            $zip_file = str_replace(" ", "_", $studentName['name']) . "_challenge_files.zip";
            $zip_path = public_path($zip_file);
            $zip = new \ZipArchive();
            $res = $zip->open($zip_path, \ZipArchive::CREATE);
            if ($res == TRUE) {
                foreach ($attachments as $individualAttachment) {
                    $filePath = public_path('tenants/' . $individualAttachment);
                    if (file_exists($filePath)) {
                        $zip->addFromString(Str::random(10) . ".jpeg", file_get_contents($filePath));
                    }
                }
                $zip->close();
                return response()->download($zip_path)->deleteFileAfterSend(true);
            } else {
                abort(500, 'Could not create ZIP file.');
            }
        }
    }

    public function deleteHighlight(Request $request) {
        $resourceId = $request->resourceId;
        if($resourceId) {
            $resource = EventResource::find($resourceId);
            
            if($resource->attachment) {
                $destinationPath = public_path('/image/event/highlight/');
                FacadesFile::delete($destinationPath . $resource->attachment);
            }
        }
        EventResource::find($resourceId)->delete();
        return true;
    }

    public function deletePoster(Request $request) {
        $posterId = $request->posterId;
        if($posterId) {
            $resource = EventPosterAttachment::find($posterId);
            
            if($resource->attachment) {
                $destinationPath = public_path('/image/event/poster/');
                FacadesFile::delete($destinationPath . $resource->attachment);
            }
        }
        EventPosterAttachment::find($posterId)->delete();
        return true;
    }

    private function ensurePartnerCanAccessEvent(?Event $event): void
    {
        if (!isPartnerUser()) {
            return;
        }

        if (!$event) {
            abort(404, 'Challenge not found.');
        }

        $partnerCountryId = partnerCountryId();
        if (empty($partnerCountryId) || (int) $event->visibility_type !== 2 || (int) $event->country_id !== (int) $partnerCountryId) {
            abort(403, 'Access denied.');
        }
    }

    private function partnerEventCurrency(): ?string
    {
        if (!isPartnerUser()) {
            return null;
        }

        $currency = \DB::table('currencies')
            ->where('id', auth()->user()->currency_id)
            ->value('code');

        return $currency ? strtolower($currency) : null;
    }
}
