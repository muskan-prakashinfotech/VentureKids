<?php

namespace App\Http\Livewire\Admin\School;

use App\Models\AdminNotification;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationBox extends Component
{
    use WithPagination;
    public $selectedNotifications = [];
    public $allSelected = false;
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function render()
    {
        $notifications = AdminNotification::latest('id')->paginate(10);

        return view('livewire.admin.school.notification-box', compact('notifications'));
    }

    public function updateCom()
    {
        $this->emitSelf('refreshComponent');
    }
}
