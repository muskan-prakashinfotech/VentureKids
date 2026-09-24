@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">Add Level</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Add Level</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.level.store') }}" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Add Level</h4>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label for="grade">Name</label>
                            <input type="text" class="form-control @error('grade') is-invalid @enderror" id="grade"
                                name="grade" placeholder="Name" value="{{ old('grade') }}">
                            @error('grade')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="exampleInputFile">Upload a Image</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" onchange="validateImage(event, this)"
                                        data-img-preview="#levelshow" id="image" name="image" accept="image/jpg, image/png, image/jpeg">
                                    <label class="custom-file-label" for="exampleInputFile">Upload a Image</label>
                                </div>
                            </div>
                            <img src="#" alt="newupload" id="levelshow" class="mt-2 img-thumbnail"
                                style="display: none; height: 160px; width: 160px; object-fit: contain;">
                            @error('image')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror"
                                id="lweveldescription" name="description" placeholder="Description"
                                value="{{ old('description') }}">
                            @error('description')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="cert_description">Certificate Description</label>
                            <input type="text" class="form-control @error('cert_description') is-invalid @enderror"
                                id="cert_description" placeholder="Certificate Description" name="cert_description"
                                value="{{ old('cert_description') }}">
                            @error('cert_description')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="cert_quote">Certificate Quote</label>
                            <input type="text" class="form-control @error('cert_quote') is-invalid @enderror"
                                id="cert_quote" placeholder="Certificate Quote" name="cert_quote"
                                value="{{ old('cert_quote') }}">
                            @error('cert_quote')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="exampleInputFile">Upload Icon</label>
                            <span style="font-size: 14px;">(Icon should be 50*50 pixel)</span>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" onchange="validateImage(event, this)"
                                        data-img-preview="#levelIconShow" id="level_icon" name="level_icon" accept="image/jpg, image/png, image/jpeg">
                                    <label class="custom-file-label" for="exampleInputFile">Upload Icon</label>
                                </div>
                            </div>
                            <img src="#" alt="newupload" id="levelIconShow" class="mt-2 img-thumbnail"
                                style="display: none; height: 160px; width: 160px; object-fit: contain;">
                            @error('level_icon')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="is_primary">Make Primary Level</label>
                            <input type="checkbox" class="@error('is_primary') is-invalid @enderror"
                                id="is_primary" name="is_primary" value="1">
                            @error('is_primary')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order"  min="1">
                        </div>
                        <div class="form-group">
                            <label for="assessment_order">Assessment Order</label>
                            <input type="number" class="form-control" id="assessment_order" name="assessment_order" min="1" value="{{ old('assessment_order') }}">
                            @error('assessment_order')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="levelpublish">Save as Draft OR Publish</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="levelPublish" id="isDraft" value="0" checked>
                                <label class="form-check-label" for="isDraft">Draft</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="levelPublish" id="isPublish" value="1">
                                <label class="form-check-label" for="isPublish">Publish</label>
                            </div>
                        </div>
                        
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        function validateImage(e, ele) {
            var fileName = e.target.files[0].name;
            var id = e.target.id;
            var allowedFileExtension = ['jpeg', 'jpg', 'png'];
            if (fileName && $.inArray(fileName.split('.').pop().toLowerCase(), allowedFileExtension) == -1) {
                alert("Only JPG, JPEG or PNG files are allowed.");
                $("#"+id).val(''); 
                var placeHolderText = 'Upload a Image';
                if(id == 'icon') {
                    placeHolderText = 'Upload Icon';
                }
                $(ele).next('.custom-file-label').html(placeHolderText);
                $($(ele).data('img-preview')).hide();
            } else {
                $(ele).next('.custom-file-label').html(fileName);
                let previewid = $(ele).data('img-preview');
                let reader = new FileReader();
                reader.onload = function(event) {
                    $(previewid).attr('src', event.target.result);
                    $(previewid).show();
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        }
    </script>
@endsection
