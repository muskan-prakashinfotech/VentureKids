<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\NotificationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    public function trainerNotification()
    {
        $trainerNotifications = NotificationInfo::where(['receiver_status'=> 2, 'receiver_id'=> Session::get('trainer_id')])->get();
        $total_notification = NotificationInfo::where(['receiver_status'=> 2, 'receiver_id'=> Session::get('trainer_id')])->get()->count();

        $message = '';
        if (!count($trainerNotifications) > 0) {
            $message = 'There are no notification available !!';
        }

        return view('trainer.notification.notification', [
            'trainerNotifications' => $trainerNotifications,
            'total_notification'=> $total_notification,
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
          $traier_id = Session::get('trainer_id');
          $notification_id = $req->notification_id;
          if ($notification_id == 'all') {
              $delete = NotificationInfo::where('receiver_id', $traier_id)->where('receiver_status', 2)->delete();
              echo '1';
          } else {
              $id = explode(',', $notification_id);
              $delete = NotificationInfo::whereIn('id', $id)->delete();
              echo '1';
          }
      }

    public function index()
    {
        return view('trainer.notification.index');
    }
}
