<?php

namespace App\Http\Livewire\Trainer;

use App\Models\TrainerNotification;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationTrainer extends Component
{
    use WithPagination;
    public $selectedNotifications = [];
    public $allSelected = false;
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function render()
    {
        // $notifications = TrainerNotification::where('trainer_id', Session::get('trainer_id'))->latest('id')->paginate(10);
        $notifications = collect();
        
        if ($this->allSelected) {
            $this->selectedNotifications = $notifications->pluck('id')->toArray();
        }
        if (count($notifications) == count($this->selectedNotifications)) {
            $this->allSelected = true;
        }

        return view('livewire.trainer.notification-trainer', compact('notifications'));
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
        TrainerNotification::whereIn('trainer_id', $this->selectedNotifications)->delete();
    }
}
