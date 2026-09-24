<?php

namespace App\Http\Livewire\Admin\Content;

use App\Models\Trainerlavel;
use App\Models\Stream;
use App\Models\Trainerstream;
use Livewire\Component;

class TrainerAgeGroupStreamsSelection extends Component
{
    public $selagegroup = null;
    public $selstreams;
    public $agegroups;
    public $content;

    public function mount($content = null)
    {
        $this->content = $content;
        $this->agegroups = Trainerlavel::all();
        if (count($this->agegroups) > 0) {
            $this->selagegroup = $this->agegroups->first()->id;
            $this->selstreams = Trainerstream::where('agegroup_id', $this->selagegroup)->orderBy('display_order_id')->get();
        }

        if (isset($this->content)) {
            $this->selagegroup = $this->content['agegroup_id'];
            $this->selstreams = Trainerstream::where('agegroup_id', $this->selagegroup)->orderBy('display_order_id')->get();
        }
    }

    public function updatedSelagegroup($selagegroup)
    {
        $this->selstreams = Trainerstream::where('agegroup_id', $selagegroup)->orderBy('display_order_id')->get();
    }

    public function render()
    {
        return view('livewire.admin.content.trainer-age-group-streams-selection');
    }
}
