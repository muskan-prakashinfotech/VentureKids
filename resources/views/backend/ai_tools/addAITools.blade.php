@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">
     <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">Create AI Tool</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    @if (session()->has('success'))
    <div class="alert alert-success" style="text-align: center;">
        {{ session()->get('success') }}
    </div>
    @endif
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Create AI Tool</h3>
                            <div class="card-tools">
                                <a href="{{ route('backend.aiToollist.aiToolList') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>

                        <form action="{{ route('backend.aiToolstore.aiToolStore') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                {{-- Name --}}
                                <div class="form-group">
                                    <label for="name">AI Tool Name</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required>
                                    @error('name')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                {{-- Image --}}
                                <div class="form-group">
                                    <label for="image">AI Tool Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                    @error('image')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description1" name="description" rows="4" required></textarea>
                                    @error('description')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                {{-- Prototype Type (Dropdown from Parent Table) --}}
                                <div class="form-group">
                                    <label for="ai_tool_id">AI Tool Type</label>
                                    <select class="form-control" name="ai_tool_id" required>
                                        <option value="">Select AI Tool Type </option>
                                        @foreach($tools as $tool)
                                        <option value="{{ $tool->id }}">{{ $tool->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('ai_tool_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div class="form-group">
                                    <label>Status</label>
                                    <div>
                                        <label class="mr-3">
                                            <input type="radio" name="status" value="1" checked> Active
                                        </label>
                                        <label>
                                            <input type="radio" name="status" value="0"> Inactive
                                        </label>
                                    </div>
                                    @error('status')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                {{-- Display Order --}}
                                <div class="form-group">
                                    <label for="display_order">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" min="1">
                                    @error('display_order')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


@endsection