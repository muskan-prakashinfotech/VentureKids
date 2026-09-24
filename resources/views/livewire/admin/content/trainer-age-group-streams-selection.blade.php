<div>
    <div class="form-group">
        <label for="eventstart">{{ __('admin/content.select_level') }}</label>
        <select class="form-control" wire:model="selagegroup" name="agegroup_id" id="group" required>
            <option value="" selected>---Select---</option>
            @foreach ($agegroups as $ag)
                <option value="{{ $ag->id }}" @if (isset($content) && $content['agegroup_id'] == $ag->id) selected @endif>
                    {{ $ag->grade }}
                </option>
            @endforeach
        </select>
        <!-- <a href="{{ route('backend.trainerlevel.index') }}" class="p-1 mt-1 badge badge-success rounded-pill">Add Level</a> -->
        @error('agegroup_id')
            <strong class="text-danger">{{ $message }}</strong>
        @enderror
    </div>
    <div class="form-group">
        <label for="eventstart">{{ __('admin/content.select_stream') }}</label>
        <select class="form-control" name="stream_id" id="stream_id"
            @if ($selagegroup == null) disabled @else @endif required>
            <option value="" selected>---Select---</option>
            @if($selstreams != null)
                @foreach ($selstreams as $stream)
                    <option value="{{ $stream->id }}" @if (isset($content) && $content['stream_id'] == $stream->id) selected @endif>
                        {{ $stream->title }}</option>
                @endforeach
            @endif
        </select>
        <!-- <span role="button" class="badge badge-info rounded-pill" data-toggle="modal"
            data-target="#addStream">{{ __('admin/content.add_stream') }}</span>
        <button type="button" class="badge btn-danger rounded-pill btn"
            onclick="deleteStream()">{{ __('admin/content.delete_stream') }}</button> -->
        @error('stream_id')
            <strong class="text-danger">{{ $message }}</strong>
        @enderror
    </div>
</div>
