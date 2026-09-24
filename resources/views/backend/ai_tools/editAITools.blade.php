@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">Edit AI Tool</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            @if (Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif

            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Edit AI Tool</h3>
                            <div class="card-tools">
                                <a href="{{ route('backend.aiToollist.aiToolList') }}" class="btn btn-warning">
                                    <i class="fa fa-chevron-left"></i> Back
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('backend.aiToolupdate.aiToolUpdate', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                {{-- Name --}}
                                <div class="form-group">
                                    <label for="name">AI Tool Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $subcategory->name) }}" required>
                                    @error('name') <strong class="text-danger">{{ $message }}</strong> @enderror
                                </div>

                                {{-- Image --}}
                                <div class="form-group">
                                    <label for="image">AI Tool Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*"
                                    @if(!$subcategory->image) required @endif >
                                    @if($subcategory->image)
                                    <div class="mt-2 d-flex align-items-center justify-content-between border p-2 rounded" id="image-row">
                                        {{-- File name --}}
                                        <span>{{ basename($subcategory->image) }}</span>

                                        {{-- Delete button --}}
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteAiToolImage({{ $subcategory->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    @endif
                                    @error('image') <strong class="text-danger">{{ $message }}</strong> @enderror
                                </div>

                                {{-- Description --}}
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description1" name="description" rows="4" required>{{ old('description', $subcategory->description) }}</textarea>
                                    @error('description') <strong class="text-danger">{{ $message }}</strong> @enderror
                                </div>

                                {{-- Prototype Type (Dropdown) --}}
                                <div class="form-group">
                                    <label for="ai_tool_id">AI Tool Type</label>
                                    <select class="form-control" name="ai_tool_id" required>
                                        <option value="">Select Prototype Type</option>
                                        @foreach($tools as $tool)
                                        <option value="{{ $tool->id }}" {{ $subcategory->ai_tool_id == $tool->id ? 'selected' : '' }}>
                                            {{ $tool->title }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('ai_tool_id') <strong class="text-danger">{{ $message }}</strong> @enderror
                                </div>

                                {{-- Status --}}
                                <div class="form-group">
                                    <label>Status</label>
                                    <div>
                                        <label class="mr-3">
                                            <input type="radio" name="status" value="1" {{ $subcategory->status == 1 ? 'checked' : '' }}> Active
                                        </label>
                                        <label>
                                            <input type="radio" name="status" value="0" {{ $subcategory->status == 0 ? 'checked' : '' }}> Inactive
                                        </label>
                                    </div>
                                    @error('status') <strong class="text-danger">{{ $message }}</strong> @enderror
                                </div>

                                {{-- Display Order --}}
                                <div class="form-group">
                                    <label for="display_order">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" value="{{ old('display_order', $subcategory->display_order) }}">
                                    @error('display_order') <strong class="text-danger">{{ $message }}</strong> @enderror
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Update Prototype</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    function deleteAiToolImage(subcategoryId) {
        if (confirm("Are you sure you want to delete this AI Tool image?")) {
            $(`#attachId${subcategoryId}`).addClass('disabled');
            $.ajax({
                url: "{{ route('backend.deleteAiToolImage') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: subcategoryId
                },
                method: "POST",
                success: function(res) {
                    if (res.success) {
                        alert(res.message);
                        window.location.href = "/admin/ai-Tools/" + subcategoryId + "/edit";
                    } else {
                        alert(res.message || "Failed to delete image");
                    }
                },
                error: function() {
                    alert("An error occurred while deleting the image.");
                }
            });
        }
    }
</script>
@endsection