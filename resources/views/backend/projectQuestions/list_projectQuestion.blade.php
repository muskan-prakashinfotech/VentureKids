@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Questions — {{ $section->section_title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.projectmanagement.index') }}">Project Management</a></li>
                        <li class="breadcrumb-item active">Project Sections</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
            @endif

            <a href="{{ route('backend.projectQuestioncreate.projectQuestionCreate', $section->id) }}" class="btn btn-primary">{{ __('Add Question') }}</a>
            <div class="card-tools">
                @unless ($attachmentQuestion)
                    <div class="custom-control custom-switch d-inline-block align-middle mr-3">
                        <input type="checkbox" class="custom-control-input" id="attachmentAllowedToggle">
                        <label class="custom-control-label" for="attachmentAllowedToggle">Attachment Allowed</label>
                    </div>
                @endunless
                <a href="{{ route('backend.projectSectionlist.projectSectionList') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <table id="example_new" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Field Text</th>
                        <th>Help Text</th>
                        <th>Display Order</th>
                        <th>Required</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="attachmentQuestionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('backend.projectQuestionAttachmentstore.projectQuestionAttachmentStore', $section->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Attachment Question</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="attachment_field_text">Field Text</label>
                            <textarea class="form-control" id="attachment_field_text" name="field_text" rows="2" required>{{ old('field_text') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="attachment_help_text">Help Text</label>
                            <textarea class="form-control" id="attachment_help_text" name="help_text" rows="2">{{ old('help_text') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Attachment Types</label>
                            @php
                                $oldAttachmentTypes = old('attachment_types', []);
                                $allTypesChecked = in_array('pdf', $oldAttachmentTypes) && in_array('images', $oldAttachmentTypes) && in_array('videos', $oldAttachmentTypes);
                            @endphp
                            <div>
                                <label class="mr-3">
                                    <input type="checkbox" class="attachment-type-option" name="attachment_types[]" value="pdf" {{ in_array('pdf', $oldAttachmentTypes) ? 'checked' : '' }}> PDF
                                </label>
                                <label class="mr-3">
                                    <input type="checkbox" class="attachment-type-option" name="attachment_types[]" value="images" {{ in_array('images', $oldAttachmentTypes) ? 'checked' : '' }}> Images
                                </label>
                                <label class="mr-3">
                                    <input type="checkbox" class="attachment-type-option" name="attachment_types[]" value="videos" {{ in_array('videos', $oldAttachmentTypes) ? 'checked' : '' }}> Video URL
                                </label>
                                <label>
                                    <input type="checkbox" id="attachmentTypeSelectAll" {{ $allTypesChecked ? 'checked' : '' }}> All
                                </label>
                            </div>
                            @error('attachment_types')
                                <strong class="text-danger d-block">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Allow Multiples</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="attachment_allowed_multiples" name="allowed_multiples" value="1" {{ old('allowed_multiples') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="attachment_allowed_multiples">Allow multiple files</label>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Required</label>
                                <div>
                                    <label class="mr-3">
                                        <input type="radio" name="is_required" value="1" {{ old('is_required', '1') == '1' ? 'checked' : '' }}> Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="is_required" value="0" {{ old('is_required') == '0' ? 'checked' : '' }}> No
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <div>
                                    <label class="mr-3">
                                        <input type="radio" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}> Active
                                    </label>
                                    <label>
                                        <input type="radio" name="status" value="0" {{ old('status') == '0' ? 'checked' : '' }}> Inactive
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(document).on('change', '#attachmentAllowedToggle', function() {
            if ($(this).is(':checked')) {
                $('#attachmentQuestionModal').modal('show');
            }
        });

        $('#attachmentQuestionModal').on('hidden.bs.modal', function() {
            $('#attachmentAllowedToggle').prop('checked', false);
        });

        $(document).on('change', '#attachmentTypeSelectAll', function() {
            $('.attachment-type-option').prop('checked', $(this).is(':checked'));
        });

        $(document).on('change', '.attachment-type-option', function() {
            var allChecked = $('.attachment-type-option').length === $('.attachment-type-option:checked').length;
            $('#attachmentTypeSelectAll').prop('checked', allChecked);
        });

        @if ($errors->any())
            $('#attachmentAllowedToggle').prop('checked', true);
            $('#attachmentQuestionModal').modal('show');
        @endif

        let table = $('#example_new').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('backend.getProjectQuestion', $section->id) }}",
                type: 'GET',
                dataType: 'json',
            },
            columns: [{
                    data: 'field_text',
                    name: 'field_text'
                },
                {
                    data: 'help_text',
                    name: 'help_text'
                },
                {
                    data: 'display_order',
                    name: 'display_order'
                },
                {
                    data: 'is_required',
                    name: 'is_required',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $(document).on('click', '#deleteProjectQuestion', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).data('url');

            swal({
                    title: "Are you sure?",
                    text: "You want to delete this question!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.success) {
                                    swal("Deleted!", "Your question has been deleted.", "success");
                                    $('#example_new').DataTable().ajax.reload();
                                } else {
                                    swal("Oops!", "Something went wrong.", "error");
                                }
                            },
                            error: function(xhr) {
                                var message = (xhr.responseJSON && xhr.responseJSON.message) || "Failed to delete the question.";
                                swal("Error!", message, "error");
                            }
                        });
                    } else {
                        swal("Cancelled", "Your question is safe.", "info");
                    }
                });
        });
    });
</script>
@endsection
