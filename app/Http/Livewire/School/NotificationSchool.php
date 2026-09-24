<?php

namespace App\Http\Livewire\School;

use App\Models\SchoolNotification;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationSchool extends Component
{
    use WithPagination;
    public $selectedNotifications = [];
    public $allSelected = false;
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function render()
    {
        // $notifications = SchoolNotification::where('school_id', Session::get('school_id'))->latest('id')->paginate(10);
        $notifications = collect();
        
        if ($this->allSelected) {
            $this->selectedNotifications = $notifications->pluck('id')->toArray();
        }
        if (count($notifications) == count($this->selectedNotifications)) {
            $this->allSelected = true;
        }

        return view('livewire.school.notification-school', compact('notifications'));
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
        SchoolNotification::whereIn('school_id', $this->selectedNotifications)->delete();
    }
}
