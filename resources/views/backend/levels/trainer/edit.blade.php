@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">Edit Level</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Edit Level</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.trainerlevel.update', $grade->id) }}" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Edit Level</h4>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        @csrf
                        @method('patch')

                        <div class="form-group">
                            <label for="unique_code">Unique Code</label>
                            <input type="text" class="form-control" id="unique_code"
                                name="unique_code" placeholder="Unique Code" value="{{ $grade->unique_code }}" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="grade">Name</label>
                            <input type="text" class="form-control @error('grade') is-invalid @enderror" id="grade"
                                name="grade" placeholder="Name" value="{{ old('grade', $grade->grade) }}">
                            @error('grade')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="exampleInputFile">Upload a Image</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image" accept="image/*" onchange="fileUploadName(event, this)" data-img-preview="#levelshow">
                                    <label class="custom-file-label" for="exampleInputFile">Upload a Image</label>
                                </div>
                            </div>
                            <img src="#" alt="newupload" id="levelshow" class="mt-2 img-thumbnail" style="display: none; height: 160px; width: 160px; object-fit: contain;">
                            @error('image')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        @if(isset($grade->image) && !empty($grade->image))
                            <strong class="text-danger img-text-danger"></strong>
                            <strong class="text-success img-text-success"></strong>
                            <div class="form-group gradeoldimg">
                                <img src="{{ asset('image/level/trainer/' . $grade->image) }}" alt="{{ $grade->grade }}" style="height: 200px; width:200px;" class="img-fluid">
                                <br>
                                <span data-gradeid="{{ $grade->id }}" class="btn btn-sm btn-danger imagedeletebtn" style="width: 200px; margin-top : -59px;">Delete</span>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order"  min="1" value="{{$grade->display_order_id}}">
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
        $('.imagedeletebtn').click(function(){
            var gradeid = $(this).data('gradeid');
            $.ajax({
                url: "{{ route('backend.trainerlevel.gradeimagedelete') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                data: { gradeid : gradeid},
                dataType : 'JSON',
                success: function(data) {
                    if(data.status == true){
                        $('.img-text-success').html(data.message);
                        $('.gradeoldimg').hide();
                    }else{
                        $('.img-text-danger').html(data.message)
                    }
                }
            });
        })
    </script>
@endsection
