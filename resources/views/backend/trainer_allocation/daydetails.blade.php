<div>
    <table class="table">
        <thead>
            <tr>
                <th scope="row" colspan="3">
                    <div>Class Schedule</div>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trainers as $trainer)
                @if (isset($trainer->trainer))
                    <tr rowspan="2">
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr class="table-warning">
                        <th scope="row">Trainer Name</th>
                        <td>{{ $trainer->trainer->trainer_name }}</td>
                        <td>
                            <button type="button" class="float-right btn btn-primary"
                                onclick="deleteClassSchedule({{ $trainer->id }})">Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Class Time</th>
                        <td class="text-uppercase" colspan="3">
                            {{ date('g:i a', strtotime($trainer->class_start)) . ' - ' . date('g:i a', strtotime($trainer->class_end)) }}
                        </td>
                    </tr>
                    @isset($trainer->level)
                        <tr>
                            <th scope="row">Level</th>
                            <td class="" colspan="3">{{ $trainer->level->grade }}
                            </td>
                        </tr>
                    @endisset
                @endif
            @empty
                <tr>
                    <th scope="row" colspan="2">
                        <div>No Class Schedule on this day.</div>
                    </th>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
