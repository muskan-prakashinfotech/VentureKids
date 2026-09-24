<table class="table table-bordered">
    <tr>
        <td>Day</td>
        <td>Lavel</td>
        <td>Start Time</td>
        <td>End Time</td>
    </tr>
    @if(isset($school['class_schedule']) && count($school['class_schedule']) > 0)
        @forelse ($school['class_schedule'] as $schedule)
            <tr>
                <td>
                    @if ($schedule['day'] == '6') Saturday @endif
                    @if ($schedule['day'] == '0') Sunday @endif
                    @if ($schedule['day'] == '1') Monday @endif
                    @if ($schedule['day'] == '2') Tuesday @endif
                    @if ($schedule['day'] == '3') Wednesday @endif
                    @if ($schedule['day'] == '4') Thursday @endif
                    @if ($schedule['day'] == '5') Friday @endif
                </td>
                <td>
                    @foreach ($grade as $grades)
                        @if ($schedule['grade'] == $grades->id)
                            {{ $grades->grade }}
                        @endif
                    @endforeach
                </td>
                <td>
                    {{ date('h:i:s a', strtotime($schedule['start_time']));  }}
                </td>
                <td>
                    {{ date('h:i:s a', strtotime($schedule['end_time'])); }}
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td align="center" colspan="4">Class Not Schedule</td>
        </tr>
    @endif
</table>
