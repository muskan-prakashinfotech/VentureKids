@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Trainer Certificates</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Trainer Certificates</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        <style>
            .btn-file {
                position: relative;
                overflow: hidden;
            }

            .btn-file input[type=file] {
                position: absolute;
                top: 0;
                right: 0;
                min-width: 100%;
                min-height: 100%;
                font-size: 100px;
                text-align: right;
                filter: alpha(opacity=0);
                opacity: 0;
                outline: none;
                background: white;
                cursor: inherit;
                display: block;
            }
        </style>
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">

                        @if (session()->has('success'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('success') }}
                            </div>
                        @endif

                        @if (session()->has('update_success'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('update_success') }}
                            </div>
                        @endif

                        @if (session()->has('suspend_success'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('suspend_success') }}
                            </div>
                        @endif
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Trainer</th>
                                    <th>Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reqcertificates as $rc)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ isset($rc->trainer) ? $rc->trainer->trainer_name : '' }}</td>
                                        <td>
                                            @if ($rc->file)
                                                <button type="button" class="btn btn-primary">Approved</button>
                                            @else
                                                <form action="{{ route('backend.trainer-request-upload') }}"
                                                    enctype="multipart/form-data" method="POST">
                                                    @csrf
                                                    <input type="hidden" value="{{ $rc->trainer_id }}" name="trainer_id" />
                                                    <span class="btn btn-default btn-file">
                                                        Browse <input type="file" name="file">
                                                    </span>
                                                    <button type="submit" class="btn btn-success">Submit</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </section>
    </div>
@endsection
