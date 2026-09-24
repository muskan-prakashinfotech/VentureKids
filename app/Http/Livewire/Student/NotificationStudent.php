<?php

namespace App\Http\Livewire\Student;

use App\Models\StudentNotification;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationStudent extends Component
{
    use WithPagination;
    public $selectedNotifications = [];
    public $allSelected = false;
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function render()
    {
        // $notifications = StudentNotification::where('student_id', Session::get('student_id'))->latest('id')->paginate(10);
        $notifications = collect();
        
        if ($this->allSelected) {
            $this->selectedNotifications = $notifications->pluck('id')->toArray();
        }
        if (count($notifications) == count($this->selectedNotifications)) {
            $this->allSelected = true;
        }

        return view('livewire.student.notification-student', compact('notifications'));
    }

    public function updateCom()
    {
        $this->emitSelf('refreshComponent');
    }

    public function selectOn()
    {
        $this->allSelected = false;
    }

    public function destory()
    {
        StudentNotification::whereIn('student_id', $this->selectedNotifications)->delete();
    }
}
