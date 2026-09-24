@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Play Affirmation </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">Play Affirmation</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('backend.student.playaffirmation.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="card shadow-sm">
                    <div class="card-body">
                        <label for="file_path" class="form-label fw-bold">Audio File (mp3/wav, max 10MB):</label>
                        <input type="file" name="file_path" id="file_path" accept=".mp3,.wav" class="form-control mb-2" required>

                        {{-- Preview and Actions --}}
                        @if(!empty($affirmation) && $affirmation->file_path)
                        <div class="border p-2 d-flex justify-content-between align-items-center">
                            <div class="text-break">
                                {{ basename($affirmation->file_path) }}
                            </div>
                            <div class="btn-group">
                                {{-- Delete --}}
                                <a onclick="deleteAffirmationFile({{ $affirmation->id }})" class="btn btn-danger btn-sm" id="audioDeleteBtn{{ $affirmation->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                        @endif

                        <button type="submit" class="btn btn-info mt-3 text-white">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteAffirmationFile(id) {
    if (confirm("Are you sure you want to delete this audio?")) {
        $(`#audioDeleteBtn${id}`).addClass('disabled');
        $.ajax({
            url: "{{ route('backend.deleteAffirmationFile') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id: id
            },
            success: function(res) {
                if (res.success) {
                    alert("Audio deleted.");
                    window.location.reload();
                } else {
                    alert("Something went wrong.");
                }
            },
            error: function() {
                alert("Failed to delete the audio.");
            }
        });
    }
}
</script>

@endsection