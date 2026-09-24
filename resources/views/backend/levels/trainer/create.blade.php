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
                            <li class="breadcrumb-item active">Add Trainer Level</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.trainerlevel.store') }}" method="POST" enctype="multipart/form-data" class="card">
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
                                    <input type="file" class="custom-file-input" onchange="fileUploadName(event, this)"
                                        data-img-preview="#levelshow" id="image" name="image" accept="image/*">
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
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order"  min="1">
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

    <script></script>
@endsection
