<?php

namespace App\Http\Livewire\Admin\Content;

use App\Models\Grade;
use App\Models\Stream;
use Livewire\Component;

class AgeGroupStreamsSelection extends Component
{
    public $selagegroup = null;
    public $selstreams;
    public $agegroups;
    public $content;

    public function mount($content = null)
    {
        $this->content = $content;
        $this->agegroups = Grade::all();
        if (count($this->agegroups) > 0) {
            $this->selagegroup = $this->agegroups->first()->id;
            $this->selstreams = Stream::where('agegroup_id', $this->selagegroup)->orderBy('display_order_id')->get();
        }

        if (isset($this->content)) {
            $this->selagegroup = $this->content['agegroup_id'];
            $this->selstreams = Stream::where('agegroup_id', $this->selagegroup)->orderBy('display_order_id')->get();
        }
    }

    public function updatedSelagegroup($selagegroup)
    {
        $this->selstreams = Stream::where('agegroup_id', $selagegroup)->orderBy('display_order_id')->get();
    }

    public function render()
    {
        return view('livewire.admin.content.age-group-streams-selection');
    }
}
