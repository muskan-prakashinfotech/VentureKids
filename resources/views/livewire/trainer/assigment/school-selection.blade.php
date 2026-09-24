<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="schoolname">{{ __('admin/student_communication.select_school') }}</label>
            <select class="form-control" name="school_id" wire:model='school'>
                <option value="">---Select---</option>
                @foreach ($schools as $school)
                    <option value="{{ $school['id'] }}">{{ $school['school_name'] }}</option>
                @endforeach
            </select>
            @error('school_id')
                <strong class="text-danger">{{ $message }}</strong>
            @enderror
        </div>
    </div>
    @if (isset($grades) && sizeof($grades) > 0)
        <div class="col-md-6">
            <div class="form-group">
                <label for="schoolname">Select Level</label>
                <select class="form-control" name="grade_id">
                    <option value="">---Select---</option>
                    @forelse ($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->grade }}</option>
                    @empty
                    @endforelse
                </select>
                @error('grade_id')
                    <strong class="text-danger">{{ $message }}</strong>
                @enderror
            </div>
        </div>
    @endif
</div>
