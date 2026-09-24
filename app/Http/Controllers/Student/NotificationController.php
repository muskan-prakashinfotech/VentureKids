<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\NotificationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    public function studentNotification()
    {
        $allnotifications = NotificationInfo::where('receiver_id', Session::get('student_id'))->get();
        $total_notification = count($allnotifications);

        $message = '';
        if (!count($allnotifications) > 0) {
            $message = 'There are no notification available !!';
        }

        return view('student.notification.notification', [
            'allnotifications' => $allnotifications,
            'message' => $message,
            'total_notification'=>$total_notification,
        ]);
    }

    public function singleNotification(Request $request)
    {
        $notification = NotificationInfo::where('id', $request->notificationId)->get()->first();
        echo json_encode($notification);
    }

    public function notification_delete(Request $req)
    {
        $student_id = Session::get('student_id');

        $notification_id = $req->notification_id;

        if ($notification_id == 'all') {
            $delete = NotificationInfo::where('receiver_id', $student_id)->where('receiver_status', 3)->delete();
            echo '1';
        } else {
            $id = explode(',', $notification_id);
            $delete = NotificationInfo::whereIn('id', $id)->delete();
            echo '1';
        }
    }

    public function index()
    {
        return view('student.notification.index');
    }
}
