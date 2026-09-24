@if (session()->has('success'))
    <div class="alert alert-success" style="text-align: center;">
        {{ session()->get('success') }}
    </div>
@endif

<form class="card InnerForm" id="frmAttendance" action="{{ route('trainer.saveAttendance') }}" method="post">
    @csrf
    <div class="card-header">
        @if (sizeof($students) > 0)
            <h4 class="text-muted">You are managing {{ sizeof($students) }} students</h4>
        @endif
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Select School</label>
                    <select class="form-control" name="school_id" id="school_id" wire:model="school_id">
                        <option value="">---Select One---</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" @php if($school->id == $selectedSchool) echo 'selected'; @endphp>{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Select Class</label>
                    <select class="form-control" name="grade_id" id="grade_id" wire:model="allocation">
                        <option value="">---Select One---</option>
                        @foreach ($trainerallocations->where('school_id', $school_id)->all() as $trainerallocation)
                            <option value="{{ $trainerallocation->id }}" class="text-capitalize" @php if($trainerallocation->id == $selectedClass) echo 'selected'; @endphp>
                                {{ ucfirst($trainerallocation->day_name) . ' (' . $trainerallocation->class_start . '-' . $trainerallocation->class_end . ')' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 d-flex justify-content-start align-items-start" style="margin-top: 27px;">
            <button class="mb-2 btn btn-primary" type="button" id="searchBtn">Search</button>
            </div>
            @php 
            $isDate = false; 
            foreach ($dateRange as $date) {
                if (in_array($date->dayOfWeekIso, $trainerday) && $date->lte(now()))
                    $isDate = true;
            }
            @endphp
            @if($trainerallocations->count() && $isDate)
            <div class="col-md-4 d-flex justify-content-end align-items-end" >
                <button class="mb-4 btn btn-primary" type="submit">Save Attendance</button>
            </div>
            @endif
        </div>
        

    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive">
        <p class="text-secondary">Future Dates Attendance can't saved</p>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Attendance</th>
                            @forelse ($dateRange as $date)
                                @if (in_array($date->dayOfWeekIso, $trainerday) && $date->lte(now()))
                                    <th scope="col">{{ $date->format('d M') }}</th>
                                @endif
                            @empty
                            @endforelse
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td>
                                    <img src="{{ (isset($student->image) && $student->image != 'no_image') ? asset($student->image) : asset('img/default_image.png') }}"
                                        class="mr-2 img-fluid rounded-circle" alt="I"
                                        style="width: 36px; height: 36px;">
                                    {{ $student->name }}
                                </td>
                                @php
                                    $att = $studentattendances
                                        ->where('student_id', $student->id)
                                        ->where('status', '1')
                                        ->count();
                                    $sa = $studentattendances->where('student_id', $student->id)->count();
                                @endphp
                                <td>{{ ($att / ($sa == 0 ? 1 : $sa)) * 100 }}%
                                </td>
                                @php
                                foreach($dateRange as $date) {
                                    if (in_array($date->dayOfWeekIso, $trainerday) &&  $date->lte(now())) {
                                        $date = $date->format('Y-m-d');
                                        $sid = $student->id;
                                        $attendance = $studentattendances
                                        ->where('student_id', $sid)
                                        ->where('date', $date)->pluck('status')->toArray();
                                        $status = 0;
                                        if(!empty($attendance))
                                            $status = $attendance[0];
                                        echo '<td>
                                            <select name="dates['.$date.']['.$sid.']">
                                                <option value="1" ' . ( $status == 1 ? ' selected="selected"' : '' ) . '>P</option>
                                                <option value="0" ' . ( $status == 0 ? ' selected="selected"' : '' ) . '>A</option>
                                            </select>
                                        </td>';
                                    }
                                }
                                @endphp
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- /.card-body -->
</form>

<script>

    $("document").ready(function(){

        setTimeout(function(){
            $("div.alert").remove();
        }, 5000 ); // 5 secs

        $('#searchBtn').click(function(){
            $('#frmAttendance').attr('action', "{{ route('trainer.student_attendence') }}");
            $('#frmAttendance').submit();
        });

    });

</script>
