<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="schoolname">{{ __('admin/student_communication.select_school') }}</label>
            <select class="form-control" name="school_id" id="school_id" wire:model='school'>
                <option value="">---Select---</option>
                @foreach ($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                @endforeach
            </select>
            @error('school_id')
                <strong class="text-danger">{{ $message }}</strong>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="schoolname">{{ __('admin/student_communication.select_level') }}</label>
            <select class="form-control" name="grade_id" id="grade_id" wire:model='grade'>
                <option value="">---Select---</option>
                @isset($grades)
                    @forelse($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->grade }}</option>
                    @empty
                    @endforelse
                @endisset
            </select>
            @error('grade_id')
                <strong class="text-danger">{{ $message }}</strong>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="schoolname">Select Trainer</label>
            <select class="form-control" name="trainer_id" id="trainer_id">
                <option value="">---Select---</option>
                @isset($trainers)
                    @foreach ($trainers as $trainer)
                        <option value="{{ $trainer->id }}">{{ $trainer->trainer_name }}</option>
                    @endforeach
                @endisset
            </select>
            @error('trainer_id')
                <strong class="text-danger">{{ $message }}</strong>
            @enderror
        </div>
    </div>
</div>
