@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.projectQuestionupdate.projectQuestionUpdate', $question->id) }}" method="POST" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Question — {{ $section->section_title }}</h4>
                        <a href="{{ route('backend.projectQuestionlist.projectQuestionList', $section->id) }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                    <div class="card-body">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="field_text">Field Text</label>
                            <textarea class="form-control" id="field_text" name="field_text" rows="2" required>{{ old('field_text', $question->field_text) }}</textarea>
                            @error('field_text')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="help_text">Help Text</label>
                            <textarea class="form-control" id="help_text" name="help_text" rows="2">{{ old('help_text', $question->help_text) }}</textarea>
                            @error('help_text')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" min="1" value="{{ old('display_order', $question->display_order) }}">
                            @error('display_order')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Is Required</label>
                                <div>
                                    <label class="mr-3">
                                        <input type="radio" name="is_required" value="1" {{ $question->is_required == 1 ? 'checked' : '' }}> Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="is_required" value="0" {{ $question->is_required == 0 ? 'checked' : '' }}> No
                                    </label>
                                </div>
                                @error('is_required')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <div>
                                    <label class="mr-3">
                                        <input type="radio" name="status" value="1" {{ $question->status == 1 ? 'checked' : '' }}> Active
                                    </label>
                                    <label>
                                        <input type="radio" name="status" value="0" {{ $question->status == 0 ? 'checked' : '' }}> Inactive
                                    </label>
                                </div>
                                @error('status')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                        </div>
                        @if ($question->field_type === 'file')
                            @php
                                $currentAttachmentTypes = old('attachment_types', explode(',', $question->allowed_types ?? ''));
                                $allTypesChecked = in_array('pdf', $currentAttachmentTypes) && in_array('images', $currentAttachmentTypes) && in_array('videos', $currentAttachmentTypes);
                            @endphp
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_allow_attachments" name="allow_attachments" value="1" {{ old('allow_attachments', $question->allow_attachments) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="edit_allow_attachments">Attachment Allowed</label>
                                </div>
                                @error('allow_attachments')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Allowed Types</label>
                                <div>
                                    <label class="mr-3">
                                        <input type="checkbox" class="attachment-type-option" name="attachment_types[]" value="pdf" {{ in_array('pdf', $currentAttachmentTypes) ? 'checked' : '' }}> PDF
                                    </label>
                                    <label class="mr-3">
                                        <input type="checkbox" class="attachment-type-option" name="attachment_types[]" value="images" {{ in_array('images', $currentAttachmentTypes) ? 'checked' : '' }}> Images
                                    </label>
                                    <label class="mr-3">
                                        <input type="checkbox" class="attachment-type-option" name="attachment_types[]" value="videos" {{ in_array('videos', $currentAttachmentTypes) ? 'checked' : '' }}> Video URL
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
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_allowed_multiples" name="allowed_multiples" value="1" {{ old('allowed_multiples', $question->allowed_multiples) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="edit_allowed_multiples">Allow multiple files</label>
                                </div>
                                @error('allowed_multiples')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <script>
                                $(document).ready(function() {
                                    $(document).on('change', '#attachmentTypeSelectAll', function() {
                                        $('.attachment-type-option').prop('checked', $(this).is(':checked'));
                                    });

                                    $(document).on('change', '.attachment-type-option', function() {
                                        var allChecked = $('.attachment-type-option').length === $('.attachment-type-option:checked').length;
                                        $('#attachmentTypeSelectAll').prop('checked', allChecked);
                                    });
                                });
                            </script>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
