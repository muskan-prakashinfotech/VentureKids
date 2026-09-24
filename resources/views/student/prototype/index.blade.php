@extends('backend.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Generate Prototype</h2>
        <a href="{{ route('student.my-workspace') }}" class="btn btn-sm btn-warning float-right"> <i class="material-icons">west</i> Back</a>
    </div>

    <!-- Main content -->
    <section>
        @if(Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <form action="{{ route('student.generate-prototype') }}" name="prototypeFrm" id="prototypeFrm" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Sketch Upload -->
                <div class="form-group">
                    <label for="image">Upload Sketch of the Prototype (jpg/jpeg/png, max 10MB)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                    @error('image')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="appearance">
                        1. What does your product look like? Describe its shape, colors, materials, and any special features. <span class="text-gray-500"></span>
                    </label>
                    <textarea name="appearance" rows="3" 
                            class="form-control w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Example: It's shaped like a jellybean with wings. It's made of bamboo and glows in the dark. (Leave blank if unsure - we'll analyze your sketch!)"></textarea>
                </div>

                <div class="form-group">
                    <label for="function">
                        2. What special function or feature makes it stand out? <span class="text-gray-500"></span>
                    </label>
                    <textarea name="function" rows="3" 
                            class="form-control w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Example: It talks to animals, translates dreams into stories, or collects plastic from the ocean and recycles it. (Leave blank if unsure!)"></textarea>
                </div>

                <div class="form-group">
                    <label for="environment">
                        3. Where is it used or what environment does it work best in? <span class="text-gray-500"></span>
                    </label>
                    <textarea name="environment" rows="3" 
                            class=" form-control w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Example: It's used underwater in coral reefs, flies above a desert town, or helps people in space stations. (Leave blank if unsure!)"></textarea>
                </div>

                <div class="form-group">
                    <label for="feeling">
                        4. What should people feel after looking at your prototype? <span class="text-gray-500"></span>
                    </label>
                    <textarea name="feeling" rows="3" 
                            class="form-control w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Example: It should feel magical and happy, dark and mysterious like a superhero gadget, or peaceful and helpful. (Leave blank if unsure!)"></textarea>
                </div>
                
                <button type="submit" class="btn btn-sm btn-primary float-right">Generate Prototype</button>
                </form>
            </div>
        </div>
        
    </section>
</div>
@endsection