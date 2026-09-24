@extends('backend.layouts.app')

@section('content')

@foreach($assignment_files as $files)
<iframe src="{{ asset($files->attachment) }}" style="width:100%;height:100vh"></iframe>
<div class="pdfdown_button"><a href="{{ asset($files->attachment) }}" target="_thapa"><i class="fas fa-download"></i> Download</a></div>
<div class="pdfdown_button"> Comment</div>
@endforeach

@endsection