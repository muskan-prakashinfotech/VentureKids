<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\NotificationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    public function schoolNotification()
    {
        $schoolNotifications = NotificationInfo::where(['receiver_status' => 1, 'receiver_id' => Session::get('school_id')])->get();
        $total_notification = NotificationInfo::where(['receiver_status'=> 1, 'receiver_id'=> Session::get('school_id')])->get()->count();
        $message = '';
        if (!count($schoolNotifications) > 0) {
            $message = 'There are no notification available !!';
        }

        return view('school.notification.notification', [
            'schoolNotifications' => $schoolNotifications,
            'total_notification'  => $total_notification,
            'message' => $message,
        ]);
    }

    public function singleNotification(Request $request)
    {
        $notification = NotificationInfo::where('id', $request->notificationId)->get()->first();
        echo json_encode($notification);
    }

     public function notification_delete(Request $req)
     {
         $school_id = Session::get('school_id');

         $notification_id = $req->notification_id;

         if ($notification_id == 'all') {
             $delete = NotificationInfo::where('receiver_id', $school_id)->where('receiver_status', 1)->delete();
             echo '1';
         } else {
             $id = explode(',', $notification_id);
             $delete = NotificationInfo::whereIn('id', $id)->delete();
             echo '1';
         }
     }

    public function index()
    {
        return view('school.notification.index');
    }
}
